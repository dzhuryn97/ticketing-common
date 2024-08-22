<?php

namespace Ticketing\Common\Infrastructure\Inbox;

use Psr\Log\LoggerInterface;
use Throwable;
use Ticketing\Common\Application\EventBus\AbstractIntegrationEvent;

class InboxMessageHandler
{
    public function __construct(
        private readonly LoggerInterface $logger
    )
    {
    }

    private const MAX_RETRY = 3;
    private const RETRY_DELAY = 1000;
    private const RETRY_MULTIPLIER = 2;

    public function __invoke(InboxMessage $inboxMessage)
    {
        $retry = 1;
        $retryDelay = self::RETRY_DELAY;


        $this->tryHandle($inboxMessage, $retry, $retryDelay);
    }

    private function tryHandle(InboxMessage $inboxMessage, int $retry, int $retryDelay)
    {
        $integrationEvent = $inboxMessage->integrationsEvent;

        try {
            $this->handle($integrationEvent);
        } catch (Throwable $e) {

            if($retry > self::MAX_RETRY){
                throw $e;
            }

            $message = sprintf('Error thrown while handling message %s. Sending for retry # %s using %s ms delay. Error: %s',
                get_class($integrationEvent),
                $retry,
                $retryDelay,
                $e->getMessage()
            );

            $this->logger->info($message);

            sleep($retryDelay/1000);

            $retry++;
            $retryDelay = $retryDelay * self::RETRY_MULTIPLIER;

            $this->tryHandle($inboxMessage, $retry, $retryDelay);
        }
    }

    private function handle(AbstractIntegrationEvent $integrationEvent)
    {
    }


}