<?php
namespace Andrey\Nats\Types;

enum Status: string
{
    case Disconnected = 'DISCONNECTED';
    case Connected = 'CONNECTED';
    case Closed = 'CLOSED';
    case Reconnecting = 'RECONNECTING';
    case Connecting = 'CONNECTING';
    case DrainingSubs = 'DRAINING_SUBS';
    case DrainingPubs = 'DRAINING_PUBS';
}
