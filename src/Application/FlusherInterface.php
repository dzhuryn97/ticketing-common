<?php

namespace Ticketing\Common\Application;

/**
 * @deprecated use UnitOfWorkInterface
 */
interface FlusherInterface
{
    public function flush(): void;

    public function beginTransaction(): void;

    public function commit(): void;
}
