<?php

declare(strict_types=1);

use Local\Mailing\Agents;

require_once $_SERVER['DOCUMENT_ROOT'] . '/local/lib/Mailing/Agents.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/local/lib/Mailing/MailingAutomationService.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/local/lib/Mailing/ConfigResolver.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/local/lib/Mailing/ShortLinkService.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/local/lib/Mailing/ABTestingService.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/local/lib/Mailing/MultiChannelCampaignService.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/local/lib/Mailing/AnalyticsService.php';

return [
    'weekly_digest' => [Agents::class, 'runWeeklyDigestAgent'],
    'inactive_users' => [Agents::class, 'runInactiveUsersAgent'],
    'abandoned_cart' => [Agents::class, 'runAbandonedCartAgent'],
];
