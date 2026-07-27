<?php

namespace Local\Mailing;

use Bitrix\Main\Loader;
use Bitrix\Main\SiteTable;

class Agent
{
    public static function getSiteConfig(string $siteId): ?array
    {
        $file = $_SERVER['DOCUMENT_ROOT'] . '/local/sites/' . $siteId . '/include/mailing.php';
        if (!file_exists($file)) {
            return null;
        }

        $config = include $file;

        return is_array($config) ? $config : null;
    }

    /**
     * Еженедельная рассылка новых материалов блога.
     */
    public static function weeklyBlogEmail(): string
    {
        if (!Loader::includeModule('iblock') || !Loader::includeModule('sender')) {
            return __METHOD__ . '();';
        }

        $sites = SiteTable::getList(['select' => ['LID']]);
        while ($site = $sites->fetch()) {
            $config = self::getSiteConfig($site['LID']);
            if (!$config) {
                continue;
            }

            BlogSender::sendWeekly($site['LID'], $config);
        }

        return __METHOD__ . '();';
    }

    /**
     * Триггеры после событий и re-engagement.
     */
    public static function triggeredCampaigns(): string
    {
        if (!Loader::includeModule('iblock')) {
            return __METHOD__ . '();';
        }

        $sites = SiteTable::getList(['select' => ['LID']]);
        while ($site = $sites->fetch()) {
            $config = self::getSiteConfig($site['LID']);
            if (!$config) {
                continue;
            }

            Triggers::sendCartReminders($site['LID'], $config);
            Triggers::sendWebinarFollowup($site['LID'], $config);
            Triggers::sendReengagement($site['LID'], $config);
        }

        return __METHOD__ . '();';
    }

    /**
     * Ежедневный агент для поздравлений/ссылок на отзывы.
     */
    public static function holidayAndReviewLinks(): string
    {
        if (!Loader::includeModule('iblock')) {
            return __METHOD__ . '();';
        }

        $sites = SiteTable::getList(['select' => ['LID']]);
        while ($site = $sites->fetch()) {
            $config = self::getSiteConfig($site['LID']);
            if (!$config) {
                continue;
            }

            UserMailer::sendHolidayCampaign($site['LID'], $config);
        }

        return __METHOD__ . '();';
    }
}
