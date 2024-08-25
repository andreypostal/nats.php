<?php
namespace Andrey\Nats\Encoders;

interface PayloadEncoder
{
    /**
     * Encodes a message.
     */
    public function encode(string $payload): string;

    /**
     * Decodes a message.
     */
    public function decode(string $payload): string;
}
