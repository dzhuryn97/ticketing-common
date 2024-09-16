<?php

namespace Ticketing\Common\Infrastructure\Command;

use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Messenger\Envelope;
use Symfony\Component\Messenger\Exception\HandlerFailedException;
use Symfony\Component\Messenger\Middleware\MiddlewareInterface;
use Symfony\Component\Messenger\Middleware\StackInterface;

/**
 * Middleware that intercepts the `HandlerFailedException` during message processing.
 * If the exception contains a `DomainException` and the current request exists
 * (indicating user interaction with the application via the command bus),
 * this middleware extracts the first `DomainException` and rethrows it.
 *
 * This approach ensures that `DomainException` is properly handled by the
 * error handling mechanisms, allowing for user-friendly error messages.
 *
 * If no `DomainException` is found or the request is absent, the original
 * `HandlerFailedException` is rethrown.
 */
class DomainExceptionExtractingMiddleware implements MiddlewareInterface
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
            $domainExceptions = $e->getWrappedExceptions(\DomainException::class);
            if ($this->supports() && count($domainExceptions) > 0) {
                $firstDomainException = array_values($domainExceptions)[0];
                throw  $firstDomainException;
            }
            throw $e;
        }

        return $envelope;
    }

    private function supports(): bool
    {
        return !is_null($this->requestStack->getMainRequest());
    }
}
