<?php

namespace Ticketing\Common\Domain\Dto;

/**
 * @template T
 *
 */
class PaginatedResults
{
    /**
     * @param T[] $data
     */
    public function __construct(
        public  array $data,
        public readonly int      $currentPage,
        public readonly int      $pageSize,
        public readonly int      $totalPages,
        public readonly int      $totalItems
    )
    {
    }
}