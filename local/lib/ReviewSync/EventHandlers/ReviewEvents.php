<?php

declare(strict_types=1);

namespace Company\ReviewSync\EventHandlers;

use Company\ReviewSync\Config;
use Company\ReviewSync\Service\AutoReplyService;

final class ReviewEvents
{
    public static function onAfterReviewPublished(array &$fields): void
    {
        $iblockId = Config::getReviewsIblockId();
        if ($iblockId <= 0) {
            return;
        }

        if ((int)($fields['IBLOCK_ID'] ?? 0) !== $iblockId) {
            return;
        }

        if (($fields['RESULT'] ?? false) !== true || ($fields['ACTIVE'] ?? 'N') !== 'Y') {
            return;
        }

        (new AutoReplyService())->ensureReply((int)$fields['ID']);
    }
}
