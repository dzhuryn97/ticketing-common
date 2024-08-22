<?php

namespace Ticketing\Common\Infrastructure\EventBus;

use Symfony\Component\Messenger\MessageBusInterface;
use Ticketing\Common\Application\EventBus\IntegrationEventInterface;

class EventBus implements \Ticketing\Common\Application\EventBus\EventBusInterface
{
    public function __construct(
        private readonly MessageBusInterface $distributedMessageBus
    )
    {
    }

    public function publish(IntegrationEventInterface $integrationsEvent): void
    {
       $this->distributedMessageBus->dispatch($integrationsEvent);
    }
}