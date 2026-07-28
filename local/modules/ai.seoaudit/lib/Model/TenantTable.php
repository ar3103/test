<?php

namespace Ai\SeoAudit\Model;

use Bitrix\Main\Entity;
use Bitrix\Main\ORM\Data\DataManager;

final class TenantTable extends DataManager
{
    public static function getTableName(): string
    {
        return 'b_ai_seo_tenant';
    }

    public static function getMap(): array
    {
        return [
            new Entity\IntegerField('ID', ['primary' => true, 'autocomplete' => true]),
            new Entity\StringField('NAME', ['required' => true]),
            new Entity\StringField('CODE', ['required' => true]),
            new Entity\StringField('ACTIVE', ['default_value' => 'Y']),
            new Entity\DatetimeField('CREATED_AT'),
        ];
    }
}
