<?php

use Bitrix\Main\Context;
use Bitrix\Main\Loader;
use Local\AiConsultant\DialogueService;
use Local\AiConsultant\GptClient;
use Local\AiConsultant\KnowledgeBase;
use Local\AiConsultant\LeadRepository;
use Local\AiConsultant\NotificationService;

require $_SERVER['DOCUMENT_ROOT'] . '/bitrix/modules/main/include/prolog_before.php';

header('Content-Type: application/json; charset=utf-8');

if (!Loader::includeModule('iblock')) {
    echo json_encode(['error' => 'iblock module is required']);
    exit;
}

$request = Context::getCurrent()->getRequest();
$siteId = SITE_ID;
$action = (string)$request->getPost('action');
$sessionId = (string)$request->getPost('session_id');
$message = (string)$request->getPost('message');

$contact = [
    'name' => (string)$request->getPost('name'),
    'phone' => (string)$request->getPost('phone'),
    'interest' => (string)$request->getPost('interest'),
    'history' => json_decode((string)$request->getPost('history'), true) ?: [],
    'rating' => (string)$request->getPost('rating'),
];

$service = new DialogueService(new KnowledgeBase(), new GptClient(), new LeadRepository(), new NotificationService());

switch ($action) {
    case 'message':
        echo json_encode($service->reply($siteId, $sessionId, $message, $contact), JSON_UNESCAPED_UNICODE);
        break;

    case 'finish':
        echo json_encode($service->closeDialog($siteId, $sessionId, $contact), JSON_UNESCAPED_UNICODE);
        break;

    default:
        echo json_encode(['error' => 'unknown action']);
}
