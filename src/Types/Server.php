<?php
namespace Andrey\Nats\Types;

class Server
{
    public function __construct(
        public string $url,
        public bool $didConnect,
        public int $reconnects,
        public string $tlsName,
    ) { }
}
