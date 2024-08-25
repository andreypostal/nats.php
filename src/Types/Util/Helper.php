<?php
namespace Andrey\Nats\Types\Util;

enum Helper: string
{
    case Crlf = "\r\n";
    case Empty = '';
    case Space = ' ';
    case Pub = 'PUB ';
    case Hpub = 'HPUB ';
    case HeaderLine = "NATS/1.0\r\n";
}
