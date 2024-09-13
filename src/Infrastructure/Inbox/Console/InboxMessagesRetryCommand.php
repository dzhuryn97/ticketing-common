<?php

namespace Ticketing\Common\Infrastructure\Inbox\Console;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Ticketing\Common\Infrastructure\Inbox\InboxMessageStorage;

class InboxMessagesRetryCommand extends Command
{
    private InboxMessageStorage $inboxMessageStorage;

    public function __construct(
        InboxMessageStorage $inboxMessageStorage,
    ) {
        parent::__construct();
        $this->inboxMessageStorage = $inboxMessageStorage;
    }

    protected function configure()
    {
        $this->setName('common:inbox:messages-retry');

    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $this->inboxMessageStorage->retry();

        $io->success('Marked messages for retry');

        return Command::SUCCESS;
    }
}
