<?php
namespace Andrey\Nats\Encoders;

use JsonException;

class JsonEncoder implements PayloadEncoder
{
    /**
     * @throws JsonException
     */
    public function encode(string $payload): string
    {
        return json_encode($payload, JSON_THROW_ON_ERROR);
    }

    /**
     * @throws JsonException
     */
    public function decode(string $payload): string
    {
        return json_decode($payload, true, 512,JSON_THROW_ON_ERROR);
    }
}
