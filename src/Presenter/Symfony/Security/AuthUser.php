<?php

declare(strict_types=1);

namespace Ticketing\Common\Presenter\Symfony\Security;

use Lexik\Bundle\JWTAuthenticationBundle\Security\User\JWTUserInterface;
use Symfony\Component\Security\Core\User\UserInterface;

final class AuthUser implements UserInterface, JWTUserInterface
{
    public const string DEFAULT_ROLE = 'ROLE_USER';

    public function __construct(
        private readonly string $id,
        private readonly string $name,
        private readonly array $roles,
    ) {
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getRoles(): array
    {
        return array_merge([self::DEFAULT_ROLE], $this->roles);
    }

    public function eraseCredentials(): void
    {
    }

    public function getUserIdentifier(): string
    {
        return $this->id;
    }

    public static function createFromPayload($username, array $payload): self
    {
        return new self($username, $payload['name'], $payload['roles']);
    }
}
