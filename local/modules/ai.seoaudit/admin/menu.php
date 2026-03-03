<?php

use Bitrix\Main\Loader;

if (!Loader::includeModule('ai.seoaudit')) {
    return false;
}

return [
    'parent_menu' => 'global_menu_services',
    'section' => 'ai_seoaudit',
    'sort' => 50,
    'text' => 'AI SEO Audit',
    'title' => 'AI SEO Audit',
    'icon' => 'sys_menu_icon',
    'page_icon' => 'sys_page_icon',
    'items_id' => 'menu_ai_seoaudit',
    'items' => [
        ['text' => 'Dashboard', 'url' => 'ai_seoaudit_dashboard.php?lang=' . LANGUAGE_ID, 'title' => 'SEO дашборд'],
        ['text' => 'Setup Wizard', 'url' => 'ai_seoaudit_wizard.php?lang=' . LANGUAGE_ID, 'title' => 'Настройка API'],
        ['text' => 'Entities', 'url' => 'ai_seoaudit_entities.php?lang=' . LANGUAGE_ID, 'title' => 'Tenant/Project/Task'],
        ['text' => 'Reports & RAG', 'url' => 'ai_seoaudit_reports.php?lang=' . LANGUAGE_ID, 'title' => 'Отчеты и база знаний'],
    ],
];
