<?php
namespace Andrey\Nats\Types\Util;

class Valid
{
    public static function Subject(?string $subject): bool
    {
        if ($subject === '' || $subject === null) {
            return false;
        }

        $invalidChars = [Helper::Space->value, "\t", "\n", "\r"];
        foreach ($invalidChars as $c) {
            if (str_contains($subject, $c)) {
                return false;
            }
        }

        $parts = explode('.', $subject);
        if (in_array('', $parts, true)) {
            return false;
        }
        return true;
    }

    public static function Queue(string $queue): bool
    {
        $invalidChars = [Helper::Space->value, "\t", "\n", "\r"];
        foreach ($invalidChars as $c) {
            if (str_contains($queue, $c)) {
                return false;
            }
        }
        return true;
    }
}
