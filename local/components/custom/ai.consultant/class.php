<?php

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) {
    die();
}

class CustomAiConsultantComponent extends CBitrixComponent
{
    public function executeComponent(): void
    {
        global $APPLICATION;

        $this->arResult['SITE_ID'] = SITE_ID;
        $this->arResult['TITLE'] = $this->arParams['TITLE'] ?? 'Онлайн-консультант';
        $this->arResult['GREETING'] = $this->arParams['GREETING'] ?? 'Здравствуйте! Чем могу помочь?';

        $APPLICATION->AddHeadScript($this->GetPath() . '/templates/.default/script.js');
        $this->includeComponentTemplate();
    }
}
