<?php

namespace Ticketing\Common\Infrastructure\Command;

use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Messenger\Envelope;
use Symfony\Component\Messenger\Exception\HandlerFailedException;
use Symfony\Component\Messenger\Middleware\MiddlewareInterface;
use Symfony\Component\Messenger\Middleware\StackInterface;

class OriginalDomainExceptionMiddleware implements MiddlewareInterface
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
            $domainsException = $e->getWrappedExceptions(\DomainException::class);
            if ($this->support() && 1 == count($domainsException)) {
                $originalException = array_values($domainsException)[0];
                throw  $originalException;
            }
            throw $e;
        }

        return $envelope;
    }

    private function support(): bool
    {
        return !is_null($this->requestStack->getMainRequest());
    }
}
