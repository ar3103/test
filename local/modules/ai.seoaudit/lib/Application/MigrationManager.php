<?php

namespace Ai\SeoAudit\Application;

use Bitrix\Main\Application;

final class MigrationManager
{
    public static function installSchema(string $modulePath): void
    {
        self::runSqlFile($modulePath . '/install/db/mysql/install.sql');
    }

    public static function uninstallSchema(string $modulePath): void
    {
        self::runSqlFile($modulePath . '/install/db/mysql/uninstall.sql');
    }

    private static function runSqlFile(string $path): void
    {
        if (!file_exists($path)) {
            return;
        }

        $sql = file_get_contents($path);
        if ($sql === false) {
            return;
        }

        $connection = Application::getConnection();
        $queries = array_filter(array_map('trim', explode(';', $sql)));

        foreach ($queries as $query) {
            $connection->queryExecute($query);
        }
    }
}
