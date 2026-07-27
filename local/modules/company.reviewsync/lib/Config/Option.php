<?php

declare(strict_types=1);

namespace Company\ReviewSync\Config;

use Bitrix\Main\Config\Option as BitrixOption;

final class Option
{
    public const MODULE_ID = 'company.reviewsync';

    public const IBLOCK_ID = 'iblock_id';
    public const MODERATION_ACTIVE = 'moderation_active';
    public const NOTIFY_EMAIL = 'notify_email';
    public const TELEGRAM_BOT_TOKEN = 'telegram_bot_token';
    public const TELEGRAM_CHAT_ID = 'telegram_chat_id';
    public const REPLY_TEMPLATES = 'reply_templates';

    public const YANDEX_API_KEY = 'yandex_api_key';
    public const YANDEX_ORG_ID = 'yandex_org_id';

    public const GOOGLE_API_KEY = 'google_api_key';
    public const GOOGLE_PLACE_ID = 'google_place_id';

    public const TWOGIS_API_KEY = 'twogis_api_key';
    public const TWOGIS_BRANCH_ID = 'twogis_branch_id';

    public static function getString(string $name, string $default = ''): string
    {
        return trim((string) BitrixOption::get(self::MODULE_ID, $name, $default));
    }

    public static function getInt(string $name, int $default = 0): int
    {
        return (int) BitrixOption::get(self::MODULE_ID, $name, (string) $default);
    }

    public static function getBool(string $name, bool $default = false): bool
    {
        return BitrixOption::get(self::MODULE_ID, $name, $default ? 'Y' : 'N') === 'Y';
    }

    /** @return string[] */
    public static function getReplyTemplates(): array
    {
        $raw = self::getString(self::REPLY_TEMPLATES);
        if ($raw === '') {
            return [];
        }

        $result = [];
        foreach (preg_split('/\R/u', $raw) as $line) {
            $line = trim((string) $line);
            if ($line !== '') {
                $result[] = $line;
            }

            if (count($result) >= 10) {
                break;
            }
        }

        return $result;
    }
}
