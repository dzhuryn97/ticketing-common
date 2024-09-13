<?php

declare(strict_types=1);

namespace Ticketing\Common\Presenter\Symfony\Security;

use Lexik\Bundle\JWTAuthenticationBundle\Security\User\JWTUserInterface;
use Symfony\Component\Security\Core\User\UserInterface;

final class AuthUser implements UserInterface, JWTUserInterface
{
    private string $id;

    private array $roles;

    public function __construct(
        string $id,
        array $roles,
    ) {
        $this->id = $id;
        $this->roles = $roles;
    }

    public function getRoles(): array
    {
        return $this->roles;
    }

    public function eraseCredentials(): void
    {
    }

    public function getUserIdentifier(): string
    {
        return $this->id;
    }

    public static function createFromPayload($username, array $payload)
    {
        $obj = new self($username, $payload['roles']);

        return $obj;
    }
}
