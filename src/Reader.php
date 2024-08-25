<?php
namespace Andrey\Nats;

use Andrey\Nats\Types\Operation;
use Andrey\Nats\Types\Util\Helper;
use InvalidArgumentException;

class Reader
{
    /**
     * @param resource $stream
     */
    public function __construct(private $stream, private readonly Options $options)
    { }

    public function readProto(): string
    {
        return $this->read();
    }

    public function readPong(): bool
    {
        $pong = $this->read(strlen(Operation::Pong->proto()));
        if ($pong === Operation::Pong->proto()) {
            return true;
        }
        $this->handleError($pong);
        return false;
    }

    public function read(?int $len = null): ?string
    {
        if ($len === null) {
            return fgets($this->stream);
        }

        $loops = ceil($len / $this->options->chunkSize);
        $data = '';
        for ($i = 0; $i < $loops; $i++) {
            $chunkSize = min($len, $this->options->chunkSize);
            $chunk = fread($this->stream, $chunkSize);
            $len -= strlen($chunk);
            $data .= $chunk;
        }

        return $len !== 0 ? null : $data;
    }

    private function handleError(string $proto): void
    {
        $proto = rtrim($proto, Helper::Crlf);
        if (str_starts_with($proto, Operation::Err->value)) {
            throw new InvalidArgumentException(sprintf("nats: expected '%s', got '%s'", Operation::Pong->value, $proto));
        }
    }
}
