<?php

namespace Ticketing\Common\IntegrationEvent\User;

use Ramsey\Uuid\UuidInterface;
use Ticketing\Common\Application\EventBus\AbstractIntegrationEvent;

class UserUpdatedIntegrationEvent extends AbstractIntegrationEvent
{
    public function __construct(
        UuidInterface $id,
        \DateTimeImmutable $occurredOn,
        public readonly UuidInterface $userId,
        public readonly string $name,
        public readonly string $email,
    ) {
        parent::__construct($id, $occurredOn);
    }
}
