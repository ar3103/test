<?php

return [
    'BLOG_IBLOCK_ID' => 5,
    'FORMS_IBLOCK_ID' => 6,
    'EMAIL_CAMPAIGN_ID' => 10,
    'PROPERTIES' => [
        'BLOG_SEND' => 'SEND_EMAIL',
        'BLOG_SENT' => 'EMAIL_SENT',
        'FORM_EMAIL' => 'EMAIL',
        'FORM_NAME' => 'NAME',
        'FORM_USER' => 'USER_ID',
        'FORM_CREATED' => 'DATE_CREATE',
        'FORM_LAST_TOUCH' => 'LAST_TOUCH',
    ],
    'TRIGGERS' => [
        'CART_IBLOCK_ID' => 7,
        'WEBINAR_IBLOCK_ID' => 8,
        'REENGAGE_DAYS' => 90,
        'REVIEW_FORM_URL' => 'https://example.com/review/',
    ],
    'EVENT_TYPES' => [
        'FORM_AUTO_RESPONSE' => 'FORM_AUTO_RESPONSE',
        'FORM_REPEAT_RESPONSE' => 'FORM_REPEAT_RESPONSE',
        'TRIGGERED_PAGE' => 'TRIGGERED_PAGE',
    ],
    'CHANNELS' => [
        'EMAIL' => true,
        'SMS' => true,
        'TELEGRAM' => true,
    ],
    'AB_TEST' => [
        'A_SUBJECT' => 'Лучшие материалы недели',
        'B_SUBJECT' => 'Подборка блога, которую вы могли пропустить',
    ],
    'SHORT_LINK' => [
        'IBLOCK_ID' => 9,
        'CODE_PREFIX' => 'rvw-',
    ],
    'ANALYTICS' => [
        'IBLOCK_ID' => 10,
    ],
];
