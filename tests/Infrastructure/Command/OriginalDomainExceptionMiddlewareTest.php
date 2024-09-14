<?php

namespace Ticketing\Common\Tests\Infrastructure\Command;

use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Messenger\Envelope;
use Symfony\Component\Messenger\Exception\HandlerFailedException;
use Symfony\Component\Messenger\Middleware\MiddlewareInterface;
use Symfony\Component\Messenger\Middleware\StackInterface;
use Ticketing\Common\Infrastructure\Command\DomainExceptionExtractingMiddleware;

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
    private  $nextMiddleware;
    private HandlerFailedException $handlerFailedExceptionWithoutDomainException;
    private HandlerFailedException $handlerFailedExceptionWithDomainException;



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

        $this->handlerFailedExceptionWithDomainException =  new HandlerFailedException(
            $this->createStub(Envelope::class),
            [new \DomainException()]
        );

        $this->handlerFailedExceptionWithoutDomainException =  new HandlerFailedException(
            $this->createStub(Envelope::class),
            [new \DomainException()]
        );
    }

    /**
     * @test
     */
    public function Handle_NextReturnEnvelope_ReturnThoseEnvelope()
    {
        ()
        //Arrange
        $requestStackWithMainRequestMock = $this->getRequestStackWithMainRequest();
        $originalDomainExceptionMiddleware = new DomainExceptionExtractingMiddleware($requestStackWithMainRequestMock);

        $this->nextMiddleware
            ->expects($this->once())
            ->method('handle')
            ->with($this->originalEnvelope, $this->stackMock)
            ->willReturn($this->nextMiddlewareEnvelop)
        ;

        //Act
        $result = $originalDomainExceptionMiddleware->handle($this->originalEnvelope, $this->stackMock);

        //Arrange
        $this->assertEquals($this->nextMiddlewareEnvelop, $result);

    }

    /**
     * @test
     * @dataProvider provideDataForHandlerFailedExceptionThrownTest
     */
    public function Handle_HandlerFailedExceptionThrown_HandlerAndThrowCorrectException(
        $requestStack,
        $stack,
        $expectedException
    )
    {
        //Arrange
        $originalDomainExceptionMiddleware = new DomainExceptionExtractingMiddleware($requestStack);

        $this->expectException($expectedException);
        //Act
        $originalDomainExceptionMiddleware->handle($this->originalEnvelope, $stack);
    }

    public function provideDataForHandlerFailedExceptionThrownTest(): \Generator
    {
        yield 'ContainDomainExceptionAndRequestExists' => [
            $this->getRequestStackWithMainRequest(),
            $this->getStackHandlerFailedExceptionContainerDomainException(),
            \DomainException::class
        ];

        yield 'ContainOtherExceptionAndRequestExists' => [
            $this->getRequestStackWithMainRequest(),
            $this->getStackHandlerFailedExceptionContainOtherException(),
            HandlerFailedException::class
        ];

        yield 'ContainDomainExceptionAndRequestAbsent' => [
            $this->getEmptyRequestStackMock(),
            $this->getStackHandlerFailedExceptionContainerDomainException(),
            HandlerFailedException::class
        ];

        yield 'ContainOtherExceptionAndRequestAbsent' => [
            $this->getEmptyRequestStackMock(),
            $this->getStackHandlerFailedExceptionContainOtherException(),
            HandlerFailedException::class
        ];

    }


    private function getHandlerFailedException(array $exceptions): HandlerFailedException
    {
        return  new HandlerFailedException(
            $this->createStub(Envelope::class),
            $exceptions
        );
    }

    private function getHandlerFailedExceptionWithDomainException(): HandlerFailedException
    {
        return $this->getHandlerFailedException([new \DomainException()]);
    }

    private function getHandlerFailedExceptionWithOtherException(): HandlerFailedException
    {
        return  $this->getHandlerFailedException([new \InvalidArgumentException()]);
    }

    private function getMiddlewareWhichThrownHandlerFailedException($handlerFailedException)
    {
        $middleware = $this->createMock(MiddlewareInterface::class);

        $middleware
            ->expects($this->once())
            ->method('handle')
            ->willThrowException(
                $handlerFailedException
            )
        ;

        return $middleware;
    }

    private function getStackHandlerFailedExceptionContainerDomainException()
    {
        $stackMock = $this->createMock(StackInterface::class);
        $stackMock
            ->method('next')
            ->willReturn(
                $this->getMiddlewareWhichThrownHandlerFailedException(
                    $this->getHandlerFailedExceptionWithDomainException()
                )
            )
        ;
        return $stackMock;
    }

    private function getStackHandlerFailedExceptionContainOtherException()
    {
        $stackMock = $this->createMock(StackInterface::class);
        $stackMock
            ->method('next')
            ->willReturn(
                $this->getMiddlewareWhichThrownHandlerFailedException(
                    $this->getHandlerFailedExceptionWithOtherException()
                )
            )
        ;
        return $stackMock;
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
