<?php

namespace Ticketing\Common\Infrastructure\Query;

use Symfony\Component\Messenger\HandleTrait;
use Symfony\Component\Messenger\MessageBusInterface;
use Ticketing\Common\Application\Query\QueryBusInterface;
use Ticketing\Common\Application\Query\QueryInterface;

class QueryBus implements QueryBusInterface
{
    use HandleTrait;

    private MessageBusInterface $messageBus;

    public function __construct(
        MessageBusInterface $messageBus,
    ) {
        $this->messageBus = $messageBus;
    }

    public function ask(QueryInterface $query): mixed
    {
        return $this->handle($query);
    }
}
