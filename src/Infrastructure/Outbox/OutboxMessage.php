<?php

namespace Ticketing\Common\Infrastructure\Outbox;

use Ticketing\Common\Domain\DomainEvent;

class OutboxMessage
{
    public function __construct(public readonly DomainEvent $domainEvent)
    {
    }
}
