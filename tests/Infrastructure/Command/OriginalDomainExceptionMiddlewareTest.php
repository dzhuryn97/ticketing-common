<?php

namespace Ticketing\Common\Tests\Infrastructure\Command;

use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Messenger\Envelope;
use Symfony\Component\Messenger\Middleware\MiddlewareInterface;
use Symfony\Component\Messenger\Middleware\StackInterface;
use Ticketing\Common\Infrastructure\Command\OriginalDomainExceptionMiddleware;

/**
 * @covers \OriginalDomainExceptionMiddleware
 */
class OriginalDomainExceptionMiddlewareTest extends TestCase
{
    /**
     * @test
     */
    public function handleNextHandleReturnEnvelopeReturnThoseEnvelope()
    {
        $requestMock = $this->createMock(Request::class);
        $requestStackMock = $this->createStub(RequestStack::class);
        $requestStackMock
            ->method('getMainRequest')
            ->willReturn(
                $requestMock
            );

        $nextMiddlewareEnvelop = new Envelope(new \stdClass(), []);
        $nextMiddleware = $this->createMock(MiddlewareInterface::class);
        $nextMiddleware->method('handle')
            ->willReturn($nextMiddlewareEnvelop);


        $envelop = $this->createStub(Envelope::class);
        $stackMock = $this->createMock(StackInterface::class);
        $stackMock
            ->method('next')
            ->willReturn($nextMiddleware)
        ;

        $originalDomainExceptionMiddleware = new OriginalDomainExceptionMiddleware($requestStackMock);


        $result = $originalDomainExceptionMiddleware->handle($envelop, $stackMock);




        $this->assertEquals($nextMiddlewareEnvelop, $result);

    }
}
