<?php

namespace Local\Mailing;

use Bitrix\Main\Loader;
use Bitrix\Sender\PostingTable;
use Bitrix\Sender\PostingRecipientTable;

class BlogSender
{
    public static function sendWeekly(string $siteId, array $config): void
    {
        if (!Loader::includeModule('iblock') || !Loader::includeModule('sender')) {
            return;
        }

        $fromDate = ConvertTimeStamp(strtotime('-7 days'), 'FULL');
        $filter = [
            'IBLOCK_ID' => (int)$config['BLOG_IBLOCK_ID'],
            '>=DATE_ACTIVE_FROM' => $fromDate,
            'PROPERTY_' . $config['PROPERTIES']['BLOG_SEND'] => 'Y',
            '!PROPERTY_' . $config['PROPERTIES']['BLOG_SENT'] => 'Y',
            'ACTIVE' => 'Y',
        ];

        $result = \CIBlockElement::GetList(
            ['DATE_ACTIVE_FROM' => 'ASC'],
            $filter,
            false,
            false,
            ['ID', 'NAME', 'DETAIL_PAGE_URL', 'DATE_ACTIVE_FROM']
        );

        $articles = [];
        while ($item = $result->GetNext()) {
            $articles[] = $item;
        }

        if (!$articles) {
            return;
        }

        $variant = random_int(0, 1) ? 'A' : 'B';
        $subject = $variant === 'A' ? $config['AB_TEST']['A_SUBJECT'] : $config['AB_TEST']['B_SUBJECT'];
        $messageBody = self::formatArticles($articles, $subject);

        $posting = PostingTable::add([
            'NAME' => 'Blog weekly ' . date('d.m.Y') . ' [' . $variant . ']',
            'STATUS' => PostingTable::STATUS_NEW,
            'FROM_EMAIL' => \COption::GetOptionString('main', 'email_from', 'no-reply@' . $_SERVER['HTTP_HOST']),
            'SUBJECT' => $subject,
            'BODY_TYPE' => 'html',
            'BODY' => $messageBody,
            'SITE_ID' => $siteId,
        ]);

        if (!$posting->isSuccess()) {
            return;
        }

        $postingId = (int)$posting->getId();
        $subscribers = \CSubscription::GetList(
            ['ID' => 'ASC'],
            ['ACTIVE' => 'Y', 'LID' => $siteId]
        );

        while ($subscriber = $subscribers->Fetch()) {
            PostingRecipientTable::add([
                'POSTING_ID' => $postingId,
                'CONTACT_TYPE' => 'EMAIL',
                'CONTACT_CODE' => 'subscr_' . $subscriber['ID'],
                'STATUS' => PostingRecipientTable::SEND_RESULT_NONE,
            ]);
        }

        foreach ($articles as $article) {
            \CIBlockElement::SetPropertyValuesEx(
                $article['ID'],
                (int)$config['BLOG_IBLOCK_ID'],
                [$config['PROPERTIES']['BLOG_SENT'] => 'Y']
            );
        }

        Analytics::log($siteId, 'blog_weekly_prepared', [
            'posting_id' => $postingId,
            'variant' => $variant,
            'article_count' => count($articles),
        ], $config);
    }

    private static function formatArticles(array $articles, string $subject): string
    {
        $html = '<h2>' . htmlspecialcharsbx($subject) . '</h2>';
        $html .= '<p>Новые статьи за неделю:</p><ul>';

        foreach ($articles as $article) {
            $url = $article['DETAIL_PAGE_URL'];
            if (strpos($url, 'http') !== 0) {
                $url = 'https://' . $_SERVER['HTTP_HOST'] . $url;
            }

            $html .= sprintf(
                '<li><a href="%s">%s</a> <small>(%s)</small></li>',
                htmlspecialcharsbx($url),
                htmlspecialcharsbx($article['NAME']),
                htmlspecialcharsbx((string)$article['DATE_ACTIVE_FROM'])
            );
        }

        $html .= '</ul>';

        return $html;
    }
}
