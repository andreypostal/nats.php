<?php
namespace Andrey\Nats\Types;

use Andrey\JsonHandler\Attributes\JsonItemAttribute;
use Andrey\JsonHandler\Attributes\JsonObjectAttribute;

#[JsonObjectAttribute]
class ConnectInfo
{
    public bool $verbose = false;
    public bool $pedantic = false;
    #[JsonItemAttribute(key: 'jwt')]
    public ?string $userJwt = null;
    #[JsonItemAttribute(key: 'nkey')]
    public ?string $nKey = null;
    #[JsonItemAttribute(key: 'sig')]
    public ?string $signature = null;
    public ?string $user = null;
    #[JsonItemAttribute(key: 'pass')]
    public ?string $password = null;
    #[JsonItemAttribute(key: 'auth_token')]
    public ?string $token = null;
    #[JsonItemAttribute(key: 'tls_required')]
    public bool $tls = false;
    public ?string $name = null;
    private string $lang = 'php';
    public string $version = '1.37.0';
    public int $protocol = 1;
    public bool $echo = false;
    public bool $headers = false;
    #[JsonItemAttribute(key: 'no_responders')]
    public bool $noResponders = false;
}
