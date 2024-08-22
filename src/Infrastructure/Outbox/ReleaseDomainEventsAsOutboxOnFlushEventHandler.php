<?php

namespace Ticketing\Common\Infrastructure\Outbox;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Event\OnFlushEventArgs;
use Symfony\Component\Messenger\MessageBusInterface;
use Ticketing\Common\Domain\DomainEntity;

class ReleaseDomainEventsAsOutboxOnFlushEventHandler
{

    public function __construct(
        private readonly MessageBusInterface $outboxMessageBus,
        private readonly EntityManagerInterface $entityManager
    )
    {
    }

    public function onFlush(OnFlushEventArgs $event)
    {
        $oof = $event->getObjectManager()->getUnitOfWork();

        $entities = array_merge(
            $oof->getScheduledEntityInsertions(),
            $oof->getScheduledEntityUpdates(),
            $oof->getScheduledEntityDeletions(),
        );

        $domainEntities = array_filter($entities, function ($entity) {
            return $entity instanceof DomainEntity;
        });

        /** @var DomainEntity[] $domainEntities */
        foreach ($domainEntities as $domainEntity) {

            $domainEvents = $domainEntity->releaseDomainEvents();
            foreach ($domainEvents as $domainEvent) {
                $outboxMessage = new OutboxMessage($domainEvent);
                $this->outboxMessageBus->dispatch($outboxMessage);
            }
        }
    }
}