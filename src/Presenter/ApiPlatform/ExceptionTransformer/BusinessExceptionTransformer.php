<?php

namespace Ticketing\Common\Presenter\ApiPlatform\ExceptionTransformer;

use Ticketing\Common\Domain\Exception\BusinessException;
use Ticketing\Common\Presenter\ApiPlatform\ErrorResource\BusinessErrorResource;

class BusinessExceptionTransformer implements ExceptionTransformerInterface
{
    /**
     * @param BusinessException $exception
     */
    public function transform(\Throwable $exception, int $status)
    {
        return BusinessErrorResource::createFromException($exception, $status);
    }

    public function support(\Throwable $exception): bool
    {
        return $exception instanceof BusinessException;
    }
}
