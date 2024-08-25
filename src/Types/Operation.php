<?php
namespace Andrey\Nats\Types;

use Andrey\Nats\Types\Util\Helper;
use LogicException;

enum Operation: string
{
    case Ok = '+OK';
    case Err = '-ERR';
    case Pong = 'PONG';
    case Info = 'INFO';
    case Ping = 'PING';
    case Sub = 'SUB';
    case Unsub = 'UNSUB';
    case Connect = 'CONNECT';
    case Msg = 'MSG';

    public function proto(): string
    {
        return match ($this) {
            self::Ok, self::Pong, self::Ping => $this->value,
            self::Msg, self::Err, self::Info => throw new LogicException('op do not have a prototype'),
            self::Sub => $this->value . ' %s %s %d',
            self::Unsub => $this->value . ' %d %s',
            self::Connect => $this->value . ' %s',
        } . Helper::Crlf->value;
    }
}
