<?php

namespace Ticketing\Common\Application\Caching;

/**
 * @template T
 */
interface CacheInterface
{
    /**
     * @return ?T
     */
    public function find(string $cacheKey);

    /**
     * @param T $value
     */
    public function set(string $cacheKey, $value): void;
}
