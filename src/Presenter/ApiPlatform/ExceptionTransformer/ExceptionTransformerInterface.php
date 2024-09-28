<?php

namespace Ticketing\Common\Presenter\ApiPlatform\ExceptionTransformer;

interface ExceptionTransformerInterface
{
    public function transform(\Throwable $exception, int $status);

    public function support(\Throwable $exception): bool;
}
