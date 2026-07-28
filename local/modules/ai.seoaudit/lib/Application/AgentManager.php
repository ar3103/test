<?php

namespace Ai\SeoAudit\Application;

final class AgentManager
{
    private const MODULE_ID = 'ai.seoaudit';

    public static function install(): void
    {
        if (class_exists('CAgent')) {
            \CAgent::AddAgent(
                '\\Ai\\SeoAudit\\Application\\AgentManager::runQueueAgent();',
                self::MODULE_ID,
                'N',
                300,
                '',
                'Y'
            );
        }
    }

    public static function uninstall(): void
    {
        if (class_exists('CAgent')) {
            \CAgent::RemoveAgent('\\Ai\\SeoAudit\\Application\\AgentManager::runQueueAgent();', self::MODULE_ID);
        }
    }

    public static function runQueueAgent(): string
    {
        QueueService::processDueTasks(50);

        return '\\Ai\\SeoAudit\\Application\\AgentManager::runQueueAgent();';
    }
}
