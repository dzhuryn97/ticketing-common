<?php

namespace Ticketing\Common\Domain\Exception;

class EntityNotFoundException extends \Exception
{
    public function __construct(string $message = '')
    {
        parent::__construct($message);
    }

    public static function fromClassAndId($id, string $class): self
    {
        $parts = explode('\\', $class);
        $className = end($parts);

        return new self(sprintf('Entity of type %s with identifier %s not found', $className, $id));
    }
}
