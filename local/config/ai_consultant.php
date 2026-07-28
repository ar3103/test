<?php

return [
    'default' => [
        'openai_api_key' => getenv('OPENAI_API_KEY') ?: '',
        'openai_model' => 'gpt-4o-mini',
        'knowledge_iblock_id' => 7,
        'dialog_iblock_id' => 8,
        'telegram_bot_token' => getenv('TELEGRAM_BOT_TOKEN') ?: '',
        'telegram_chat_id' => getenv('TELEGRAM_CHAT_ID') ?: '',
        'email_to' => 'sales@example.com',
        'auto_reply' => 'Спасибо за обращение! Мы уже передали ваш запрос менеджеру и скоро свяжемся с вами.',
    ],
    's1' => [
        'knowledge_iblock_id' => 11,
        'dialog_iblock_id' => 12,
        'email_to' => 'sales-site1@example.com',
    ],
    's2' => [
        'knowledge_iblock_id' => 21,
        'dialog_iblock_id' => 22,
        'email_to' => 'sales-site2@example.com',
    ],
];
