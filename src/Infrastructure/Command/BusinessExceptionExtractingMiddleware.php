<?php

namespace Ticketing\Common\Infrastructure\Command;

use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Messenger\Envelope;
use Symfony\Component\Messenger\Exception\HandlerFailedException;
use Symfony\Component\Messenger\Middleware\MiddlewareInterface;
use Symfony\Component\Messenger\Middleware\StackInterface;
use Ticketing\Common\Domain\Exception\BusinessException;

/**
 * Middleware that intercepts the `HandlerFailedException` during message processing.
 * If the exception contains a `BusinessException` and the current request exists
 * (indicating user interaction with the application via the command bus),
 * this middleware extracts the first `BusinessException` and rethrows it.
 *
 * This approach ensures that `BusinessException` is properly handled by the
 * error handling mechanisms, allowing for user-friendly error messages.
 *
 * If no `BusinessException` is found or the request is absent, the original
 * `HandlerFailedException` is rethrown.
 */
class BusinessExceptionExtractingMiddleware implements MiddlewareInterface
{
    public function __construct(
        private readonly RequestStack $requestStack,
    ) {
    }

    public function handle(Envelope $envelope, StackInterface $stack): Envelope
    {
        try {
            $envelope = $stack->next()->handle($envelope, $stack);
        } catch (HandlerFailedException $e) {
            $businessExceptions = $this->filterInstanceOfBusinessException($e);
            if ($this->supports() && count($businessExceptions) > 0) {
                $firstBusinessException = array_values($businessExceptions)[0];
                throw  $firstBusinessException;
            }
            throw $e;
        }

        return $envelope;
    }

    private function supports(): bool
    {
        return !is_null($this->requestStack->getMainRequest());
    }

    private function filterInstanceOfBusinessException(HandlerFailedException|\Exception $e): array
    {
        $businessExceptions = [];
        foreach ($e->getWrappedExceptions() as $wrappedException) {
            if($wrappedException instanceof BusinessException){
                $businessExceptions[] = $wrappedException;
            }
        }

        return $businessExceptions;
    }
}
