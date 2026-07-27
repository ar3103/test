<?php

namespace Local\Mailing;

use Bitrix\Main\Loader;
use Bitrix\Main\SiteTable;

Loader::includeModule('iblock');

class Agent
{
    public static function getSiteConfig($siteId)
    {
        $file = $_SERVER['DOCUMENT_ROOT'] . '/local/sites/' . $siteId . '/include/mailing.php';
        if (!file_exists($file)) {
            return false;
        }

        return include $file;
    }

    public static function sendFeedbackLinks()
    {
        $siteIterator = SiteTable::getList([
            'select' => ['LID'],
            'filter' => ['=ACTIVE' => 'Y'],
        ]);

        while ($site = $siteIterator->fetch()) {
            $config = self::getSiteConfig($site['LID']);
            if (!$config) {
                continue;
            }

            FeedbackSender::sendForCompletedOrders($site['LID'], $config);
        }

        return __METHOD__ . '();';
    }
}
