<?php

namespace Ticketing\Common\Domain\Exception;

class EntityNotFoundException extends BusinessException
{
    public function __construct($id, string $class)
    {
        $parts = explode('\\', $class);
        $className = end($parts);

        parent::__construct(sprintf('Entity of type %s with identifier %s not found', $className, $id), 'EntityNotFound');
    }
}
