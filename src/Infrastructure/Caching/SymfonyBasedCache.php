<?php

namespace Ticketing\Common\Infrastructure\Caching;

use Psr\Cache\CacheItemInterface;
use Psr\Cache\CacheItemPoolInterface;
use Ticketing\Common\Application\Caching\CacheInterface;

class SymfonyBasedCache implements CacheInterface
{
    public function __construct(
        private readonly CacheItemPoolInterface $cacheItemPool
    )
    {
    }

    public function find(string $cacheKey)
    {
        $cacheItem = $this->getCacheItem($cacheKey);
        return $cacheItem->get();
    }

    public function set(string $cacheKey, $value): void
    {
        $cacheItem = $this->getCacheItem($cacheKey);
        $cacheItem->set($value);
        $this->cacheItemPool->save($cacheItem);
    }

    private function getCacheItem(string $cacheKey): CacheItemInterface
    {
        return $this->cacheItemPool->getItem($cacheKey);
    }
}