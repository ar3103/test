<?php

namespace Ai\SeoAudit\Model;

use Bitrix\Main\Entity;
use Bitrix\Main\ORM\Data\DataManager;

final class ProjectTable extends DataManager
{
    public static function getTableName(): string
    {
        return 'b_ai_seo_project';
    }

    public static function getMap(): array
    {
        return [
            new Entity\IntegerField('ID', ['primary' => true, 'autocomplete' => true]),
            new Entity\IntegerField('TENANT_ID', ['required' => true]),
            new Entity\StringField('DOMAIN', ['required' => true]),
            new Entity\StringField('NAME', ['required' => true]),
            new Entity\StringField('ACTIVE', ['default_value' => 'Y']),
            new Entity\DatetimeField('CREATED_AT'),
            new Entity\ReferenceField('TENANT', TenantTable::class, ['=this.TENANT_ID' => 'ref.ID']),
        ];
    }
}
