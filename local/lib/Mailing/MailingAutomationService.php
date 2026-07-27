<?php

declare(strict_types=1);

namespace Local\Mailing;

use Bitrix\Iblock\ElementTable;
use Bitrix\Main\Config\Option;
use Bitrix\Main\Context;
use Bitrix\Main\Loader;

final class MailingAutomationService
{
    private const MODULE_ID = 'local.mailing';

    public static function runWeeklyBlogDigest(string $siteId): void
    {
        if (!Loader::includeModule('iblock')) {
            return;
        }

        $config = ConfigResolver::forSite($siteId);
        $from = new \DateTime('-7 days');

        $result = ElementTable::getList([
            'filter' => [
                '=IBLOCK_ID' => (int)$config['IBLOCKS']['BLOG'],
                '=ACTIVE' => 'Y',
                '>=DATE_CREATE' => $from->format('Y-m-d H:i:s'),
            ],
            'select' => ['ID', 'NAME', 'IBLOCK_ID'],
        ]);

        $rows = [];
        while ($row = $result->fetch()) {
            $props = \CIBlockElement::GetProperty((int)$row['IBLOCK_ID'], (int)$row['ID'], [], ['CODE' => $config['BLOG']['SEND_CHECKBOX_CODE']]);
            $prop = $props->Fetch();
            if (($prop['VALUE'] ?? 'N') !== 'Y') {
                continue;
            }
            $rows[] = $row;
        }

        if ($rows === []) {
            return;
        }

        $articlesHtml = '';
        foreach ($rows as $article) {
            $articlesHtml .= sprintf("<li>%s (ID:%d)</li>", htmlspecialcharsbx($article['NAME']), (int)$article['ID']);
        }

        \CEvent::Send($config['EVENTS']['BLOG_WEEKLY_DIGEST'], $siteId, [
            'DIGEST_TITLE' => 'Подборка статей за неделю',
            'ARTICLES' => '<ul>' . $articlesHtml . '</ul>',
        ]);

        Option::set(self::MODULE_ID, 'last_weekly_digest_at_' . $siteId, date('c'));
        AnalyticsService::log($siteId, 'sent', ['type' => 'weekly_digest', 'count' => count($rows)]);
    }

    public static function onFormSaved(array &$fields): void
    {
        $siteId = (string)($fields['LID'] ?? Context::getCurrent()->getSite());
        $config = ConfigResolver::forSite($siteId);

        if ((int)($fields['IBLOCK_ID'] ?? 0) !== (int)$config['IBLOCKS']['REQUESTS']) {
            return;
        }

        $email = (string)($fields['PROPERTY_VALUES'][$config['FORMS']['EMAIL_PROP_ID']][0]['VALUE'] ?? '');
        if ($email === '') {
            return;
        }

        \CEvent::Send($config['EVENTS']['FORM_THANK_YOU'], $siteId, [
            'EMAIL_TO' => $email,
            'MESSAGE' => 'Спасибо за оформление заявки. Мы работаем с вашим обращением и скоро свяжемся.',
        ]);

        $history = self::appendHistory($siteId, $email, [
            'request_id' => (int)($fields['ID'] ?? 0),
            'created_at' => date('c'),
        ]);

        if (count($history) > 1) {
            \CEvent::Send($config['EVENTS']['REPEAT_REQUEST_REPLY'], $siteId, [
                'EMAIL_TO' => $email,
                'HISTORY_COUNT' => count($history),
                'HISTORY_JSON' => json_encode($history, JSON_UNESCAPED_UNICODE),
            ]);
        }

        AnalyticsService::log($siteId, 'sent', ['type' => 'form_thank_you']);
    }

    public static function onPageVisit(): void
    {
        $siteId = (string)Context::getCurrent()->getSite();
        $config = ConfigResolver::forSite($siteId);
        $request = Context::getCurrent()->getRequest();

        $page = (string)$request->getRequestedPage();
        $email = (string)$request->get('email');

        if ($email === '' || !in_array($page, $config['TRIGGERS']['PAGES'], true)) {
            return;
        }

        \CEvent::Send($config['EVENTS']['TRIGGER_PAGE_VISIT'], $siteId, [
            'EMAIL_TO' => $email,
            'PAGE' => $page,
        ]);

        AnalyticsService::log($siteId, 'sent', ['type' => 'trigger_page_visit', 'page' => $page]);
    }

    public static function runInactiveUsersCampaign(string $siteId): void
    {
        $config = ConfigResolver::forSite($siteId);
        $contacts = self::getInactiveContacts($siteId, (int)$config['REENGAGEMENT']['DAYS_INACTIVE']);

        foreach ($contacts as $contact) {
            $shortCode = ShortLinkService::create(
                $siteId,
                $config['REVIEW_FORM_URL'],
                (int)$contact['ID']
            );
            $shortUrl = rtrim($config['SHORT_LINK_BASE_URL'], '/') . '/local/tools/short-link.php?code=' . $shortCode;

            $variant = ABTestingService::pickVariant((int)$contact['ID'], $config['AB_VARIANTS']);

            \CEvent::Send($config['EVENTS']['REENGAGEMENT'], $siteId, [
                'EMAIL_TO' => $contact['EMAIL'],
                'SUBJECT' => $variant['subject'],
                'MESSAGE' => $variant['body'],
                'SHORT_URL' => $shortUrl,
            ]);

            MultiChannelCampaignService::runCampaign($config, $contact, [
                'message' => $variant['body'],
                'sms' => 'Мы скучаем! Вернитесь к нам: ' . $shortUrl,
                'telegram' => 'Новая акция для вас: ' . $shortUrl,
                'short_url' => $shortUrl,
            ]);

            AnalyticsService::log($siteId, 'sent', ['type' => 'reengagement', 'contact_id' => $contact['ID']]);
        }

        Option::set(self::MODULE_ID, 'last_reengagement_at_' . $siteId, date('c'));
    }

    public static function runAbandonedCartCampaign(string $siteId): void
    {
        $config = ConfigResolver::forSite($siteId);
        if (empty($config['ABANDONED_CART']['EMAILS'])) {
            return;
        }

        foreach ($config['ABANDONED_CART']['EMAILS'] as $email) {
            \CEvent::Send($config['EVENTS']['TRIGGER_PAGE_VISIT'], $siteId, [
                'EMAIL_TO' => $email,
                'PAGE' => '/cart/',
                'MESSAGE' => 'Вы оставили товары в корзине. Завершите заказ.',
            ]);
            AnalyticsService::log($siteId, 'sent', ['type' => 'abandoned_cart', 'email' => $email]);
        }
    }

    private static function getInactiveContacts(string $siteId, int $days): array
    {
        $sample = [
            ['ID' => 101, 'EMAIL' => 'customer1@example.com', 'NAME' => 'Ivan', 'PHONE' => '+79990000001', 'TELEGRAM_CHAT_ID' => '1001'],
            ['ID' => 102, 'EMAIL' => 'customer2@example.com', 'NAME' => 'Olga', 'PHONE' => '+79990000002', 'TELEGRAM_CHAT_ID' => '1002'],
        ];

        $cutoff = (new \DateTime())->modify('-' . $days . ' days')->format('Y-m-d');
        return array_map(static function (array $row) use ($cutoff, $siteId): array {
            $row['LAST_ACTIVITY_BEFORE'] = $cutoff;
            $row['SITE_ID'] = $siteId;
            return $row;
        }, $sample);
    }

    private static function appendHistory(string $siteId, string $email, array $entry): array
    {
        $key = 'request_history_' . $siteId;
        $all = json_decode((string)Option::get(self::MODULE_ID, $key, '{}'), true);
        if (!is_array($all)) {
            $all = [];
        }

        if (!isset($all[$email]) || !is_array($all[$email])) {
            $all[$email] = [];
        }

        $all[$email][] = $entry;
        Option::set(self::MODULE_ID, $key, json_encode($all, JSON_UNESCAPED_UNICODE));

        return $all[$email];
    }
}
