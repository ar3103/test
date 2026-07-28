<?php

declare(strict_types=1);

namespace MultiRegion;

final class IblockFormManager
{
    private LanguageManager $languageManager;

    public function __construct()
    {
        $this->languageManager = new LanguageManager();
    }

    public function getRegionFormConfig(string $regionCode): array
    {
        $regions = Config::regions();
        if (!isset($regions[$regionCode])) {
            throw new \InvalidArgumentException('Unknown region: ' . $regionCode);
        }

        $lang = $regions[$regionCode]['lang'] ?? 'en';

        return [
            'IBLOCK_ID' => 20,
            'USE_CAPTCHA' => 'Y',
            'PROPERTY_CODES' => ['NAME', 'PHONE', 'EMAIL', 'MESSAGE', 'REGION'],
            'DEFAULT_VALUES' => [
                'REGION' => $regionCode,
                'CITY' => $regions[$regionCode]['city'],
            ],
            'FIELD_LABELS' => $this->languageManager->translateFields([
                'NAME' => 'field_name',
                'PHONE' => 'field_phone',
                'EMAIL' => 'field_email',
                'MESSAGE' => 'field_message',
                'REGION' => 'field_region',
                'CITY' => 'field_city',
            ], $lang),
            'FIELD_PLACEHOLDERS' => $this->languageManager->translateFields([
                'NAME' => 'placeholder_name',
                'PHONE' => 'placeholder_phone',
                'EMAIL' => 'placeholder_email',
                'MESSAGE' => 'placeholder_message',
            ], $lang),
            'SUBMIT_BUTTON_TEXT' => $this->languageManager->translate('confirm_region', $lang),
        ];
    }
}
