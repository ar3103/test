<?php

return [
    'IBLOCKS' => [
        'BLOG' => 7,
        'REQUESTS' => 9,
    ],
    'BLOG' => [
        'SEND_CHECKBOX_CODE' => 'SEND_EMAIL',
    ],
    'FORMS' => [
        'EMAIL_PROP_ID' => 41,
    ],
    'EVENTS' => [
        'BLOG_WEEKLY_DIGEST' => 'BLOG_WEEKLY_DIGEST',
        'FORM_THANK_YOU' => 'FORM_THANK_YOU',
        'REENGAGEMENT' => 'REENGAGEMENT',
        'TRIGGER_PAGE_VISIT' => 'TRIGGER_PAGE_VISIT',
        'REPEAT_REQUEST_REPLY' => 'REPEAT_REQUEST_REPLY',
        'MULTI_CHANNEL' => 'MULTI_CHANNEL_TOUCH',
    ],
    'TRIGGERS' => [
        'PAGES' => ['/cart/', '/webinar/signup/'],
    ],
    'REENGAGEMENT' => [
        'DAYS_INACTIVE' => 45,
    ],
    'REVIEW_FORM_URL' => 'https://s1.example.com/review/',
    'SHORT_LINK_BASE_URL' => 'https://s1.example.com',
    'AB_VARIANTS' => [
        ['subject' => 'С праздником! Дарим подарок', 'body' => 'Возвращайтесь, для вас персональная акция.'],
        ['subject' => 'Мы давно вас не видели', 'body' => 'Для вас бонус за повторный визит.'],
    ],
    'SMS' => [
        'WEBHOOK' => 'https://sms-gateway.example.com/send',
    ],
    'TELEGRAM' => [
        'BOT_TOKEN' => 'BOT_TOKEN_FOR_S1',
    ],
    'ABANDONED_CART' => [
        'EMAILS' => ['lead1@example.com'],
    ],
];
