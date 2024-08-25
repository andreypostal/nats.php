<?php
namespace Andrey\Nats;

use Andrey\Nats\Types\ConnectInfo;

class Options
{
    public string $name;

    public bool $canReconnect = true;

    public int $maxReconnects = 60;

    /** Time interval between pings in seconds */
    public int $pingInterval = 120;

    public int $chunkSize = 1024;

    /** Time to wait before reconnection in seconds */
    public int $reconnectWait = 2;

    /** @var string[] */
    public array $servers = [ 'tcp://127.0.0.1:4222' ];

    public bool $secure = false;

    /** Timeout for operations in seconds */
    public int $timeout = 30;

    /** Timeout for a drain operation to complete in seconds */
    public int $drainTimeout = 30;

    /** Timeout for write operations in seconds */
    public int $flusherTimeout = 30;

    /** Max pending pings awaiting server response */
    public int $maxPingsOut = 2;

    public bool $retryOnFailedConnect;

    public ConnectInfo $connectInfo;
}
