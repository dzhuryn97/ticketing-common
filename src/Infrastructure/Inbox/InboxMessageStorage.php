<?php

namespace Ticketing\Common\Infrastructure\Inbox;

class InboxMessageStorage
{
    public function __construct(
        private readonly Connection $connection,
    ) {
    }

    public function ack(InboxMessage $inboxMessage): void
    {
        $this->connection->ask($inboxMessage->inboxMessageId);
    }

    public function reject(InboxMessage $inboxMessage): void
    {
        $this->connection->reject($inboxMessage->inboxMessageId);
    }

    public function send(InboxMessage $inboxMessage): void
    {

        if ($this->connection->exists($inboxMessage->inboxMessageId)) {
            return;
        }

        $encodedMessage = serialize($inboxMessage);

        $this->connection->send($inboxMessage->inboxMessageId, $encodedMessage, $inboxMessage->occurredOn);
    }

    public function get(): ?InboxMessage
    {
        $message = $this->connection->getAvailableMessage();
        if (!$message) {
            return null;
        }

        return unserialize($message['content']);
    }

    public function retry(): void
    {
        $this->connection->retry();
    }
}
