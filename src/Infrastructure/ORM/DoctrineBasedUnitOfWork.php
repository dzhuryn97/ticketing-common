<?php

namespace Ticketing\Common\Infrastructure\ORM;

use Doctrine\ORM\EntityManagerInterface;
use Ticketing\Common\Application\UnitOfWork;

class DoctrineBasedUnitOfWork implements UnitOfWork
{
    public function __construct(
        private readonly EntityManagerInterface $em,
    ) {
    }

    public function beginTransaction(): void
    {
        $this->em->beginTransaction();
    }

    public function commit(): void
    {
        $this->em->commit();
    }

    public function rollback(): void
    {
        $this->em->rollback();
    }
}
