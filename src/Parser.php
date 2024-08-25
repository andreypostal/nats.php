<?php

namespace Andrey\Nats;

use Andrey\Nats\Types\Message;
use Andrey\Nats\Types\Util\Helper;

class Parser
{
    public function parse(Reader $reader, string $raw): Message
    {
        $parts = explode(Helper::Space->value, $raw);
        $subject = null;
        $reply = null;
        $length  = (int) trim($parts[3]);
        $sid = (int) trim($parts[2]);

        $nParts = count($parts);
        if ($nParts === 5) {
            $length  = (int) trim($parts[4]);
            $reply = trim($parts[3]);
            $subject = trim($parts[1]);
        } else if (count($parts) === 4) {
            $subject = $parts[1];
        }

        $payload = $reader->read($length);
        return new Message(
            data: $payload,
            subject: $subject,
            reply: $reply,
            subscriptionId: $sid,
        );
    }
}
