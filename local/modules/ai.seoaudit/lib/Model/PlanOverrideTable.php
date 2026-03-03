<?php

namespace Ai\SeoAudit\Model;

use Bitrix\Main\Entity;
use Bitrix\Main\ORM\Data\DataManager;

final class PlanOverrideTable extends DataManager
{
    public static function getTableName(): string
    {
        return 'b_ai_seo_plan_override';
    }

    public static function getMap(): array
    {
        return [
            new Entity\IntegerField('ID', ['primary' => true, 'autocomplete' => true]),
            new Entity\IntegerField('TENANT_ID', ['required' => true]),
            new Entity\StringField('CODE', ['required' => true]),
            new Entity\StringField('VALUE', ['required' => true]),
            new Entity\DatetimeField('CREATED_AT'),
        ];
    }
}
