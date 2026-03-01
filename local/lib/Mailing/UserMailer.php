<?php

namespace Local\Mailing;

class UserMailer
{
    public static function sendPersonalized(int $userId, string $siteId, string $html, array $config): void
    {
        if ($userId <= 0) {
            return;
        }

        $user = \CUser::GetByID($userId)->Fetch();
        if (!$user) {
            return;
        }

        $recipient = [
            'EMAIL' => (string)($user['EMAIL'] ?? ''),
            'PHONE' => (string)($user['PERSONAL_PHONE'] ?? ''),
            'TELEGRAM_CHAT_ID' => (string)($user['UF_TELEGRAM_CHAT_ID'] ?? ''),
        ];

        $subject = 'Персональные рекомендации для вас';
        ChannelDispatcher::send($siteId, $config['CHANNELS'], $recipient, $subject, $html, ['USER_ID' => $userId]);

        Analytics::log($siteId, 'personalized_sent', ['user_id' => $userId], $config);
    }

    public static function sendHolidayCampaign(string $siteId, array $config): void
    {
        $inactiveUsers = self::collectInactiveUsers($config);

        foreach ($inactiveUsers as $userId => $payload) {
            $shortUrl = ShortLinkManager::create(
                $siteId,
                (int)$userId,
                (string)$config['TRIGGERS']['REVIEW_FORM_URL'],
                $config
            );

            $message = 'С праздником! Оставьте, пожалуйста, отзыв: ' . $shortUrl;
            ChannelDispatcher::send($siteId, $config['CHANNELS'], $payload['recipient'], 'Праздничное предложение', $message, [
                'USER_ID' => (int)$userId,
                'SHORT_URL' => $shortUrl,
            ]);

            Analytics::log($siteId, 'holiday_campaign_sent', [
                'user_id' => (int)$userId,
                'short_url' => $shortUrl,
            ], $config);
        }
    }

    private static function collectInactiveUsers(array $config): array
    {
        $days = (int)$config['TRIGGERS']['REENGAGE_DAYS'];
        $border = date('d.m.Y', strtotime('-' . $days . ' days'));
        $by = 'ID';
        $order = 'ASC';
        $users = \CUser::GetList(
            $by,
            $order,
            ['ACTIVE' => 'Y', '<LAST_LOGIN' => $border],
            ['SELECT' => ['UF_TELEGRAM_CHAT_ID']]
        );

        $result = [];
        while ($user = $users->Fetch()) {
            $result[(int)$user['ID']] = [
                'recipient' => [
                    'EMAIL' => (string)($user['EMAIL'] ?? ''),
                    'PHONE' => (string)($user['PERSONAL_PHONE'] ?? ''),
                    'TELEGRAM_CHAT_ID' => (string)($user['UF_TELEGRAM_CHAT_ID'] ?? ''),
                ],
            ];
        }

        return $result;
    }
}
