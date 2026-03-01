<?php

namespace Local\Mailing;

class Triggers
{
    public static function sendCartReminders(string $siteId, array $config): void
    {
        $rows = self::getLeadsByIblock((int)$config['TRIGGERS']['CART_IBLOCK_ID'], '-1 day');
        self::sendLeadCampaign($siteId, $config, $rows, 'Вы забыли товары в корзине', 'У вас остались товары в корзине. Завершите оформление: ' . self::pageLink('/personal/cart/'), 'trigger_cart_sent');
    }

    public static function sendWebinarFollowup(string $siteId, array $config): void
    {
        $rows = self::getLeadsByIblock((int)$config['TRIGGERS']['WEBINAR_IBLOCK_ID'], '-3 day');
        self::sendLeadCampaign($siteId, $config, $rows, 'Спасибо за интерес к вебинару', 'Мы подготовили материалы вебинара: ' . self::pageLink('/webinars/materials/'), 'trigger_webinar_sent');
    }

    public static function sendReengagement(string $siteId, array $config): void
    {
        $days = (int)$config['TRIGGERS']['REENGAGE_DAYS'];
        $rows = self::getLeadsByIblock((int)$config['FORMS_IBLOCK_ID'], '-' . $days . ' day');
        self::sendLeadCampaign($siteId, $config, $rows, 'Мы скучаем, возвращайтесь!', 'Спецпредложение для вас и новые статьи в блоге: ' . self::pageLink('/blog/'), 'trigger_reengagement_sent');
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


    private static function sendLeadCampaign(
        string $siteId,
        array $config,
        array $rows,
        string $subject,
        string $message,
        string $analyticsEvent
    ): void {
        foreach ($rows as $row) {
            $leadId = (int)($row['ID'] ?? 0);
            if ($leadId <= 0 || self::isAlreadySent($siteId, $leadId, $analyticsEvent, $config)) {
                continue;
            }

            $recipient = self::extractRecipient($row);
            ChannelDispatcher::send($siteId, $config['CHANNELS'], $recipient, $subject, $message, ['LEAD_ID' => $leadId]);
            Analytics::log($siteId, $analyticsEvent, ['lead_id' => $leadId], $config);
        }
    }

    private static function isAlreadySent(string $siteId, int $leadId, string $analyticsEvent, array $config): bool
    {
        $analyticsIblockId = (int)($config['ANALYTICS']['IBLOCK_ID'] ?? 0);
        if ($analyticsIblockId <= 0) {
            return false;
        }

        $payloadNeedle = '"lead_id":' . $leadId;
        $res = \CIBlockElement::GetList(
            ['ID' => 'DESC'],
            [
                'IBLOCK_ID' => $analyticsIblockId,
                'ACTIVE' => 'Y',
                '=PROPERTY_SITE_ID' => $siteId,
                '=PROPERTY_EVENT_TYPE' => $analyticsEvent,
                '%PROPERTY_PAYLOAD' => $payloadNeedle,
            ],
            false,
            ['nTopCount' => 1],
            ['ID']
        );

        return (bool)$res->Fetch();
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
