<?php

namespace Ticketing\Common\Application\Security;


use Ramsey\Uuid\Rfc4122\UuidV4;
use Ramsey\Uuid\UuidInterface;

class AuthUserDto
{
    public function __construct(
        public readonly UuidInterface $id,
        public readonly string $name,
    )
    {
    }

    public static function createFromJWTPayload(array $data): self
    {
        return new self(UuidV4::fromString($data['id']), $data['name']);
    }

    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
        ];
    }
}