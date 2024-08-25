<?php
namespace Andrey\Nats\Types;

use Andrey\JsonHandler\Attributes\JsonItemAttribute;
use Andrey\JsonHandler\Attributes\JsonObjectAttribute;
use Andrey\JsonHandler\JsonHandler;
use JsonSerializable;

#[JsonObjectAttribute]
class ServerInfo implements JsonSerializable
{
    #[JsonItemAttribute(key: 'server_id')]
    public string $id;
    #[JsonItemAttribute(key: 'server_name')]
    public string $name;
    public string $version;
    public int $proto;
    public string $host;
    public int $port;
    public bool $headers;
    #[JsonItemAttribute(key: 'max_payload')]
    public int $maxPayload;
    #[JsonItemAttribute(key: 'jetstream')]
    public ?bool $jetStream;
    #[JsonItemAttribute(key: 'auth_required')]
    public ?bool $authRequired;
    #[JsonItemAttribute(key: 'tls_required')]
    public ?bool $tlsRequired;
    #[JsonItemAttribute(key: 'tls_available')]
    public ?bool $tlsAvailable;
    #[JsonItemAttribute(key: 'client_id')]
    public ?int $clientId;
    #[JsonItemAttribute(key: 'client_ip')]
    public ?string $clientIp;
    public ?string $nonce;
    public ?string $cluster;
    /** @var null|string[] */
    #[JsonItemAttribute(key: 'connect_urls')]
    public ?array $connectUrls;
    #[JsonItemAttribute(key: 'ldm')]
    public ?bool $lameDuckMode;

    public function jsonSerialize(): array
    {
        return (new JsonHandler())->serialize($this);
    }
}
