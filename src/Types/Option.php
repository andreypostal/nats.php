<?php

namespace Andrey\Nats\Types;

use Andrey\Nats\Options;

interface Option
{
    public function __invoke(Options $options): Options;
}
