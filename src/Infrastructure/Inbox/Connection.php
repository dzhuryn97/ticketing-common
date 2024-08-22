<?php

namespace Ticketing\Common\Infrastructure\Inbox;

use Doctrine\DBAL\Connection as DBALConnection;
use Doctrine\DBAL\Query\ForUpdate\ConflictResolutionMode;
use Doctrine\DBAL\Query\QueryBuilder;
use Doctrine\DBAL\Schema\Schema;
use Doctrine\DBAL\Types\Types;
use Ramsey\Uuid\Doctrine\UuidType;
use Ramsey\Uuid\UuidInterface;

class Connection
{
    private const TABLE_NAME = 'inbox_messages';

    private const REDELIVER_TIMEOUT = 3600;


    public function __construct(
        private readonly DBALConnection $connection
    )
    {
    }

    private function createAvailableQueryBuilder(): QueryBuilder
    {
        $now = new \DateTimeImmutable('UTC');
        $redeliverLimit = $now->modify(sprintf('-%d seconds', self::REDELIVER_TIMEOUT));


        $qb = $this->connection->createQueryBuilder()
            ->from(self::TABLE_NAME)
            ->andWhere('delivered_on is null OR delivered_on < ?')
            ->andWhere('rejected_on is null')
            ->andWhere('processed_on is null')
            ->setParameters([
                $redeliverLimit
            ], [
                Types::DATETIME_IMMUTABLE
            ]);

        return $qb;
    }

    public function ask(UuidInterface $inboxMessageId)
    {
        $now = new \DateTimeImmutable('UTC');

        $this->connection->createQueryBuilder()
            ->update(self::TABLE_NAME)
            ->set('processed_on',':processedOn')
            ->set('delivered_on','NULL')
            ->where('inbox_message_id = :inboxMessageId')
            ->setParameters([
               'inboxMessageId'=> $inboxMessageId,
                'processedOn'=>$now,
            ],[
                'processedOn'=>Types::DATETIME_IMMUTABLE,
                'inboxMessageId'=> UuidType::NAME,
            ])
            ->executeQuery()
        ;
    }

    public function reject(UuidInterface $inboxMessageId)
    {
        $now = new \DateTimeImmutable('UTC');

        $this->connection->createQueryBuilder()
            ->update(self::TABLE_NAME)
            ->where('inbox_message_id = :inboxMessageId')
            ->set('rejected_on',':rejectedOn')
            ->set('delivered_on','NULL')
            ->setParameters([
                'inboxMessageId' => $inboxMessageId,
                'rejectedOn' => $now,
            ],[
                'inboxMessageId'=> UuidType::NAME,
                'rejectedOn' => Types::DATETIME_IMMUTABLE,
            ])
            ->executeQuery()
        ;
    }

    public function retry(): void
    {
        $this->connection->createQueryBuilder()
            ->update(self::TABLE_NAME)
            ->set('rejected_on','NULL')
            ->executeQuery();
    }

    public function getAvailableMessage()
    {
        $this->connection->beginTransaction();
        $availableQB = $this->createAvailableQueryBuilder();

        $message = $availableQB
            ->select('*')
            ->orderBy('occurred_on','asc')
            ->forUpdate()
            ->setMaxResults(1)
                ->executeQuery()
            ->fetchAssociative()
        ;

        if(!$message){
            $this->connection->commit();
            return null;
        }


        $now = new \DateTimeImmutable('UTC');
        $this->connection->createQueryBuilder()
            ->update(self::TABLE_NAME)
            ->set('delivered_on','?')
            ->where('inbox_message_id = ?')
            ->setParameters([
                $now,
                $message['inbox_message_id']
            ],[
                Types::DATETIME_IMMUTABLE,
                UuidType::NAME
            ])
        ;

        $this->connection->commit();

        return $message;
    }

    public function getAvailableMessageCount():int
    {
        $availableQB = $this->createAvailableQueryBuilder();
        $result=  $availableQB->select('count(*')
            ->fetchOne();
        dd($result);
    }

    public function exists(UuidInterface $inboxMessageId): bool
    {
        $message = $this->connection->createQueryBuilder()
            ->select('inbox_message_id')
            ->from(self::TABLE_NAME)
            ->where('inbox_message_id = :inboxMessageId')
            ->setParameter('inboxMessageId', $inboxMessageId)
            ->fetchAssociative();

        return !empty($message);

    }

    public function send(UuidInterface $inboxMessageId, string $content, \DateTimeImmutable $occurredOn)
    {
        $result = $this->connection->createQueryBuilder()
            ->insert(self::TABLE_NAME)
            ->values([
                'inbox_message_id' => ':inboxMessageId',
                'content' => ':content',
                'occurred_on' => ':occurredOn'
            ])
            ->setParameter('inboxMessageId', $inboxMessageId)
            ->setParameter('content', $content)
            ->setParameter('occurredOn', $occurredOn, 'datetime_immutable')
            ->executeQuery();

    }

    public function updateSchema(Schema $schema): void
    {
        $table = $schema->createTable(self::TABLE_NAME);

        $table->addColumn('inbox_message_id', UuidType::NAME)
            ->setNotnull(true);
        $table->addColumn('content', Types::TEXT)
            ->setNotnull(true);
        $table->addColumn('occurred_on', Types::DATETIME_IMMUTABLE)
            ->setNotnull(true);
        $table->addColumn('delivered_on', Types::DATETIME_IMMUTABLE)
            ->setNotnull(false);
        $table->addColumn('rejected_on', Types::DATETIME_IMMUTABLE)
            ->setNotnull(false);
        $table->addColumn('processed_on', Types::DATETIME_IMMUTABLE)
            ->setNotnull(false);

        $table->setPrimaryKey(['inbox_message_id']);

    }

}