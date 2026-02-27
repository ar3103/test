<?php

return [
    'IBLOCKS' => [
        'BLOG' => 17,
        'REQUESTS' => 19,
    ],
    'BLOG' => [
        'SEND_CHECKBOX_CODE' => 'SEND_EMAIL',
    ],
    'FORMS' => [
        'EMAIL_PROP_ID' => 51,
    ],
    'EVENTS' => [
        'BLOG_WEEKLY_DIGEST' => 'BLOG_WEEKLY_DIGEST_S2',
        'FORM_THANK_YOU' => 'FORM_THANK_YOU_S2',
        'REENGAGEMENT' => 'REENGAGEMENT_S2',
        'TRIGGER_PAGE_VISIT' => 'TRIGGER_PAGE_VISIT_S2',
        'REPEAT_REQUEST_REPLY' => 'REPEAT_REQUEST_REPLY_S2',
        'MULTI_CHANNEL' => 'MULTI_CHANNEL_TOUCH_S2',
    ],
    'TRIGGERS' => [
        'PAGES' => ['/cart/', '/webinar/signup/'],
    ],
    'REENGAGEMENT' => [
        'DAYS_INACTIVE' => 60,
    ],
    'REVIEW_FORM_URL' => 'https://s2.example.com/review/',
    'SHORT_LINK_BASE_URL' => 'https://s2.example.com',
    'AB_VARIANTS' => [
        ['subject' => 'Спецпредложение для вас', 'body' => 'Вернитесь и получите скидку.'],
        ['subject' => 'Подарок постоянному клиенту', 'body' => 'Активируйте бонус по ссылке.'],
    ],
    'SMS' => [
        'WEBHOOK' => 'https://sms-gateway.example.com/send-s2',
    ],
    'TELEGRAM' => [
        'BOT_TOKEN' => 'BOT_TOKEN_FOR_S2',
    ],
    'ABANDONED_CART' => [
        'EMAILS' => ['lead2@example.com'],
    ],
];
