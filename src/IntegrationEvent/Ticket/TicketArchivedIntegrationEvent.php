<?php

namespace Ticketing\Common\IntegrationEvent\Ticket;

use Ramsey\Uuid\UuidInterface;
use Ticketing\Common\Application\EventBus\AbstractIntegrationEvent;

class TicketArchivedIntegrationEvent extends AbstractIntegrationEvent
{
    public function __construct(
        UuidInterface $id,
        \DateTimeImmutable $occurredOn,
        public readonly UuidInterface $ticketId,
        public readonly string $code,
    ) {
        parent::__construct($id, $occurredOn);
    }
}
