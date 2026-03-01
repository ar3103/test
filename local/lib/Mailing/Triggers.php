<?php

namespace Local\Mailing;

class Triggers
{
    public static function sendCartReminders(string $siteId, array $config): void
    {
        $rows = self::getLeadsByIblock((int)$config['TRIGGERS']['CART_IBLOCK_ID'], '-1 day');
        foreach ($rows as $row) {
            $recipient = self::extractRecipient($row);
            $subject = 'Вы забыли товары в корзине';
            $message = 'У вас остались товары в корзине. Завершите оформление: ' . self::pageLink('/personal/cart/');
            ChannelDispatcher::send($siteId, $config['CHANNELS'], $recipient, $subject, $message, ['LEAD_ID' => $row['ID']]);
            Analytics::log($siteId, 'trigger_cart_sent', ['lead_id' => $row['ID']], $config);
        }
    }

    public static function sendWebinarFollowup(string $siteId, array $config): void
    {
        $rows = self::getLeadsByIblock((int)$config['TRIGGERS']['WEBINAR_IBLOCK_ID'], '-3 day');
        foreach ($rows as $row) {
            $recipient = self::extractRecipient($row);
            $subject = 'Спасибо за интерес к вебинару';
            $message = 'Мы подготовили материалы вебинара: ' . self::pageLink('/webinars/materials/');
            ChannelDispatcher::send($siteId, $config['CHANNELS'], $recipient, $subject, $message, ['LEAD_ID' => $row['ID']]);
            Analytics::log($siteId, 'trigger_webinar_sent', ['lead_id' => $row['ID']], $config);
        }
    }

    public static function sendReengagement(string $siteId, array $config): void
    {
        $days = (int)$config['TRIGGERS']['REENGAGE_DAYS'];
        $rows = self::getLeadsByIblock((int)$config['FORMS_IBLOCK_ID'], '-' . $days . ' day');
        foreach ($rows as $row) {
            $recipient = self::extractRecipient($row);
            $subject = 'Мы скучаем, возвращайтесь!';
            $message = 'Спецпредложение для вас и новые статьи в блоге: ' . self::pageLink('/blog/');
            ChannelDispatcher::send($siteId, $config['CHANNELS'], $recipient, $subject, $message, ['LEAD_ID' => $row['ID']]);
            Analytics::log($siteId, 'trigger_reengagement_sent', ['lead_id' => $row['ID']], $config);
        }
    }

    /**
     * Триггер после посещения конкретной страницы (например, брошенная корзина/вебинар).
     */
    public static function afterVisitedPage(string $siteId, array $config, string $page, array $recipient): void
    {
        $subject = 'Информация по вашему действию на сайте';
        $message = 'Вы посещали страницу: ' . $page . '. Если нужна помощь, ответьте на письмо.';
        ChannelDispatcher::send($siteId, $config['CHANNELS'], $recipient, $subject, $message);

        Analytics::log($siteId, 'trigger_page_visit', ['page' => $page, 'email' => $recipient['EMAIL'] ?? ''], $config);
    }

    private static function getLeadsByIblock(int $iblockId, string $olderThan): array
    {
        if ($iblockId <= 0) {
            return [];
        }

        $date = ConvertTimeStamp(strtotime($olderThan), 'FULL');
        $res = \CIBlockElement::GetList(
            ['ID' => 'DESC'],
            ['IBLOCK_ID' => $iblockId, '<=DATE_CREATE' => $date, 'ACTIVE' => 'Y'],
            false,
            ['nTopCount' => 200],
            ['ID', 'NAME', 'IBLOCK_ID', 'DATE_CREATE', 'PROPERTY_EMAIL', 'PROPERTY_PHONE', 'PROPERTY_TELEGRAM_CHAT_ID']
        );

        $rows = [];
        while ($item = $res->GetNext()) {
            $rows[] = $item;
        }

        return $rows;
    }

    private static function extractRecipient(array $row): array
    {
        return [
            'EMAIL' => (string)($row['PROPERTY_EMAIL_VALUE'] ?? ''),
            'PHONE' => (string)($row['PROPERTY_PHONE_VALUE'] ?? ''),
            'TELEGRAM_CHAT_ID' => (string)($row['PROPERTY_TELEGRAM_CHAT_ID_VALUE'] ?? ''),
        ];
    }

    private static function pageLink(string $path): string
    {
        return 'https://' . $_SERVER['HTTP_HOST'] . $path;
    }
}
