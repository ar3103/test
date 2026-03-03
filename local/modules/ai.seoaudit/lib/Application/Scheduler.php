<?php

namespace Ai\SeoAudit\Application;

use Ai\SeoAudit\Infrastructure\Api\ApiProviderRegistry;
use Bitrix\Main\Diag\Debug;

final class Scheduler
{
    public static function tick(): void
    {
        QueueService::processDueTasks(20);

        $configuredSearchProviders = array_filter(
            ApiProviderRegistry::searchProviders(),
            static fn ($provider) => $provider->isConfigured()
        );

        Debug::writeToFile([
            'event' => 'ai.seoaudit.scheduler.tick',
            'ts' => date('c'),
            'search_providers' => array_map(static fn ($provider) => $provider->getCode(), $configuredSearchProviders),
        ], '', '/upload/ai_seoaudit_scheduler.log');
    }
}
