<?php

namespace Ticketing\Common\Infrastructure\ORM;

use Doctrine\ORM\Tools\Pagination\Paginator as OrmPaginator;
use Ticketing\Common\Domain\Dto\PaginatedResults;

class Paginator
{
    public static function paginate($query, int $page = 1, int $pageSize = 10): PaginatedResults
    {
        $offset = $pageSize * ($page - 1);
        $paginator = new OrmPaginator($query);

        $paginator
            ->getQuery()
            ->setFirstResult($offset)
            ->setMaxResults($pageSize);


        $lastPage = (int) ceil($paginator->count() / $paginator->getQuery()->getMaxResults());

        return new PaginatedResults(
            iterator_to_array($paginator->getIterator()),
            $page,
            $pageSize,
            $lastPage,
            $paginator->count()
        );
    }
}
