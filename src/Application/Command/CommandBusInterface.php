<?php

namespace Ticketing\Common\Application\Command;

interface CommandBusInterface
{
    /**
     * @template T
     * @param CommandInterface<T> $command
     * @return T
     */
    public function dispatch(CommandInterface $command);
}