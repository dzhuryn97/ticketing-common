<?php

namespace Ticketing\Common\Infrastructure\Inbox\Console;

use Psr\Cache\CacheItemPoolInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\ConsoleOutputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Ticketing\Common\Infrastructure\Inbox\InboxConfig;

class InboxMessageStopConsumersCommand extends Command
{
    public function __construct(
        private readonly CacheItemPoolInterface $cacheApp,
    ) {
        parent::__construct();
    }

    protected function configure()
    {
        $this->setName('common:inbox:stop-consumers');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output instanceof ConsoleOutputInterface ? $output->getErrorOutput() : $output);

        $cacheItem = $this->cacheApp->getItem(InboxConfig::RESTART_REQUESTED_TIMESTAMP_KEY);
        $cacheItem->set(microtime(true));
        $this->cacheApp->save($cacheItem);

        $io->success('Signal successfully sent to stop any running inbox message consumers.');

        return Command::SUCCESS;
    }
}
