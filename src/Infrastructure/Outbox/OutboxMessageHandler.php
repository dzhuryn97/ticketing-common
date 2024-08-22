<?php

namespace Ticketing\Common\Infrastructure\Outbox;

use Symfony\Component\Messenger\Attribute\AsMessageHandler;
use Symfony\Component\Messenger\MessageBusInterface;

#[AsMessageHandler]
class OutboxMessageHandler
{
    public function __construct(
        private readonly MessageBusInterface $messageBus
    )
    {
    }

    public function __invoke(OutboxMessage $outboxMessage)
    {
        $this->messageBus->dispatch($outboxMessage->domainEvent);
    }
}