<?php
namespace Andrey\Nats;

use Andrey\Nats\Types\Operation;
use RuntimeException;

class Writer
{
    /**
     * @param resource $stream
     */
    public function __construct(private $stream)
    { }

    public function write(string ...$msgs): int
    {
        $size = 0;
        foreach ($msgs as $msg) {
            $size += $this->send($msg);
        }
        return $size;
    }

    public function pong(): int
    {
        return $this->send(Operation::Pong->proto());
    }

    private function send(string $msg): int
    {
        $total = strlen($msg);
        $len = $total;
        while (true) {
            $written = fwrite($this->stream, $msg);
            if ($written === false) {
                throw new RuntimeException('error while sending data to the server');
            }

            if ($written === 0) {
                throw new RuntimeException('broken pipe or closed connection');
            }

            $len -= $written;
            if ($len <= 0) {
                return $total;
            }
            $msg = substr($msg, -$len);
        }
    }
}
