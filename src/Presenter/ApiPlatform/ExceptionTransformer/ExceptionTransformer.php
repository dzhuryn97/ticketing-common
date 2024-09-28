<?php

namespace Ticketing\Common\Presenter\ApiPlatform\ExceptionTransformer;

use ApiPlatform\State\ApiResource\Error;

class ExceptionTransformer implements ExceptionTransformerInterface
{
    public function __construct(
        private bool $debug,
    ) {
    }

    public function transform(\Throwable $exception, int $status)
    {
        $error = Error::createFromException($exception, $status);
        if (!$this->debug && $status >= 500) {
            $error->setDetail('Internal Server Error');
        }

        return $error;
    }

    public function support(\Throwable $exception): bool
    {
        return true;
    }
}
