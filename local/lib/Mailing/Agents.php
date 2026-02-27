<?php

declare(strict_types=1);

namespace Local\Mailing;

use Bitrix\Main\Config\Option;

final class Agents
{
    private const MODULE_ID = 'local.mailing';

    public static function registerDefaultAgents(): void
    {
        if (Option::get(self::MODULE_ID, 'agents_registered', 'N') === 'Y') {
            return;
        }

        \CAgent::AddAgent('\\Local\\Mailing\\Agents::runWeeklyDigestAgent();', '', 'N', 604800);
        \CAgent::AddAgent('\\Local\\Mailing\\Agents::runInactiveUsersAgent();', '', 'N', 604800);
        \CAgent::AddAgent('\\Local\\Mailing\\Agents::runAbandonedCartAgent();', '', 'N', 86400);

        Option::set(self::MODULE_ID, 'agents_registered', 'Y');
    }

    public static function runWeeklyDigestAgent(): string
    {
        foreach (self::sites() as $siteId) {
            MailingAutomationService::runWeeklyBlogDigest($siteId);
        }

        return '\\Local\\Mailing\\Agents::runWeeklyDigestAgent();';
    }

    public static function runInactiveUsersAgent(): string
    {
        foreach (self::sites() as $siteId) {
            MailingAutomationService::runInactiveUsersCampaign($siteId);
        }

        return '\\Local\\Mailing\\Agents::runInactiveUsersAgent();';
    }

    public static function runAbandonedCartAgent(): string
    {
        foreach (self::sites() as $siteId) {
            MailingAutomationService::runAbandonedCartCampaign($siteId);
        }

        return '\\Local\\Mailing\\Agents::runAbandonedCartAgent();';
    }

    private static function sites(): array
    {
        return ['s1', 's2'];
    }
}
