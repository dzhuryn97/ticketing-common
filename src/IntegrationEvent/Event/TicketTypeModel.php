<?php

namespace Ticketing\Common\IntegrationEvent\Event;

use Ramsey\Uuid\UuidInterface;

class TicketTypeModel
{

    public function __construct(
        public readonly UuidInterface $id,
        public readonly UuidInterface $eventId,
        public readonly string $name,
        public readonly float $price,
        public readonly string $currency,
        public readonly int $quantity,

    )
    {
    }

}