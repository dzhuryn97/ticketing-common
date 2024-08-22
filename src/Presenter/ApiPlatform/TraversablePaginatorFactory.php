<?php

namespace Ticketing\Common\Presenter\ApiPlatform;

use ApiPlatform\State\Pagination\TraversablePaginator;
use Ticketing\Common\Domain\Dto\PaginatedResults;

class TraversablePaginatorFactory
{
    public static function create(PaginatedResults $pagination)
    {
        return new TraversablePaginator(
            $pagination->data,
            $pagination->currentPage,
            $pagination->pageSize,
            $pagination->totalItems
        );
    }
}