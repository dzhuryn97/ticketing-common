<?php

namespace Ticketing\Common\IntegrationEvent\Event;

use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\UuidInterface;
use Ticketing\Common\Application\EventBus\AbstractIntegrationEvent;

class EventCancellationCompletedIntegrationEvent extends AbstractIntegrationEvent
{
    public function __construct(
        public readonly UuidInterface $eventId,
    ) {
        parent::__construct(Uuid::uuid4(), new \DateTimeImmutable());
    }
}
