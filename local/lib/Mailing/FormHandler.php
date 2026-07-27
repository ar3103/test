<?php

namespace Local\Mailing;

class FormHandler
{
    public static function onFormAdd(array &$fields): void
    {
        $iblockId = (int)($fields['IBLOCK_ID'] ?? 0);
        $siteId = (string)($fields['LID'] ?? SITE_ID);
        $config = Agent::getSiteConfig($siteId);
        if (!$config || $iblockId !== (int)$config['FORMS_IBLOCK_ID']) {
            return;
        }

        $emailCode = $config['PROPERTIES']['FORM_EMAIL'];
        $nameCode = $config['PROPERTIES']['FORM_NAME'];
        $userCode = $config['PROPERTIES']['FORM_USER'];

        $email = self::readPropertyValue($fields['PROPERTY_VALUES'] ?? [], $emailCode);
        if ($email === '') {
            return;
        }

        $userId = (int)self::readPropertyValue($fields['PROPERTY_VALUES'] ?? [], $userCode);
        $name = self::readPropertyValue($fields['PROPERTY_VALUES'] ?? [], $nameCode);
        $history = self::getUserHistory($userId, $config);

        \CEvent::Send(
            $config['EVENT_TYPES']['FORM_AUTO_RESPONSE'],
            $siteId,
            [
                'EMAIL_TO' => $email,
                'USER_NAME' => $name !== '' ? $name : 'Клиент',
                'HISTORY' => $history,
            ]
        );

        // Автоответ на повторное обращение с историей сообщений
        if ($history !== '') {
            \CEvent::Send(
                $config['EVENT_TYPES']['FORM_REPEAT_RESPONSE'],
                $siteId,
                [
                    'EMAIL_TO' => $email,
                    'USER_NAME' => $name !== '' ? $name : 'Клиент',
                    'HISTORY' => $history,
                ]
            );
        }

        Analytics::log($siteId, 'form_auto_response_sent', [
            'email' => $email,
            'user_id' => $userId,
            'element_id' => (int)($fields['ID'] ?? 0),
        ], $config);
    }

    public static function onAfterUserAdd(array &$fields): void
    {
        if (($fields['RESULT_MESSAGE'] ?? '') === '') {
            return;
        }
    }

    private static function getUserHistory(int $userId, array $config): string
    {
        if ($userId <= 0) {
            return '';
        }

        $res = \CIBlockElement::GetList(
            ['ID' => 'DESC'],
            [
                'IBLOCK_ID' => (int)$config['FORMS_IBLOCK_ID'],
                'PROPERTY_' . $config['PROPERTIES']['FORM_USER'] => $userId,
            ],
            false,
            ['nTopCount' => 5],
            ['ID', 'NAME', 'DATE_CREATE']
        );

        $history = [];
        while ($item = $res->GetNext()) {
            $history[] = sprintf('#%s %s (%s)', $item['ID'], $item['NAME'], $item['DATE_CREATE']);
        }

        return implode("\n", $history);
    }

    private static function readPropertyValue(array $properties, string $code): string
    {
        if (!isset($properties[$code])) {
            return '';
        }

        $value = $properties[$code];
        if (is_array($value)) {
            $first = reset($value);
            if (is_array($first)) {
                return trim((string)($first['VALUE'] ?? ''));
            }

            return trim((string)$first);
        }

        return trim((string)$value);
    }
}
