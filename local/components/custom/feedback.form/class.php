<?php

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) {
    die();
}

use Bitrix\Main\Loader;

class CustomFeedbackFormComponent extends CBitrixComponent
{
    public function executeComponent()
    {
        if (!Loader::includeModule('iblock')) {
            ShowError('Module iblock is required');
            return;
        }

        $this->arResult['SUCCESS'] = false;

        if ($_SERVER['REQUEST_METHOD'] === 'POST' && check_bitrix_sessid() && isset($_POST['submit_feedback'])) {
            $this->saveFeedback();
        }

        $this->includeComponentTemplate();
    }

    protected function saveFeedback()
    {
        $name = trim((string) $_POST['NAME']);
        $email = trim((string) $_POST['EMAIL']);
        $text = trim((string) $_POST['TEXT']);
        $rating = (int) $_POST['RATING'];

        if ($name === '' || $email === '' || $text === '' || $rating < 1 || $rating > 5) {
            $this->arResult['ERROR'] = 'Заполните все поля формы корректно.';
            return;
        }

        $iblockId = (int) $this->arParams['IBLOCK_ID'];
        if ($iblockId <= 0) {
            $this->arResult['ERROR'] = 'Не указан инфоблок для отзывов.';
            return;
        }

        $element = new CIBlockElement();
        $id = $element->Add([
            'IBLOCK_ID' => $iblockId,
            'ACTIVE' => 'Y',
            'NAME' => $name,
            'PREVIEW_TEXT' => $text,
            'PROPERTY_VALUES' => [
                'EMAIL' => $email,
                'RATING' => $rating,
            ],
        ]);

        if (!$id) {
            $this->arResult['ERROR'] = 'Ошибка сохранения: ' . $element->LAST_ERROR;
            return;
        }

        $this->arResult['SUCCESS'] = true;
    }
}
