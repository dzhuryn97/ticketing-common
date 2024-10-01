<?php

namespace Ticketing\Common\Application\Security;

use Ramsey\Uuid\UuidInterface;

readonly class AuthUserDto
{
    public function __construct(
        public UuidInterface $id,
        public string $name,
    ) {
    }
}
