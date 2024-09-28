<?php

namespace Ticketing\Common\Domain\Exception;

abstract class BusinessException extends \RuntimeException
{
    protected string $type;

    public function __construct(string $message = '', string $type = '', int $code = 0, ?\Throwable $previous = null)
    {
        $this->type = $type;
        parent::__construct($message, $code, $previous);
    }

    public function getType(): string
    {
        return $this->type;
    }
}
