<?php

declare(strict_types=1);

namespace Company\ReviewSync\Agent;

use Bitrix\Main\Loader;
use CAgent;
use Company\ReviewSync\Infrastructure\IblockReviewRepository;
use Company\ReviewSync\Infrastructure\NotificationService;
use Company\ReviewSync\Infrastructure\QueueRepository;
use Company\ReviewSync\Service\ReviewImportService;

final class ReviewImportAgent
{
    public static function run(): string
    {
        if (!Loader::includeModule('iblock')) {
            return '\\' . static::class . '::run();';
        }

        (new ReviewImportService(
            new QueueRepository(),
            new IblockReviewRepository(),
            new NotificationService()
        ))->execute();

        return '\\' . static::class . '::run();';
    }

    public static function ensureAgentExists(): void
    {
        $agentName = '\\' . static::class . '::run();';
        if (CAgent::GetList([], ['NAME' => $agentName])->Fetch()) {
            return;
        }

        CAgent::AddAgent(
            $agentName,
            '',
            'N',
            300,
            '',
            'Y'
        );
    }
}
