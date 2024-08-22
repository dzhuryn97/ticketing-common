<?php

namespace Ticketing\Common\Infrastructure\Command;

use Symfony\Component\Messenger\HandleTrait;
use Symfony\Component\Messenger\MessageBusInterface;
use Ticketing\Common\Application\Command\CommandBusInterface;
use Ticketing\Common\Application\Command\CommandInterface;

class CommandBus implements CommandBusInterface
{
    use HandleTrait;

    public function __construct(MessageBusInterface $messageBus)
    {
        $this->messageBus = $messageBus;
    }

    public function dispatch(CommandInterface $command)
    {
        return $this->handle($command);
    }
}