<?php

namespace Local\AiConsultant;

use CIBlockElement;

class KnowledgeBase
{
    public function getContext(int $iblockId, string $question): string
    {
        if ($iblockId <= 0) {
            return '';
        }

        $context = [];
        $res = CIBlockElement::GetList(
            ['SORT' => 'ASC'],
            ['IBLOCK_ID' => $iblockId, 'ACTIVE' => 'Y'],
            false,
            ['nTopCount' => 15],
            ['ID', 'NAME', 'PREVIEW_TEXT', 'DETAIL_TEXT']
        );

        while ($item = $res->Fetch()) {
            $blob = trim((string)($item['NAME'] . ' ' . $item['PREVIEW_TEXT'] . ' ' . $item['DETAIL_TEXT']));
            if ($blob !== '') {
                $context[] = $blob;
            }
        }

        return implode("\n---\n", $context);
    }
}
