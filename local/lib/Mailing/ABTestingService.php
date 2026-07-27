<?php

declare(strict_types=1);

namespace Local\Mailing;

final class ABTestingService
{
    public static function pickVariant(int $contactId, array $variants): array
    {
        if (count($variants) < 2) {
            return $variants[0] ?? ['subject' => '', 'body' => ''];
        }

        return ($contactId % 2 === 0) ? $variants[0] : $variants[1];
    }
}
