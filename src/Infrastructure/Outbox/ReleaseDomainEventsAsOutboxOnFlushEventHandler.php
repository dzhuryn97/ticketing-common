<?php

namespace Ticketing\Common\Infrastructure\Outbox;

use Doctrine\ORM\Event\OnFlushEventArgs;
use Symfony\Component\Messenger\MessageBusInterface;
use Ticketing\Common\Domain\DomainEntity;

class ReleaseDomainEventsAsOutboxOnFlushEventHandler
{
    public function __construct(
        private readonly MessageBusInterface $outboxMessageBus,
    ) {
    }

    public function onFlush(OnFlushEventArgs $event)
    {
        $uow = $event->getObjectManager()->getUnitOfWork();

        $entities = $this->getManagedEntities($uow);
        $domainEntities = $this->filterDomainEntities($entities);

        $this->handleDomainEntities($domainEntities);
    }

    private function getManagedEntities(\Doctrine\ORM\UnitOfWork $uow): array
    {
        $entities = [];
        foreach ($uow->getIdentityMap() as $entitiesOfClass) {
            foreach ($entitiesOfClass as $_entity) {
                $entities[] = $_entity;
            }
        }

        return $entities;
    }

    private function filterDomainEntities(array $entities): array
    {
        return array_filter($entities, function ($entity) {
            return $entity instanceof DomainEntity;
        });
    }

    private function handleDomainEntities(array $domainEntities)
    {
        /** @var DomainEntity[] $domainEntities */
        foreach ($domainEntities as $domainEntity) {

            $domainEvents = $domainEntity->releaseDomainEvents();
            foreach ($domainEvents as $domainEvent) {
                $this->dispatchAsOutbox($domainEvent);
            }
        }
    }

    private function dispatchAsOutbox(\Ticketing\Common\Domain\DomainEvent $domainEvent)
    {
        $outboxMessage = new OutboxMessage($domainEvent);
        $this->outboxMessageBus->dispatch($outboxMessage);
    }
}
