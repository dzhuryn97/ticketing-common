<?php

namespace Ticketing\Common\Infrastructure\Outbox;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Event\PostFlushEventArgs;
use Symfony\Component\Messenger\MessageBusInterface;
use Ticketing\Common\Domain\DomainEntity;

/**
 * For using in test env.
 */
class ReleaseDomainEventsAsOutboxOnPostFlushEventHandler
{
    public function __construct(
        private readonly MessageBusInterface $outboxMessageBus,
        private readonly EntityManagerInterface $entityManager,
    ) {
    }

    public function postFlush(PostFlushEventArgs $event)
    {
        $uow = $event->getObjectManager()->getUnitOfWork();

        $domainEntities = [];
        foreach ($uow->getIdentityMap() as $entities) {
            $domainEntities = array_merge(array_filter($entities, function ($entity) {
                return $entity instanceof DomainEntity;
            }), $domainEntities);
        }

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
