<?php

namespace Ticketing\Common\Infrastructure\Inbox;

use Doctrine\ORM\Tools\Event\GenerateSchemaEventArgs;

class InboxTableSchemaConfiguratorEventListener
{

    public function __construct(
        private readonly Connection $connection
    )
    {
    }

    public function __invoke(GenerateSchemaEventArgs $eventArgs)
    {
        $this->connection->updateSchema($eventArgs->getSchema());
    }
}