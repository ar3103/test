<?php

declare(strict_types=1);

namespace Company\ReviewSync\Event;

use Company\ReviewSync\Config\Option;
use Company\ReviewSync\Repository\ReviewRepository;
use Company\ReviewSync\Service\AutoReplyService;

final class ReviewPublishHandler
{
    private static bool $isLocked = false;

    /** @param array<string, mixed> $fields */
    public static function onAfterIBlockElementUpdate(array &$fields): void
    {
        if (self::$isLocked || empty($fields['RESULT']) || (int) ($fields['ID'] ?? 0) <= 0) {
            return;
        }

        $iblockId = Option::getInt(Option::IBLOCK_ID);
        $elementId = (int) $fields['ID'];

        if ((int) ($fields['IBLOCK_ID'] ?? 0) !== $iblockId || ($fields['ACTIVE'] ?? 'N') !== 'Y') {
            return;
        }

        $repository = new ReviewRepository();
        if (!$repository->isAutoReplyEmpty($iblockId, $elementId)) {
            return;
        }

        $replyService = new AutoReplyService();

        self::$isLocked = true;
        $repository->setAutoReply($elementId, $iblockId, $replyService->generate());
        self::$isLocked = false;
    }
}
