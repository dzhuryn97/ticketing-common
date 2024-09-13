<?php

namespace Ticketing\Common\Domain;

use Ramsey\Uuid\Rfc4122\UuidV4;
use Ramsey\Uuid\UuidInterface;

abstract class DomainEvent
{
    public readonly UuidInterface $id;
    public readonly \DateTimeImmutable $occurredOn;

    public function __construct(
    ) {
        $this->id = UuidV4::uuid4();
        $this->occurredOn = new \DateTimeImmutable();
    }
}
