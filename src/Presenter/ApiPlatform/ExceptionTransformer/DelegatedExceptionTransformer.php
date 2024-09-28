<?php

namespace Ticketing\Common\Presenter\ApiPlatform\ExceptionTransformer;

class DelegatedExceptionTransformer implements ExceptionTransformerInterface
{
    /**
     * @param iterable|ExceptionTransformerInterface[] $transformers
     */
    public function __construct(
        private iterable $transformers,
    ) {
    }

    public function transform(\Throwable $exception, int $status)
    {
        $transformer = $this->findTransformer($exception);
        if (!$transformer) {
            throw new \InvalidArgumentException(sprintf('Exception with type %s not supported', get_class($exception)));
        }

        return $transformer->transform($exception, $status);
    }

    public function support(\Throwable $exception): bool
    {
        if ($this->findTransformer($exception)) {
            return true;
        }

        return false;
    }

    private function findTransformer(\Throwable $exception): ?ExceptionTransformerInterface
    {
        foreach ($this->transformers as $transformer) {
            if ($transformer->support($exception)) {
                return $transformer;
            }
        }

        return null;
    }
}
