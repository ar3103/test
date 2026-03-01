<?php

namespace Local\Mailing;

class ShortLinkManager
{
    public static function create(string $siteId, int $userId, string $targetUrl, array $config): string
    {
        $iblockId = (int)($config['SHORT_LINK']['IBLOCK_ID'] ?? 0);
        $prefix = (string)($config['SHORT_LINK']['CODE_PREFIX'] ?? 'lnk-');
        $code = $prefix . substr(md5($siteId . '|' . $userId . '|' . $targetUrl . '|' . microtime(true)), 0, 8);

        if ($iblockId > 0) {
            $element = new \CIBlockElement();
            $element->Add([
                'IBLOCK_ID' => $iblockId,
                'NAME' => 'Short link ' . $code,
                'CODE' => $code,
                'ACTIVE' => 'Y',
                'PROPERTY_VALUES' => [
                    'TARGET_URL' => $targetUrl,
                    'USER_ID' => $userId,
                    'SITE_ID' => $siteId,
                    'CLICKS' => 0,
                ],
            ]);
        }

        return 'https://' . $_SERVER['HTTP_HOST'] . '/r/' . $code;
    }

    public static function registerClick(string $siteId, string $code, array $config): void
    {
        $iblockId = (int)($config['SHORT_LINK']['IBLOCK_ID'] ?? 0);
        if ($iblockId <= 0 || $code === '') {
            return;
        }

        $res = \CIBlockElement::GetList([], ['IBLOCK_ID' => $iblockId, 'CODE' => $code], false, false, ['ID']);
        if ($item = $res->Fetch()) {
            $property = \CIBlockElement::GetProperty($iblockId, (int)$item['ID'], [], ['CODE' => 'CLICKS'])->Fetch();
            $count = (int)($property['VALUE'] ?? 0);
            \CIBlockElement::SetPropertyValuesEx((int)$item['ID'], $iblockId, ['CLICKS' => $count + 1]);

            Analytics::log($siteId, 'short_link_click', ['code' => $code, 'element_id' => (int)$item['ID']], $config);
        }
    }
}
