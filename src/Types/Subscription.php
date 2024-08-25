<?php
namespace Andrey\Nats\Types;

use Closure;

class Subscription
{
    public function __construct(
        public int $sid,
        public string $subject,
        public string $queue,
        public Closure $handler,
    ) { }
}
