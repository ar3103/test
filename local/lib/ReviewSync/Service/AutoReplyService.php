<?php

declare(strict_types=1);

namespace Company\ReviewSync\Service;

use Bitrix\Iblock\ElementPropertyTable;
use Bitrix\Main\Loader;
use CIBlockElement;
use Company\ReviewSync\Config;

final class AutoReplyService
{
    public function __construct()
    {
        Loader::includeModule('iblock');
    }

    public function ensureReply(int $elementId): void
    {
        $iblockId = Config::getReviewsIblockId();
        if ($iblockId <= 0 || $this->hasReply($elementId, $iblockId)) {
            return;
        }

        $variants = Config::getAutoReplyVariants();
        $reply = $variants ? $variants[array_rand($variants)] : Config::getDefaultAutoReply();

        CIBlockElement::SetPropertyValuesEx($elementId, $iblockId, [
            'COMPANY_REPLY' => $reply,
        ]);
    }

    private function hasReply(int $elementId, int $iblockId): bool
    {
        $value = ElementPropertyTable::getList([
            'select' => ['VALUE'],
            'filter' => [
                '=IBLOCK_ELEMENT_ID' => $elementId,
                '=IBLOCK_PROPERTY.IBLOCK_ID' => $iblockId,
                '=IBLOCK_PROPERTY.CODE' => 'COMPANY_REPLY',
            ],
            'limit' => 1,
        ])->fetch();

        return !empty($value['VALUE']);
    }
}
