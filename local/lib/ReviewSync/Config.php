<?php

declare(strict_types=1);

namespace Company\ReviewSync;

use Bitrix\Main\Config\Option;

final class Config
{
    public const MODULE_ID = 'company.reviewsync';

    public static function getReviewsIblockId(): int
    {
        return (int)Option::get(self::MODULE_ID, 'reviews_iblock_id', '0');
    }

    public static function getImportBatchSize(): int
    {
        return max(1, (int)Option::get(self::MODULE_ID, 'import_batch_size', '50'));
    }

    public static function getProviderConfig(string $providerCode): array
    {
        return [
            'endpoint' => Option::get(self::MODULE_ID, sprintf('%s_endpoint', $providerCode), ''),
            'api_key' => Option::get(self::MODULE_ID, sprintf('%s_api_key', $providerCode), ''),
            'place_id' => Option::get(self::MODULE_ID, sprintf('%s_place_id', $providerCode), ''),
        ];
    }

    public static function getNotificationEmail(): string
    {
        return Option::get(self::MODULE_ID, 'notification_email', '');
    }

    public static function getTelegramBotToken(): string
    {
        return Option::get(self::MODULE_ID, 'telegram_bot_token', '');
    }

    public static function getTelegramChatId(): string
    {
        return Option::get(self::MODULE_ID, 'telegram_chat_id', '');
    }

    /** @return string[] */
    public static function getAutoReplyVariants(): array
    {
        $raw = Option::get(self::MODULE_ID, 'auto_reply_variants', '');
        $items = array_filter(array_map('trim', explode(PHP_EOL, $raw)));

        return array_slice(array_values($items), 0, 10);
    }

    public static function getDefaultAutoReply(): string
    {
        return 'Спасибо за ваш отзыв! Мы рады, что вы выбрали нашу компанию.';
    }
}
