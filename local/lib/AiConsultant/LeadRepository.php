<?php

namespace Local\AiConsultant;

use CIBlockElement;

class LeadRepository
{
    public function findBySession(int $iblockId, string $sessionId): ?array
    {
        $res = CIBlockElement::GetList(
            ['ID' => 'DESC'],
            ['IBLOCK_ID' => $iblockId, 'PROPERTY_SESSION_ID' => $sessionId],
            false,
            ['nTopCount' => 1],
            ['ID', 'IBLOCK_ID', 'NAME', 'PROPERTY_HISTORY', 'PROPERTY_PHONE', 'PROPERTY_INTEREST', 'PROPERTY_CLIENT_NAME']
        );

        $item = $res->Fetch();

        return $item ?: null;
    }

    public function saveConversation(int $iblockId, string $siteId, string $sessionId, array $payload): int
    {
        $existing = $this->findBySession($iblockId, $sessionId);
        $history = json_encode($payload['history'] ?? [], JSON_UNESCAPED_UNICODE);

        $fields = [
            'IBLOCK_ID' => $iblockId,
            'NAME' => 'Диалог ' . date('d.m.Y H:i'),
            'ACTIVE' => 'Y',
            'PROPERTY_VALUES' => [
                'SITE_ID' => $siteId,
                'SESSION_ID' => $sessionId,
                'CLIENT_NAME' => $payload['name'] ?? '',
                'PHONE' => $payload['phone'] ?? '',
                'INTEREST' => $payload['interest'] ?? '',
                'HISTORY' => $history,
                'RATING' => $payload['rating'] ?? '',
            ],
        ];

        $element = new CIBlockElement();

        if ($existing) {
            $element->Update((int)$existing['ID'], $fields);
            CIBlockElement::SetPropertyValuesEx((int)$existing['ID'], $iblockId, $fields['PROPERTY_VALUES']);

            return (int)$existing['ID'];
        }

        return (int)$element->Add($fields);
    }
}
