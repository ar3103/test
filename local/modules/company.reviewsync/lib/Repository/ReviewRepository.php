<?php

declare(strict_types=1);

namespace Company\ReviewSync\Repository;

use Bitrix\Main\Loader;
use Company\ReviewSync\Config\Option;
use Company\ReviewSync\DTO\ReviewItem;

final class ReviewRepository
{
    public function __construct()
    {
        Loader::includeModule('iblock');
    }

    public function existsByXmlId(int $iblockId, string $xmlId): bool
    {
        $res = \CIBlockElement::GetList([], ['IBLOCK_ID' => $iblockId, '=XML_ID' => $xmlId], false, ['nTopCount' => 1], ['ID']);
        return (bool) $res->Fetch();
    }

    public function add(int $iblockId, ReviewItem $item): int
    {
        $el = new \CIBlockElement();
        $active = Option::getBool(Option::MODERATION_ACTIVE, true) ? 'N' : 'Y';

        $fields = [
            'IBLOCK_ID' => $iblockId,
            'NAME' => mb_substr($item->authorName . ': ' . $item->text, 0, 120),
            'ACTIVE' => $active,
            'XML_ID' => $item->getUniqueXmlId(),
            'PREVIEW_TEXT' => $item->text,
            'PROPERTY_VALUES' => [
                'AUTHOR_NAME' => $item->authorName,
                'AUTHOR_ROLE' => $item->authorRole,
                'RATING' => $item->rating,
                'PLATFORM' => $item->platform,
                'SOURCE_DATE' => $item->publishedAt->format('d.m.Y H:i:s'),
            ],
        ];

        if ($item->authorPhotoUrl) {
            $fields['PROPERTY_VALUES']['AUTHOR_PHOTO'] = \CFile::MakeFileArray($item->authorPhotoUrl);
        }

        if ($item->attachmentUrl) {
            $fields['PROPERTY_VALUES']['ATTACHMENT'] = \CFile::MakeFileArray($item->attachmentUrl);
        }

        $id = (int) $el->Add($fields, false, true, true);
        if ($id <= 0) {
            throw new \RuntimeException((string) $el->LAST_ERROR);
        }

        return $id;
    }

    public function isAutoReplyEmpty(int $iblockId, int $elementId): bool
    {
        $prop = \CIBlockElement::GetProperty($iblockId, $elementId, ['sort' => 'asc'], ['CODE' => 'COMPANY_REPLY'])->Fetch();
        return empty($prop['VALUE']);
    }

    public function setAutoReply(int $elementId, int $iblockId, string $reply): void
    {
        \CIBlockElement::SetPropertyValuesEx($elementId, $iblockId, ['COMPANY_REPLY' => $reply]);
    }
}
