<?php
namespace Andrey\Nats\Types;

use Andrey\Nats\Types\Util\Helper;

class Message
{
    public function __construct(
        public string $data,
        public ?string $subject = null,
        public ?string $reply = null,
        public array $header = [],
        public ?int $subscriptionId = null,
    ) { }

    public function size(): int
    {
        return strlen($this->subject ?? '') + strlen($this->reply) + $this->headerSize() + strlen($this->data);
    }

    public function headerSize(): int
    {
        return strlen($this->header());
    }

    public function header(): string
    {
        if (count($this->header) === 0) {
            return '';
        }

        $h = Helper::HeaderLine->value;

        foreach ($this->header as $header => $values) {
            foreach ($values as $value) {
                $h .= $header . ': ' . trim($value) . Helper::Crlf->value;
            }
        }
        return $h . Helper::Crlf->value;
    }
}
