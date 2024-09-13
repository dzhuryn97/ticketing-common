<?php

namespace Ticketing\Common\IntegrationEvent\Event;

use Ramsey\Uuid\UuidInterface;
use Ticketing\Common\Application\EventBus\AbstractIntegrationEvent;

class TicketTypePriceChangedIntegrationEvent extends AbstractIntegrationEvent
{
    public function __construct(
        UuidInterface $id,
        \DateTimeImmutable $occurredOn,
        public readonly UuidInterface $ticketTypeId,
        public readonly float $price,
    ) {
        parent::__construct($id, $occurredOn);
    }
}
