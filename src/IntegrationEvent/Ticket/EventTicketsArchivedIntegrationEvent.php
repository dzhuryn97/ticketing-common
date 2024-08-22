<?php

namespace Ticketing\Common\IntegrationEvent\Ticket;

use Ramsey\Uuid\UuidInterface;
use Ticketing\Common\Application\EventBus\AbstractIntegrationEvent;

class EventTicketsArchivedIntegrationEvent extends AbstractIntegrationEvent
{
    public function __construct(UuidInterface $id, \DateTimeImmutable $occurredOn, public readonly UuidInterface $eventId)
    {
        parent::__construct($id, $occurredOn);
    }
}