<?php

use Bitrix\Main\Config\Option;
use Bitrix\Main\EventManager;
use Bitrix\Main\Loader;

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) {
    die();
}

/**
 * Автообновление sitemap при изменениях в инфоблоках.
 */
final class LocalSitemapAutoUpdate
{
    private const OPTION_MODULE = 'main';
    private const OPTION_FLAG = 'local_sitemap_autoupdate_pending';
    private const AGENT_NAME = '\\LocalSitemapAutoUpdate::runAgent();';

    /**
     * @param array<string, mixed> $fields
     */
    public static function onIblockEntityChange(array $fields): void
    {
        $iblockId = (int)($fields['IBLOCK_ID'] ?? 0);
        if ($iblockId <= 0) {
            return;
        }

        if (!self::isAllowedIblock($iblockId)) {
            return;
        }

        self::queueRegeneration();
    }

    public static function queueRegeneration(): void
    {
        if (Option::get(self::OPTION_MODULE, self::OPTION_FLAG, 'N') === 'Y') {
            return;
        }

        Option::set(self::OPTION_MODULE, self::OPTION_FLAG, 'Y');

        if (class_exists('CAgent')) {
            // Отложенный запуск агентом снижает риск блокировки пользовательского запроса.
            CAgent::RemoveAgent(self::AGENT_NAME, 'main');
            CAgent::AddAgent(
                self::AGENT_NAME,
                'main',
                'N',
                60,
                '',
                'Y',
                ConvertTimeStamp(false, 'FULL')
            );
        }
    }

    public static function runAgent(): string
    {
        Option::set(self::OPTION_MODULE, self::OPTION_FLAG, 'N');
        self::regenerateSitemap();

        return '';
    }

    private static function regenerateSitemap(): void
    {
        if (!Loader::includeModule('seo')) {
            return;
        }

        // Проходимся по всем sitemap и запускаем генерацию.
        if (class_exists('CSeoUtils') && method_exists('CSeoUtils', 'GetSitemapList')) {
            $siteMapList = CSeoUtils::GetSitemapList();
            if (is_array($siteMapList)) {
                foreach ($siteMapList as $siteId => $maps) {
                    if (!is_array($maps)) {
                        continue;
                    }

                    foreach ($maps as $map) {
                        $mapId = (int)($map['ID'] ?? 0);
                        if ($mapId <= 0) {
                            continue;
                        }

                        if (class_exists('CSiteMap') && method_exists('CSiteMap', 'ReIndex')) {
                            CSiteMap::ReIndex($siteId, $mapId);
                        }
                    }
                }
            }
        }
    }

    private static function isAllowedIblock(int $iblockId): bool
    {
        // Ограничение по инфоблокам через константу (пример: define('AUTO_SITEMAP_IBLOCK_IDS', [2, 5, 7]);)
        if (!defined('AUTO_SITEMAP_IBLOCK_IDS')) {
            return true;
        }

        $allowed = AUTO_SITEMAP_IBLOCK_IDS;

        return is_array($allowed) ? in_array($iblockId, $allowed, true) : true;
    }
}

$eventManager = EventManager::getInstance();

$events = [
    'OnAfterIBlockElementAdd',
    'OnAfterIBlockElementUpdate',
    'OnAfterIBlockElementDelete',
    'OnAfterIBlockSectionAdd',
    'OnAfterIBlockSectionUpdate',
    'OnAfterIBlockSectionDelete',
];

foreach ($events as $eventName) {
    $eventManager->addEventHandler('iblock', $eventName, [LocalSitemapAutoUpdate::class, 'onIblockEntityChange']);
}
