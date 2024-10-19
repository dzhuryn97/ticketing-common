<?php

namespace Ticketing\Common\Infrastructure\Inbox\Console;

use Psr\Cache\CacheItemPoolInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Ticketing\Common\Application\EventBus\AbstractIntegrationEvent;
use Ticketing\Common\Infrastructure\Inbox\InboxConfig;
use Ticketing\Common\Infrastructure\Inbox\InboxMessage;
use Ticketing\Common\Infrastructure\Inbox\InboxMessageStorage;

class InboxMessageConsumeCommand extends Command
{
    private const MAX_RETRY = 3;
    private const RETRY_DELAY = 1;
    private const RETRY_MULTIPLIER = 2;

    private float $startedAt;

    public function __construct(
        private readonly InboxMessageStorage $inboxMessageStorage,
        private readonly LoggerInterface $logger,
        private readonly CacheItemPoolInterface $cache,
        private readonly array $eventToHandlersMap,
    ) {
        $this->startedAt = microtime(true);
        parent::__construct();
    }

    protected function configure()
    {
        $this->setName('common:inbox:message-consume');
        parent::configure();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $io->success('Ready for handling inboxMessages');

        while ($this->shouldRun()) {

            $message = $this->inboxMessageStorage->get();
            if (!$message) {
                sleep(1);
                continue;
            }

            $this->tryHandle($message, 1, self::RETRY_DELAY);
        }

        return Command::SUCCESS;
    }

    private function tryHandle(InboxMessage $message, $retry, $delay)
    {
        try {
            $integrationEvent = $message->integrationsEvent;
            $eventClass = get_class($integrationEvent);
            $this->logger->info(
                'Handling inboxMessage with integrationEvent {event}',
                ['event' => get_class($integrationEvent)]
            );

            $handlers = $this->eventToHandlersMap[$eventClass] ?? [];
            if (!$handlers) {
                $this->inboxMessageStorage->ack($message);
                $this->logger->info('No handlers for event {event}', ['event' => $eventClass]);

                return;
            }

            $this->handle($handlers, $integrationEvent);
            $this->inboxMessageStorage->ack($message);
            $this->logger->info('{event} was handled successfully', ['event' => $eventClass]);
        } catch (\Throwable $e) {
            if ($retry > self::MAX_RETRY) {
                $this->inboxMessageStorage->reject($message);
                $this->logger->critical(
                    'Error thrown while handling event {event}. Removing from transport after {retry} retries. Error: {error}',
                    [
                        'event' => $eventClass,
                        'retry' => $retry,
                        'error' => $e->getMessage(),
                        'exception' => $e,
                    ]
                );

                return;
            }

            $this->logger->warning('Error thrown while handling event {event}. Sending for retry #{retry} using {delay} s delay. Error: {error}', [
                'event' => $eventClass,
                'retry' => $retry,
                'delay' => $delay,
                'error' => $e->getMessage(),
                'exception' => $e,
            ]);
            sleep($delay);

            $nextDelay = $delay * self::RETRY_MULTIPLIER;
            ++$retry;

            $this->tryHandle($message, $retry, $nextDelay);
        }
    }

    private function handle(array $handlers, AbstractIntegrationEvent $integrationEvent)
    {
        foreach ($handlers as $handler) {
            $handler($integrationEvent);

            $this->logger->info('Event {event} handled by {handler}::__invoke', [
                'event' => get_class($integrationEvent),
                'handler' => get_class($handler),
            ]);
        }
    }

    private function shouldRun()
    {
        $cacheItem = $this->cache->getItem(InboxConfig::RESTART_REQUESTED_TIMESTAMP_KEY);

        if (!$cacheItem->isHit()) {
            return true;
        }

        if ($this->startedAt < $cacheItem->get()) {
            $this->logger->info('Received signal to stop');

            return false;
        }

        return true;
    }
}
