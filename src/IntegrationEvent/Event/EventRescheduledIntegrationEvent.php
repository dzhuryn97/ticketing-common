<?php

namespace Ticketing\Common\IntegrationEvent\Event;

use Ramsey\Uuid\UuidInterface;
use Ticketing\Common\Application\EventBus\AbstractIntegrationEvent;

class EventRescheduledIntegrationEvent extends AbstractIntegrationEvent
{
    public function __construct(
        public readonly UuidInterface $id,
        public readonly \DateTimeImmutable $occurredOn,
        public readonly UuidInterface $eventId,
        public readonly \DateTimeImmutable $startsAt,
        public readonly ?\DateTimeImmutable $endsAt,
    ) {
        parent::__construct($id, $this->occurredOn);
    }
}
