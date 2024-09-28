<?php

namespace Ticketing\Common\Tests\Infrastructure\Command;

use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Messenger\Envelope;
use Symfony\Component\Messenger\Exception\HandlerFailedException;
use Symfony\Component\Messenger\Middleware\MiddlewareInterface;
use Symfony\Component\Messenger\Middleware\StackInterface;
use Ticketing\Common\Infrastructure\Command\BusinessExceptionExtractingMiddleware;

/**
 * @covers \OriginalDomainExceptionMiddleware
 */
class OriginalDomainExceptionMiddlewareTest extends TestCase
{
    private Envelope $nextMiddlewareEnvelop;
    /**
     * @var (object&\PHPUnit\Framework\MockObject\MockObject)|\PHPUnit\Framework\MockObject\MockObject|StackInterface|(StackInterface&object&\PHPUnit\Framework\MockObject\MockObject)|(StackInterface&\PHPUnit\Framework\MockObject\MockObject)
     */
    private StackInterface $stackMock;
    /**
     * @var (object&\PHPUnit\Framework\MockObject\Stub)|\PHPUnit\Framework\MockObject\Stub|Envelope|(Envelope&object&\PHPUnit\Framework\MockObject\Stub)|(Envelope&\PHPUnit\Framework\MockObject\Stub)
     */
    private Envelope $originalEnvelope;
    /**
     * @var (object&\PHPUnit\Framework\MockObject\MockObject)|\PHPUnit\Framework\MockObject\MockObject|MiddlewareInterface|(MiddlewareInterface&object&\PHPUnit\Framework\MockObject\MockObject)|(MiddlewareInterface&\PHPUnit\Framework\MockObject\MockObject)
     */
    private $nextMiddleware;

    protected function setUp(): void
    {
        $this->nextMiddlewareEnvelop = new Envelope(new \stdClass(), []);
        $this->nextMiddleware = $this->createMock(MiddlewareInterface::class);

        $this->stackMock = $this->createMock(StackInterface::class);
        $this->stackMock
            ->method('next')
            ->willReturn($this->nextMiddleware)
        ;
        $this->originalEnvelope = $this->createStub(Envelope::class);
    }

    /**
     * @test
     */
    public function handleNextReturnEnvelopeReturnThoseEnvelope()
    {
        // Arrange
        $requestStackWithMainRequestMock = $this->getRequestStackWithMainRequest();
        $originalDomainExceptionMiddleware = new BusinessExceptionExtractingMiddleware($requestStackWithMainRequestMock);

        $this->nextMiddleware
            ->expects($this->once())
            ->method('handle')
            ->with($this->originalEnvelope, $this->stackMock)
            ->willReturn($this->nextMiddlewareEnvelop)
        ;

        // Act
        $result = $originalDomainExceptionMiddleware->handle($this->originalEnvelope, $this->stackMock);

        // Arrange
        $this->assertEquals($this->nextMiddlewareEnvelop, $result);
    }

    /**
     * @test
     *
     * @dataProvider provideDataForHandlerFailedExceptionThrownTest
     */
    public function handleExceptionThrownRethrownCorrectException(
        $requestStack,
        $handlerFailedException,
        $expectedException,
    ) {
        // Arrange
        $originalDomainExceptionMiddleware = new BusinessExceptionExtractingMiddleware($requestStack);
        $this
            ->nextMiddleware
            ->method('handle')
            ->willThrowException($handlerFailedException)
        ;

        $this->expectException($expectedException);
        // Act
        $originalDomainExceptionMiddleware->handle($this->originalEnvelope, $this->stackMock);
        $this->assertEquals(1, 1);
    }

    public function provideDataForHandlerFailedExceptionThrownTest(): \Generator
    {
        yield 'ContainDomainExceptionAndRequestExists' => [
            $this->getRequestStackWithMainRequest(),
            $this->getHandlerFailedExceptionWithDomainException(),
            \DomainException::class,
        ];

        yield 'ContainOtherExceptionAndRequestExists' => [
            $this->getRequestStackWithMainRequest(),
            $this->getHandlerFailedExceptionWithOtherException(),
            HandlerFailedException::class,
        ];

        yield 'ContainDomainExceptionAndRequestAbsent' => [
            $this->getEmptyRequestStackMock(),
            $this->getHandlerFailedExceptionWithDomainException(),
            HandlerFailedException::class,
        ];

        yield 'ContainOtherExceptionAndRequestAbsent' => [
            $this->getEmptyRequestStackMock(),
            $this->getHandlerFailedExceptionWithOtherException(),
            HandlerFailedException::class,
        ];

        yield 'HandleThrownNotHandlerFailedExceptionRequestPresent' => [
            $this->getEmptyRequestStackMock(),
            new \Exception(),
            \Exception::class,
        ];

        yield 'HandleThrownNotHandlerFailedExceptionRequestAbsent' => [
            $this->getRequestStackWithMainRequest(),
            new \Exception(),
            \Exception::class,
        ];
    }

    private function getHandlerFailedExceptionWithDomainException(): HandlerFailedException
    {
        return  new HandlerFailedException(
            $this->createStub(Envelope::class),
            [new \DomainException()]
        );
    }

    private function getHandlerFailedExceptionWithOtherException(): HandlerFailedException
    {
        return  new HandlerFailedException(
            $this->createStub(Envelope::class),
            [new \InvalidArgumentException()]
        );
    }

    private function getEmptyRequestStackMock()
    {
        return $this->createStub(RequestStack::class);

    }

    private function getRequestStackWithMainRequest()
    {
        $requestStackMock = $this->getEmptyRequestStackMock();

        $requestMock = $this->createMock(Request::class);
        $requestStackMock
            ->method('getMainRequest')
            ->willReturn(
                $requestMock
            );

        return $requestStackMock;
    }
}
