<?php

namespace Ticketing\Common\IntegrationEvent\Event;

use Ramsey\Uuid\UuidInterface;
use Ticketing\Common\Application\EventBus\AbstractIntegrationEvent;

class EventPublishedIntegrationEvent extends AbstractIntegrationEvent
{
    /**
     * @param array<TicketTypeModel> $ticketsType
     */
    public function __construct(
        UuidInterface                       $id,
        \DateTimeImmutable                  $occurredOn,
        public readonly UuidInterface       $eventId,
        public readonly string              $title,
        public readonly string              $description,
        public readonly string              $location,
        public readonly \DateTimeImmutable  $startsAt,
        public readonly ?\DateTimeImmutable $endsAt,
        public readonly array               $ticketsType
    )
    {
        parent::__construct($id, $occurredOn);
    }
}