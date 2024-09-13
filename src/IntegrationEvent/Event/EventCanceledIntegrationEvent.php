<?php

namespace Ticketing\Common\IntegrationEvent\Event;

use Ramsey\Uuid\UuidInterface;
use Ticketing\Common\Application\EventBus\AbstractIntegrationEvent;

class EventCanceledIntegrationEvent extends AbstractIntegrationEvent
{
    public function __construct(
        UuidInterface $id,
        \DateTimeImmutable $occurredOn,
        public readonly UuidInterface $eventId,
    ) {
        parent::__construct($id, $occurredOn);
    }
}
