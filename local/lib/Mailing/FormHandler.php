<?php

namespace Local\Mailing;

use CEvent;

class FormHandler
{
    public static function onFormAdd(array &$arFields): void
    {
        $propertyValues = $arFields['PROPERTY_VALUES'] ?? [];
        $email = self::extractPropertyValue($propertyValues, 'EMAIL');

        if (empty($email)) {
            return;
        }

        CEvent::Send(
            'FEEDBACK_THANKS',
            SITE_ID,
            [
                'EMAIL' => $email,
                'USER_NAME' => self::extractPropertyValue($propertyValues, 'NAME') ?: '',
            ]
        );
    }

    private static function extractPropertyValue(array $propertyValues, string $code): ?string
    {
        if (!isset($propertyValues[$code])) {
            return null;
        }

        $value = $propertyValues[$code];

        if (is_array($value)) {
            if (isset($value['VALUE'])) {
                return is_array($value['VALUE']) ? (string) reset($value['VALUE']) : (string) $value['VALUE'];
            }
            return (string) reset($value);
        }

        return (string) $value;
    }
}
