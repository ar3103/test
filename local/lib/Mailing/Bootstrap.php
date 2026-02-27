<?php

declare(strict_types=1);

namespace Local\Mailing;

use Bitrix\Main\EventManager;

final class Bootstrap
{
    public static function registerHandlers(): void
    {
        $eventManager = EventManager::getInstance();

        $eventManager->addEventHandler('iblock', 'OnAfterIBlockElementAdd', [MailingAutomationService::class, 'onFormSaved']);
        $eventManager->addEventHandler('main', 'OnBeforeProlog', [MailingAutomationService::class, 'onPageVisit']);
    }
}
