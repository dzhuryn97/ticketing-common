<?php

namespace Ticketing\Common\Application\EventBus;

interface EventBusInterface
{
    public function publish(IntegrationEventInterface $integrationsEvent): void;
}
