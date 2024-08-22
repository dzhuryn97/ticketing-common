<?php

namespace Ticketing\Common\Infrastructure\Security;

use Ramsey\Uuid\Rfc4122\UuidV4;
use Symfony\Bundle\SecurityBundle\Security as SecurityComponent;
use Ticketing\Common\Application\Security\AuthUserDto;
use Ticketing\Common\Application\Security\Security;
use Ticketing\Common\Presenter\Symfony\Security\AuthUser;


class SecurityService implements Security
{
    public function __construct(
        private readonly SecurityComponent $security,
    )
    {
    }

    public function isAuthenticated(): bool
    {
        return null !== $this->connectedUser();
    }

    public function connectedUser(): ?AuthUserDto
    {
        $authenticatedUser = $this->security->getUser();

        if (!$authenticatedUser instanceof AuthUser) {
            return null;
        }

        return new AuthUserDto(UuidV4::fromString($authenticatedUser->getUserIdentifier()), $authenticatedUser->getEmail());
    }
}