<?php

declare(strict_types=1);

use Bitrix\Main\Application;
use Bitrix\Main\DB\SqlQueryException;

/**
 * Примитивный репозиторий пунктов выдачи европочты.
 * Работает без отдельного модуля, только через свою таблицу.
 */
final class EuropostOfficeRepository
{
    private const TABLE = 'b_europost_office';

    public static function installSchema(): void
    {
        $connection = Application::getConnection();

        if ($connection->isTableExists(self::TABLE)) {
            return;
        }

        $connection->queryExecute(
            'CREATE TABLE ' . self::TABLE . " (
                ID INT NOT NULL AUTO_INCREMENT,
                EXTERNAL_ID VARCHAR(64) NOT NULL,
                TITLE VARCHAR(255) NOT NULL,
                ADDRESS VARCHAR(512) NOT NULL,
                CITY VARCHAR(255) NOT NULL,
                POSTAL_CODE VARCHAR(32) DEFAULT NULL,
                PHONE VARCHAR(64) DEFAULT NULL,
                WORK_TIME VARCHAR(512) DEFAULT NULL,
                GPS_LAT DECIMAL(10, 7) DEFAULT NULL,
                GPS_LON DECIMAL(10, 7) DEFAULT NULL,
                ACTIVE CHAR(1) NOT NULL DEFAULT 'Y',
                UPDATED_AT DATETIME NOT NULL,
                PRIMARY KEY (ID),
                UNIQUE KEY UX_EUROPOST_EXTERNAL_ID (EXTERNAL_ID),
                KEY IX_EUROPOST_ACTIVE_CITY (ACTIVE, CITY)
            )"
        );
    }

    /**
     * @param array<int, array<string, mixed>> $offices
     */
    public static function upsertBatch(array $offices): void
    {
        if ($offices === []) {
            return;
        }

        $connection = Application::getConnection();
        $helper = $connection->getSqlHelper();
        $now = $helper->convertToDbDateTime(date('Y-m-d H:i:s'));

        foreach ($offices as $office) {
            $externalId = (string)($office['external_id'] ?? '');
            if ($externalId === '') {
                continue;
            }

            $fields = [
                'EXTERNAL_ID' => $helper->forSql($externalId, 64),
                'TITLE' => $helper->forSql((string)($office['title'] ?? ''), 255),
                'ADDRESS' => $helper->forSql((string)($office['address'] ?? ''), 512),
                'CITY' => $helper->forSql((string)($office['city'] ?? ''), 255),
                'POSTAL_CODE' => $helper->forSql((string)($office['postal_code'] ?? ''), 32),
                'PHONE' => $helper->forSql((string)($office['phone'] ?? ''), 64),
                'WORK_TIME' => $helper->forSql((string)($office['work_time'] ?? ''), 512),
                'GPS_LAT' => isset($office['gps_lat']) ? (float)$office['gps_lat'] : null,
                'GPS_LON' => isset($office['gps_lon']) ? (float)$office['gps_lon'] : null,
                'ACTIVE' => 'Y',
            ];

            $update = sprintf(
                "INSERT INTO %s
                (EXTERNAL_ID, TITLE, ADDRESS, CITY, POSTAL_CODE, PHONE, WORK_TIME, GPS_LAT, GPS_LON, ACTIVE, UPDATED_AT)
                VALUES ('%s', '%s', '%s', '%s', '%s', '%s', '%s', %s, %s, 'Y', %s)
                ON DUPLICATE KEY UPDATE
                    TITLE = VALUES(TITLE),
                    ADDRESS = VALUES(ADDRESS),
                    CITY = VALUES(CITY),
                    POSTAL_CODE = VALUES(POSTAL_CODE),
                    PHONE = VALUES(PHONE),
                    WORK_TIME = VALUES(WORK_TIME),
                    GPS_LAT = VALUES(GPS_LAT),
                    GPS_LON = VALUES(GPS_LON),
                    ACTIVE = 'Y',
                    UPDATED_AT = %s",
                self::TABLE,
                $fields['EXTERNAL_ID'],
                $fields['TITLE'],
                $fields['ADDRESS'],
                $fields['CITY'],
                $fields['POSTAL_CODE'],
                $fields['PHONE'],
                $fields['WORK_TIME'],
                $fields['GPS_LAT'] === null ? 'NULL' : (string)$fields['GPS_LAT'],
                $fields['GPS_LON'] === null ? 'NULL' : (string)$fields['GPS_LON'],
                $now,
                $now
            );

            $connection->queryExecute($update);
        }
    }

    /**
     * @param string[] $actualExternalIds
     */
    public static function markInactiveMissing(array $actualExternalIds): void
    {
        $connection = Application::getConnection();
        $helper = $connection->getSqlHelper();

        if ($actualExternalIds === []) {
            $connection->queryExecute("UPDATE " . self::TABLE . " SET ACTIVE = 'N'");
            return;
        }

        $quoted = array_map(
            static fn(string $id): string => "'" . $helper->forSql($id, 64) . "'",
            $actualExternalIds
        );

        $connection->queryExecute(
            "UPDATE " . self::TABLE . " SET ACTIVE = 'N' WHERE EXTERNAL_ID NOT IN (" . implode(', ', $quoted) . ")"
        );
    }

    /**
     * @return array<int, array{ID:int, EXTERNAL_ID:string, TITLE:string, ADDRESS:string, CITY:string}>
     */
    public static function getActiveOffices(): array
    {
        $connection = Application::getConnection();
        $result = $connection->query(
            "SELECT ID, EXTERNAL_ID, TITLE, ADDRESS, CITY
             FROM " . self::TABLE . "
             WHERE ACTIVE = 'Y'
             ORDER BY CITY ASC, TITLE ASC"
        );

        $rows = [];
        while ($row = $result->fetch()) {
            $rows[] = [
                'ID' => (int)$row['ID'],
                'EXTERNAL_ID' => (string)$row['EXTERNAL_ID'],
                'TITLE' => (string)$row['TITLE'],
                'ADDRESS' => (string)$row['ADDRESS'],
                'CITY' => (string)$row['CITY'],
            ];
        }

        return $rows;
    }
}
