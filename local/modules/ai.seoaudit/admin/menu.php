<?php

use Bitrix\Main\Loader;
use Bitrix\Main\Localization\Loc;

Loc::loadMessages(__FILE__);

if (!Loader::includeModule('ai.seoaudit')) {
    return false;
}

return [
    'parent_menu' => 'global_menu_services',
    'section' => 'ai_seoaudit',
    'sort' => 50,
    'text' => Loc::getMessage('AI_SEO_MENU_TITLE'),
    'title' => Loc::getMessage('AI_SEO_MENU_TITLE'),
    'icon' => 'sys_menu_icon',
    'page_icon' => 'sys_page_icon',
    'items_id' => 'menu_ai_seoaudit',
    'items' => [
        ['text' => Loc::getMessage('AI_SEO_MENU_DASHBOARD'), 'url' => 'ai_seoaudit_dashboard.php?lang=' . LANGUAGE_ID, 'title' => Loc::getMessage('AI_SEO_MENU_DASHBOARD')],
        ['text' => Loc::getMessage('AI_SEO_MENU_WIZARD'), 'url' => 'ai_seoaudit_wizard.php?lang=' . LANGUAGE_ID, 'title' => Loc::getMessage('AI_SEO_MENU_WIZARD')],
        ['text' => Loc::getMessage('AI_SEO_MENU_ENTITIES'), 'url' => 'ai_seoaudit_entities.php?lang=' . LANGUAGE_ID, 'title' => Loc::getMessage('AI_SEO_MENU_ENTITIES')],
        ['text' => Loc::getMessage('AI_SEO_MENU_REPORTS'), 'url' => 'ai_seoaudit_reports.php?lang=' . LANGUAGE_ID, 'title' => Loc::getMessage('AI_SEO_MENU_REPORTS')],
        ['text' => Loc::getMessage('AI_SEO_MENU_MARKETPLACE'), 'url' => 'ai_seoaudit_marketplace.php?lang=' . LANGUAGE_ID, 'title' => Loc::getMessage('AI_SEO_MENU_MARKETPLACE')],
        ['text' => Loc::getMessage('AI_SEO_MENU_ENTERPRISE'), 'url' => 'ai_seoaudit_enterprise.php?lang=' . LANGUAGE_ID, 'title' => Loc::getMessage('AI_SEO_MENU_ENTERPRISE')],
    ],
];
