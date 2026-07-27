<?php

namespace Local\Mailing;

use CEvent;

class FormHandler
{
    public static function onFormAdd(&$fields)
    {
        $siteId = defined('SITE_ID') ? SITE_ID : 's1';
        $config = Agent::getSiteConfig($siteId);
        if (!$config) {
            return;
        }

        if ((int) $fields['IBLOCK_ID'] !== (int) $config['REVIEWS_IBLOCK_ID']) {
            return;
        }

        $propertyValues = isset($fields['PROPERTY_VALUES']) ? $fields['PROPERTY_VALUES'] : [];
        $email = isset($propertyValues['EMAIL']) ? $propertyValues['EMAIL'] : '';

        if (!$email) {
            return;
        }

        CEvent::Send(
            'FEEDBACK_THANKS',
            $siteId,
            [
                'EMAIL' => $email,
                'USER_NAME' => isset($propertyValues['NAME']) ? $propertyValues['NAME'] : '',
            ]
        );
    }
}
