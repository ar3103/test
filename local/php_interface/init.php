<?php

use Bitrix\Main\Loader;

Loader::registerAutoLoadClasses(null, [
    'Local\\Mailing\\Agent' => '/local/lib/Mailing/Agent.php',
    'Local\\Mailing\\FeedbackSender' => '/local/lib/Mailing/FeedbackSender.php',
    'Local\\Mailing\\FormHandler' => '/local/lib/Mailing/FormHandler.php',
    'Local\\Mailing\\UserNotifier' => '/local/lib/Mailing/UserNotifier.php',
    'Local\\Mailing\\LinkTracker' => '/local/lib/Mailing/LinkTracker.php',
]);

AddEventHandler('iblock', 'OnAfterIBlockElementAdd', ['Local\\Mailing\\FormHandler', 'onFormAdd']);

if (!CAgent::GetList([], ['NAME' => 'Local\\Mailing\\Agent::sendFeedbackLinks();'])->Fetch()) {
    CAgent::AddAgent(
        'Local\\Mailing\\Agent::sendFeedbackLinks();',
        '',
        'N',
        86400
    );
}
