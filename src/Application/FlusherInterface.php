<?php

namespace Ticketing\Common\Application;

interface FlusherInterface
{
    public function flush(): void;

    public function beginTransaction(): void;

    public function commit(): void;
}
