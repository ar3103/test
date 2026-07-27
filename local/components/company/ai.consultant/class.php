<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) {
    die();
}

use Bitrix\Main\Engine\ActionFilter\Csrf;
use Bitrix\Main\Engine\Contract\Controllerable;
use Bitrix\Main\Error;
use Bitrix\Main\Loader;

require_once __DIR__ . '/lib/ConsultantService.php';

class CompanyAiConsultantComponent extends CBitrixComponent implements Controllerable
{
    private ConsultantService $service;

    public function configureActions(): array
    {
        return [
            'sendMessage' => [
                'prefilters' => [new Csrf()],
            ],
            'rateDialog' => [
                'prefilters' => [new Csrf()],
            ],
        ];
    }

    public function onPrepareComponentParams($params): array
    {
        $params['DIALOG_IBLOCK_CODE'] = (string)($params['DIALOG_IBLOCK_CODE'] ?? 'ai_dialogs');
        $params['FAQ_IBLOCK_CODE'] = (string)($params['FAQ_IBLOCK_CODE'] ?? 'ai_faq');
        $params['TELEGRAM_CHAT_ID'] = (string)($params['TELEGRAM_CHAT_ID'] ?? '');

        return $params;
    }

    public function executeComponent(): void
    {
        if (!Loader::includeModule('iblock')) {
            ShowError('Не подключен модуль iblock.');
            return;
        }

        $this->service = new ConsultantService(
            SITE_ID,
            $this->arParams['DIALOG_IBLOCK_CODE'],
            $this->arParams['FAQ_IBLOCK_CODE'],
            $this->arParams['TELEGRAM_CHAT_ID']
        );

        $this->arResult['COMPONENT_NAME'] = $this->getName();
        $this->arResult['SIGNED_PARAMS'] = $this->getSignedParameters();

        $this->includeComponentTemplate();
    }

    public function sendMessageAction(string $sessionId, string $message, array $contacts = []): ?array
    {
        $this->initService();

        $message = trim($message);
        if ($message === '') {
            $this->addError(new Error('Пустое сообщение.'));
            return null;
        }

        return $this->service->processMessage($sessionId, $message, $contacts);
    }

    public function rateDialogAction(string $sessionId, int $rating, string $comment = ''): ?array
    {
        $this->initService();

        if ($rating < 1 || $rating > 5) {
            $this->addError(new Error('Оценка должна быть от 1 до 5.'));
            return null;
        }

        $result = $this->service->rateDialog($sessionId, $rating, $comment);
        if (!$result) {
            $this->addError(new Error('Диалог не найден.'));
            return null;
        }

        return [
            'success' => true,
            'autoReply' => 'Спасибо за оценку! Если нужно, могу помочь с новым вопросом.',
        ];
    }

    private function initService(): void
    {
        if (isset($this->service)) {
            return;
        }

        $this->service = new ConsultantService(
            SITE_ID,
            $this->arParams['DIALOG_IBLOCK_CODE'],
            $this->arParams['FAQ_IBLOCK_CODE'],
            $this->arParams['TELEGRAM_CHAT_ID']
        );
    }
}
