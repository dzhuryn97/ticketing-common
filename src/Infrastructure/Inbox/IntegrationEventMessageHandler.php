<?php

namespace Ticketing\Common\Infrastructure\Inbox;

use Ticketing\Common\Application\EventBus\AbstractIntegrationEvent;

class IntegrationEventMessageHandler
{

    public function __construct(
        private readonly InboxMessageStorage $inboxMessageStorage
    )
    {
    }

    public function __invoke(AbstractIntegrationEvent $integrationsEvent)
    {
        $inboxMessage = InboxMessage::fromIntegrationEvent($integrationsEvent);
        $this->inboxMessageStorage->send($inboxMessage);
    }
}