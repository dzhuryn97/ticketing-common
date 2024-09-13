<?php

namespace Ticketing\Common\Infrastructure;

use Doctrine\ORM\EntityManagerInterface;
use Ticketing\Common\Application\FlusherInterface;

class Flusher implements FlusherInterface
{
    private EntityManagerInterface $entityManager;

    public function __construct(
        EntityManagerInterface $entityManager,
    ) {
        $this->entityManager = $entityManager;
    }

    public function flush(): void
    {
        $this->entityManager->flush();
    }

    public function beginTransaction(): void
    {
        $this->entityManager->beginTransaction();
    }

    public function commit(): void
    {
        $this->entityManager->commit();
    }
}
