<?php

namespace Ticketing\Common\Presenter\ApiPlatform;

use ApiPlatform\Metadata\HttpOperation;
use ApiPlatform\Metadata\Operation;
use ApiPlatform\Metadata\ResourceClassResolverInterface;
use ApiPlatform\State\ProviderInterface;
use Ticketing\Common\Presenter\ApiPlatform\ExceptionTransformer\ExceptionTransformerInterface;

class ErrorProvider implements ProviderInterface
{
    public function __construct(
        private readonly ?ExceptionTransformerInterface $exceptionTransformer = null,
        private ?ResourceClassResolverInterface $resourceClassResolver = null,
    ) {
    }

    public function provide(Operation $operation, array $uriVariables = [], array $context = []): object|array|null
    {
        if (!($request = $context['request'] ?? null) || !$operation instanceof HttpOperation || null === ($exception = $request->attributes->get('exception'))) {
            throw new \RuntimeException('Not an HTTP request');
        }

        if ($this->resourceClassResolver?->isResourceClass($exception::class)) {
            return $exception;
        }

        $status = $operation->getStatus() ?? 500;

        return $this->exceptionTransformer->transform($exception, $status);
    }
}
