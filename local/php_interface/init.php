<?php

use Bitrix\Main\Loader;
use Local\Mailing\Agent;
use Local\Mailing\FormHandler;

Loader::registerAutoLoadClasses(null, [
    'Local\\Mailing\\Agent' => '/local/lib/Mailing/Agent.php',
    'Local\\Mailing\\FeedbackSender' => '/local/lib/Mailing/FeedbackSender.php',
    'Local\\Mailing\\FormHandler' => '/local/lib/Mailing/FormHandler.php',
    'Local\\Mailing\\UserNotifier' => '/local/lib/Mailing/UserNotifier.php',
    'Local\\Mailing\\LinkTracker' => '/local/lib/Mailing/LinkTracker.php',
]);

AddEventHandler('iblock', 'OnAfterIBlockElementAdd', [FormHandler::class, 'onFormAdd']);

if (!CAgent::GetList([], ['NAME' => Agent::class . '::sendFeedbackLinks();'])->Fetch()) {
    CAgent::AddAgent(
        Agent::class . '::sendFeedbackLinks();',
        '',
        'N',
        86400
    );
}
