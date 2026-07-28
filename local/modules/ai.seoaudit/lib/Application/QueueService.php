<?php

namespace Ai\SeoAudit\Application;

use Ai\SeoAudit\Model\TaskTable;
use Bitrix\Main\Type\DateTime;

final class QueueService
{
    public static function processDueTasks(int $limit = 100): void
    {
        $rows = TaskTable::getList([
            'filter' => [
                '=STATUS' => 'queued',
                '<=NEXT_RUN_AT' => new DateTime(),
            ],
            'order' => ['ID' => 'ASC'],
            'limit' => $limit,
        ])->fetchAll();

        foreach ($rows as $row) {
            self::processTask((int) $row['ID']);
        }
    }

    public static function processTask(int $taskId): void
    {
        TaskTable::update($taskId, [
            'STATUS' => 'done',
            'UPDATED_AT' => new DateTime(),
        ]);
    }
}
