<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) {
    die();
}

$arComponentDescription = [
    'NAME' => 'AI онлайн-консультант',
    'DESCRIPTION' => 'Виджет онлайн-консультанта на базе ChatGPT с поддержкой многосайтовости.',
    'ICON' => '/images/icon.gif',
    'SORT' => 10,
    'CACHE_PATH' => 'Y',
    'PATH' => [
        'ID' => 'company',
        'NAME' => 'Компания',
    ],
];
