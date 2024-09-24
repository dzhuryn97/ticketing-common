<?php

namespace Ticketing\Common\Application\Security;

use Ramsey\Uuid\Rfc4122\UuidV4;
use Ramsey\Uuid\UuidInterface;

readonly class AuthUserDto
{
    public function __construct(
        public UuidInterface $id,
        public string $name,
    ) {
    }

    /**
     * @deprecated
     */
    public static function createFromJWTPayload(array $data): self
    {
        return new self(UuidV4::fromString($data['id']), $data['name']);
    }

    /**
     * @deprecated
     */
    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
        ];
    }
}
