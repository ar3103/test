<?php

namespace Ai\SeoAudit\Model;

use Bitrix\Main\Entity;
use Bitrix\Main\ORM\Data\DataManager;

final class SubscriptionTable extends DataManager
{
    public static function getTableName(): string
    {
        return 'b_ai_seo_subscription';
    }

    public static function getMap(): array
    {
        return [
            new Entity\IntegerField('ID', ['primary' => true, 'autocomplete' => true]),
            new Entity\IntegerField('TENANT_ID', ['required' => true]),
            new Entity\StringField('PLAN_CODE', ['required' => true]),
            new Entity\StringField('STATUS', ['required' => true]),
            new Entity\DatetimeField('STARTS_AT'),
            new Entity\DatetimeField('ENDS_AT'),
            new Entity\StringField('CONTRACT_REF'),
            new Entity\DatetimeField('CREATED_AT'),
            new Entity\DatetimeField('UPDATED_AT'),
        ];
    }
}
