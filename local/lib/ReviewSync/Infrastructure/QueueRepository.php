<?php

declare(strict_types=1);

namespace Company\ReviewSync\Infrastructure;

use Bitrix\Main\Application;
use Bitrix\Main\DB\SqlQueryException;

final class QueueRepository
{
    private const TABLE = 'company_review_import_queue';

    public function ensureTable(): void
    {
        $connection = Application::getConnection();

        try {
            $connection->queryExecute(
                'CREATE TABLE IF NOT EXISTS ' . self::TABLE . " (
                    ID INT AUTO_INCREMENT PRIMARY KEY,
                    PROVIDER VARCHAR(32) NOT NULL,
                    EXTERNAL_ID VARCHAR(128) NOT NULL,
                    REVIEW_HASH CHAR(40) NOT NULL,
                    PAYLOAD LONGTEXT NOT NULL,
                    STATUS VARCHAR(20) NOT NULL DEFAULT 'new',
                    ELEMENT_ID INT NULL,
                    DATE_CREATE DATETIME NOT NULL,
                    DATE_UPDATE DATETIME NOT NULL,
                    UNIQUE KEY uq_provider_external (PROVIDER, EXTERNAL_ID),
                    KEY ix_status (STATUS)
                )"
            );
        } catch (SqlQueryException) {
            // Таблица уже существует или нет прав на DDL в конкретной инсталляции.
        }
    }

    public function enqueue(string $provider, string $externalId, string $hash, string $payload): void
    {
        $connection = Application::getConnection();
        $helper = $connection->getSqlHelper();

        $connection->queryExecute(sprintf(
            "INSERT INTO %s (PROVIDER, EXTERNAL_ID, REVIEW_HASH, PAYLOAD, STATUS, DATE_CREATE, DATE_UPDATE)
             VALUES ('%s', '%s', '%s', '%s', 'new', NOW(), NOW())
             ON DUPLICATE KEY UPDATE REVIEW_HASH=VALUES(REVIEW_HASH), PAYLOAD=VALUES(PAYLOAD), DATE_UPDATE=NOW()",
            self::TABLE,
            $helper->forSql($provider),
            $helper->forSql($externalId),
            $helper->forSql($hash),
            $helper->forSql($payload)
        ));
    }

    /** @return array<int, array<string, mixed>> */
    public function reserveBatch(int $limit): array
    {
        $connection = Application::getConnection();
        $limit = max(1, $limit);

        $rows = $connection->query(sprintf(
            "SELECT ID, PROVIDER, EXTERNAL_ID, PAYLOAD
             FROM %s
             WHERE STATUS='new'
             ORDER BY ID ASC
             LIMIT %d",
            self::TABLE,
            $limit
        ))->fetchAll();

        if (!$rows) {
            return [];
        }

        $ids = implode(',', array_map(static fn(array $row): string => (string)(int)$row['ID'], $rows));
        $connection->queryExecute(sprintf(
            "UPDATE %s SET STATUS='processing', DATE_UPDATE=NOW() WHERE ID IN (%s)",
            self::TABLE,
            $ids
        ));

        return $rows;
    }

    public function markDone(int $queueId, int $elementId): void
    {
        Application::getConnection()->queryExecute(sprintf(
            "UPDATE %s SET STATUS='done', ELEMENT_ID=%d, DATE_UPDATE=NOW() WHERE ID=%d",
            self::TABLE,
            $elementId,
            $queueId
        ));
    }

    public function markFailed(int $queueId): void
    {
        Application::getConnection()->queryExecute(sprintf(
            "UPDATE %s SET STATUS='failed', DATE_UPDATE=NOW() WHERE ID=%d",
            self::TABLE,
            $queueId
        ));
    }
}
