<?php

namespace Ai\SeoAudit\Model;

use Bitrix\Main\Entity;
use Bitrix\Main\ORM\Data\DataManager;

final class ApiCredentialTable extends DataManager
{
    public static function getTableName(): string
    {
        return 'b_ai_seo_api_credential';
    }

    public static function getMap(): array
    {
        return [
            new Entity\IntegerField('ID', ['primary' => true, 'autocomplete' => true]),
            new Entity\IntegerField('TENANT_ID', ['required' => true]),
            new Entity\StringField('PROVIDER', ['required' => true]),
            new Entity\TextField('CREDENTIALS', ['required' => true]),
            new Entity\StringField('ACTIVE', ['default_value' => 'Y']),
            new Entity\DatetimeField('CREATED_AT'),
            new Entity\ReferenceField('TENANT', TenantTable::class, ['=this.TENANT_ID' => 'ref.ID']),
        ];
    }
}
