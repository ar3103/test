<?php

namespace Local\Mailing;

use Bitrix\Main\Loader;
use Bitrix\Main\SiteTable;

Loader::includeModule('iblock');

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

    public static function sendFeedbackLinks(): string
    {
        $sites = SiteTable::getList([
            'select' => ['LID'],
            'filter' => ['=ACTIVE' => 'Y'],
        ]);

        while ($site = $sites->fetch()) {
            $config = self::getSiteConfig($site['LID']);
            if ($config === null) {
                continue;
            }

            FeedbackSender::sendForCompletedOrders($site['LID'], $config);
        }

        return __METHOD__ . '();';
    }
}
