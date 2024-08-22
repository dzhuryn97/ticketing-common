<?php

namespace Ticketing\Common\Application\EventBus;

use Ramsey\Uuid\UuidInterface;

abstract class AbstractIntegrationEvent implements IntegrationEventInterface
{

    public function __construct(
        public readonly UuidInterface $id,
        public readonly \DateTimeImmutable $occurredOn,
    )
    {
    }
}