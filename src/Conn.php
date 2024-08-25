<?php

namespace Andrey\Nats;

use Andrey\JsonHandler\JsonHandler;
use Andrey\Nats\Types\Message;
use Andrey\Nats\Types\Operation;
use Andrey\Nats\Types\ServerInfo;
use Andrey\Nats\Types\Subscription;
use Andrey\Nats\Types\Util\Helper;
use Andrey\Nats\Types\Util\Valid;
use Closure;
use InvalidArgumentException;
use JsonException;
use RuntimeException;

/**
 * A Conn represents the connection with a nats-server.
 * It can send and receive payloads.
 */
class Conn
{
    public Stats $stats;

    private int $ssid = 0;

    public readonly ServerInfo $serverInfo;

    /** @var resource $streamSocket */
    private $streamSocket;

    /** @var Subscription[] */
    private array $subscriptions = [];

    private Writer $writer;
    private Reader $reader;

    public function __construct(
        private readonly string $url,
        public readonly Parser $parser,
        public readonly Options $options,
    ) { }

    /**
     * @return resource
     */
    private function getStream(string $url): mixed
    {
        $errNum  = null;
        $errStr = null;

        $fp = stream_socket_client(
            $url,
            $errNum,
            $errStr, null,
            STREAM_CLIENT_CONNECT,
            stream_context_get_default(),
        );

        if ($fp === false) {
            throw new RuntimeException('failed to connect');
        }

        $timeout = number_format($this->options->timeout, 3);
        $seconds = floor($timeout);
        $microseconds = ($timeout - $seconds) * 1000;
        stream_set_timeout($fp, $seconds, $microseconds);

        return $fp;
    }

    /**
     * @throws JsonException
     */
    public function connect(): void
    {
        $this->stats = new Stats();

        $this->streamSocket = $this->getStream($this->url);
        $this->writer = new Writer($this->streamSocket);
        $this->reader = new Reader($this->streamSocket, $this->options);

        $infoRaw = $this->reader->read();
        if (!str_starts_with($infoRaw, 'INFO')) {
            throw new RuntimeException('expected INFO payload response on connection');
        }
        $handler = new JsonHandler();

        $this->serverInfo = new ServerInfo();
        $handler->hydrateObject(substr($infoRaw, 5), $this->serverInfo);

        if (!empty($this->options->connectInfo->nKey) && empty($this->serverInfo->nonce)) {
            throw new InvalidArgumentException('nats: nkeys not supported by the server');
        }

        $this->writer->write($this->connectProto());
        $this->ping();
    }

    /**
     * @throws JsonException
     */
    private function connectProto(): string
    {
        $handler = new JsonHandler();
        $connectInfo = JsonHandler::Encode($handler->serialize($this->options->connectInfo));
        return sprintf(Operation::Connect->proto(), $connectInfo);
    }

    public function ping(): bool
    {
        $this->writer->write(Operation::Ping->proto());
        if (!$this->reader->readPong()) {
            return false;
        }
        return true;
    }

    public function subscribe(
        string $subject,
        string $queue,
        Closure $handler,
    ): int {
        if (!Valid::Subject($subject)) {
            throw new InvalidArgumentException('invalid subject name');
        }

        if (!Valid::Queue($queue)) {
            throw new InvalidArgumentException('invalid queue name');
        }

        $this->ssid++;
        $this->subscriptions[$this->ssid] = new Subscription(
            sid: $this->ssid,
            subject: $subject,
            queue: $queue,
            handler: $handler,
        );

        $bytes = $this->writer->write(sprintf(
            Operation::Sub->proto(),
            $subject,
            $queue,
            $this->ssid,
        ));
        $this->stats->outBytes += $bytes;
        $this->stats->outMsgs++;

        return $this->ssid;
    }

    public function unsubscribe(int $sid, ?int $quantity = null): void
    {
        $this->writer->write(sprintf(
            Operation::Unsub->proto(),
            $sid,
            $quantity ? (string)($quantity) : '',
        ));
        if ($quantity === null) {
            unset($this->subscriptions[$sid]);
        }
    }

    public function publish(Message $message): void
    {
        if (!Valid::Subject($message->subject)) {
            throw new InvalidArgumentException('invalid subject name');
        }

        if (!$this->serverInfo->headers && count($message->header) === 0) {
            throw new InvalidArgumentException('headers not supported');
        }

        $msgSize = $message->size();
        if ($msgSize > $this->serverInfo->maxPayload) {
            throw new InvalidArgumentException('max payload size exceeded');
        }

        $proto = Helper::Pub->value;
        if (count($message->header)) {
            $proto = Helper::Hpub->value;
        }

        $proto .= $message->subject . ' ';
        if ($message->reply !== null) {
            $proto .= $message->reply . ' ';
        }

        if (count($message->header)) {
            $proto .= $message->headerSize() . ' ';
        }

        if ($msgSize > 0) {
            $proto .= $msgSize . Helper::Crlf->value;
        }

        $this->writer->write($proto, $message->header(), $message->data, Helper::Crlf->value);
    }

    public function listen(int $quantity = 0): void
    {
        $totalHandled = 0;
        $info  = stream_get_meta_data($this->streamSocket);
        while (is_resource($this->streamSocket) === true && feof($this->streamSocket) === false && empty($info['timed_out']) === true) {
            $line = $this->reader->read();
            if ($line === null) {
                return;
            }

            if (str_starts_with($line, Operation::Ping->value)) {
                $this->writer->pong();
                continue;
            }

            if (str_starts_with($line, Operation::Msg->value)) {
                $totalHandled++;
                $message = $this->parser->parse($this->reader, $line);
                $this->deliver($message);
                if ($quantity && $totalHandled >= $quantity) {
                    return;
                }
            }

            $info = stream_get_meta_data($this->streamSocket);
        }
    }

    private function deliver(Message $message): void
    {
        $f = $this->subscriptions[$message->subscriptionId]->handler;
        $f($this->reader, $this->writer, $message);
    }

    /**
     * Request does a request and executes a callback with the response.
     *
     * @param string   $subject  Message topic.
     * @param string   $payload  Message data.
     * @param \Closure $callback Closure to be executed as callback.
     *
     * @return void
     */
    public function request($subject, $payload, \Closure $callback)
    {
        $inbox = uniqid('_INBOX.');
        $sid   = $this->subscribe(
            $inbox,
            $callback
        );
        $this->unsubscribe($sid, 1);
        $this->publish($subject, $payload, $inbox);
        $this->wait(1);
    }
//
//
//    /**
//     * Publish publishes the data argument to the given subject.
//     *
//     * @param string $subject Message topic.
//     * @param string $payload Message data.
//     * @param string $inbox   Message inbox.
//     *
//     * @throws Exception If subscription not found.
//     * @return void
//     *
//     */
//    public function publish($subject, $payload = null, $inbox = null)
//    {
//        $msg = 'PUB '.$subject;
//        if ($inbox !== null) {
//            $msg = $msg.' '.$inbox;
//        }
//
//        $msg = $msg.' '.strlen($payload);
//        $this->send($msg."\r\n".$payload);
//        $this->pubs += 1;
//    }
//

    /**
     * @throws JsonException
     */
    public function reconnect(): void
    {
        $this->stats->reconnects++;
        if ($this->options->maxReconnects < $this->stats->reconnects) {
            throw new RuntimeException('max reconnections reached');
        }
        $this->close();
        $this->connect();
    }

    /** Close will close the connection to the server. */
    public function close(): void
    {
        if ($this->streamSocket === null) {
            return;
        }

        fclose($this->streamSocket);
        $this->streamSocket = null;
    }
}
