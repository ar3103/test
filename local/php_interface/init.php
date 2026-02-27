<?php

declare(strict_types=1);

use Bitrix\Main\Loader;
use Local\Mailing\Agents;
use Local\Mailing\Bootstrap;

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) {
    return;
}

require_once $_SERVER['DOCUMENT_ROOT'] . '/local/lib/Mailing/Bootstrap.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/local/lib/Mailing/ConfigResolver.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/local/lib/Mailing/ShortLinkService.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/local/lib/Mailing/MultiChannelCampaignService.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/local/lib/Mailing/ABTestingService.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/local/lib/Mailing/AnalyticsService.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/local/lib/Mailing/MailingAutomationService.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/local/lib/Mailing/Agents.php';

if (!Loader::includeModule('main')) {
    return;
}

Bootstrap::registerHandlers();
Agents::registerDefaultAgents();
