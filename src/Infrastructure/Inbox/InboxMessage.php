<?php

namespace Ticketing\Common\Infrastructure\Inbox;

use Ramsey\Uuid\UuidInterface;
use Ticketing\Common\Application\EventBus\AbstractIntegrationEvent;

class InboxMessage
{

    public function __construct(
        public readonly UuidInterface            $inboxMessageId,
        public readonly AbstractIntegrationEvent $integrationsEvent,
        public readonly \DateTimeImmutable       $occurredOn
    )
    {
    }

    public static function fromIntegrationEvent(AbstractIntegrationEvent $integrationsEvent)
    {
        return new self(
            $integrationsEvent->id,
            $integrationsEvent,
            $integrationsEvent->occurredOn
        );
    }
}