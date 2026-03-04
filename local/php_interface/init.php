<?php

use Bitrix\Main\Config\Option;
use Bitrix\Main\EventManager;
use Bitrix\Main\Loader;

/**
 * Синхронизация sitemap после изменений в инфоблоках.
 *
 * Важно: не используем Runtime::addIblockElement/addIblockSection,
 * т.к. в части проектов этих методов нет (разные версии модуля seo).
 */
final class ProjectSitemapSync
{
    private const OPTION_MODULE = 'main';
    private const OPTION_FLAG = 'project_sitemap_sync_pending';
    private const AGENT_NAME = '\\ProjectSitemapSync::runAgent();';

    /**
     * @param array<string, mixed> $fields
     */
    public static function onEntityChange(array $fields): void
    {
        $iblockId = (int)($fields['IBLOCK_ID'] ?? 0);
        if ($iblockId <= 0 || !self::isAllowedIblock($iblockId)) {
            return;
        }

        self::queue();
    }

    public static function queue(): void
    {
        if (Option::get(self::OPTION_MODULE, self::OPTION_FLAG, 'N') === 'Y') {
            return;
        }

        Option::set(self::OPTION_MODULE, self::OPTION_FLAG, 'Y');

        if (!class_exists('CAgent')) {
            return;
        }

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

    public static function runAgent(): string
    {
        Option::set(self::OPTION_MODULE, self::OPTION_FLAG, 'N');
        self::regenerateAll();

        return '';
    }

    private static function regenerateAll(): void
    {
        if (!Loader::includeModule('seo') || !class_exists('CSeoUtils')) {
            return;
        }

        $sitemaps = CSeoUtils::GetSitemapList();
        if (!is_array($sitemaps)) {
            return;
        }

        foreach ($sitemaps as $siteId => $maps) {
            if (!is_array($maps)) {
                continue;
            }

            foreach ($maps as $map) {
                $mapId = (int)($map['ID'] ?? 0);
                if ($mapId <= 0) {
                    continue;
                }

                self::rebuildMap($siteId, $mapId, $map);
            }
        }
    }

    /**
     * @param array<string, mixed> $map
     */
    private static function rebuildMap(string $siteId, int $mapId, array $map): void
    {
        if (class_exists('CSiteMap') && method_exists('CSiteMap', 'ReIndex')) {
            CSiteMap::ReIndex($siteId, $mapId);
            return;
        }

        if (class_exists('CSiteMap') && method_exists('CSiteMap', 'Generate')) {
            CSiteMap::Generate($mapId, $map);
        }
    }

    private static function isAllowedIblock(int $iblockId): bool
    {
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
    $eventManager->addEventHandler('iblock', $eventName, [ProjectSitemapSync::class, 'onEntityChange']);
}
