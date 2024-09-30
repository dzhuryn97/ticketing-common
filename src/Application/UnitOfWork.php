<?php

namespace Ticketing\Common\Application;

interface UnitOfWork
{
    public function beginTransaction(): void;

    public function commit(): void;

    public function rollback(): void;
}
