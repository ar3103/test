<?php

use Bitrix\Main\Loader;
use Local\Mailing\Agent;
use Local\Mailing\FormHandler;

if (!Loader::includeModule('main')) {
    return;
}

Loader::registerAutoLoadClasses(null, [
    'Local\\Mailing\\Agent' => '/local/lib/Mailing/Agent.php',
    'Local\\Mailing\\Analytics' => '/local/lib/Mailing/Analytics.php',
    'Local\\Mailing\\BlogSender' => '/local/lib/Mailing/BlogSender.php',
    'Local\\Mailing\\ChannelDispatcher' => '/local/lib/Mailing/ChannelDispatcher.php',
    'Local\\Mailing\\FormHandler' => '/local/lib/Mailing/FormHandler.php',
    'Local\\Mailing\\ShortLinkManager' => '/local/lib/Mailing/ShortLinkManager.php',
    'Local\\Mailing\\Triggers' => '/local/lib/Mailing/Triggers.php',
    'Local\\Mailing\\UserMailer' => '/local/lib/Mailing/UserMailer.php',
]);

AddEventHandler('iblock', 'OnAfterIBlockElementAdd', [FormHandler::class, 'onFormAdd']);
AddEventHandler('main', 'OnAfterUserAdd', [FormHandler::class, 'onAfterUserAdd']);

if (Loader::includeModule('sender')) {
    if (!\CAgent::GetList([], ['NAME' => Agent::class . '::weeklyBlogEmail();'])->Fetch()) {
        \CAgent::AddAgent(Agent::class . '::weeklyBlogEmail();', 'main', 'N', 604800);
    }

    if (!\CAgent::GetList([], ['NAME' => Agent::class . '::triggeredCampaigns();'])->Fetch()) {
        \CAgent::AddAgent(Agent::class . '::triggeredCampaigns();', 'main', 'N', 86400);
    }

    if (!\CAgent::GetList([], ['NAME' => Agent::class . '::holidayAndReviewLinks();'])->Fetch()) {
        \CAgent::AddAgent(Agent::class . '::holidayAndReviewLinks();', 'main', 'N', 86400);
    }
}
