<?php

namespace Andrey\Nats;

use Andrey\JsonHandler\Attributes\JsonObjectAttribute;

#[JsonObjectAttribute]
class Stats
{
    public int $inMsgs = 0;
    public int $outMsgs = 0;
    public int $inBytes = 0;
    public int $outBytes = 0;
    public int $reconnects = 0;
}
