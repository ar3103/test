<?php

declare(strict_types=1);

namespace Company\ReviewSync\Infrastructure;

use Bitrix\Iblock\ElementTable;
use Bitrix\Main\Loader;
use CFile;
use CIBlockElement;
use Company\ReviewSync\Config;
use Company\ReviewSync\Domain\ReviewDto;

final class IblockReviewRepository
{
    public function __construct()
    {
        Loader::includeModule('iblock');
    }

    public function existsByExternalId(string $provider, string $externalId): bool
    {
        $iblockId = Config::getReviewsIblockId();
        if ($iblockId <= 0) {
            return true;
        }

        $code = sprintf('%s_%s', $provider, $externalId);

        return (bool)ElementTable::getList([
            'select' => ['ID'],
            'filter' => ['=IBLOCK_ID' => $iblockId, '=XML_ID' => $code],
            'limit' => 1,
        ])->fetch();
    }

    public function createOnModeration(ReviewDto $review): int
    {
        $iblockId = Config::getReviewsIblockId();
        if ($iblockId <= 0) {
            throw new \RuntimeException('Не задан ID инфоблока отзывов.');
        }

        $element = new CIBlockElement();
        $xmlId = sprintf('%s_%s', $review->provider, $review->externalId);

        $fields = [
            'IBLOCK_ID' => $iblockId,
            'XML_ID' => $xmlId,
            'NAME' => sprintf('%s (%d/5)', $review->authorName, $review->rating),
            'ACTIVE' => 'N',
            'PREVIEW_TEXT' => $review->text,
            'PROPERTY_VALUES' => [
                'AUTHOR_NAME' => $review->authorName,
                'POSITION' => $review->authorPosition,
                'RATING' => $review->rating,
                'SOURCE' => strtoupper($review->provider),
                'SOURCE_EXTERNAL_ID' => $review->externalId,
            ],
        ];

        if ($review->photoUrl !== '') {
            $fields['PROPERTY_VALUES']['PHOTO'] = CFile::MakeFileArray($review->photoUrl);
        }

        if ($review->attachmentUrl !== '') {
            $fields['PROPERTY_VALUES']['ATTACHMENT'] = CFile::MakeFileArray($review->attachmentUrl);
        }

        $elementId = (int)$element->Add($fields, false, false, true);
        if ($elementId <= 0) {
            throw new \RuntimeException((string)$element->LAST_ERROR);
        }

        return $elementId;
    }
}
