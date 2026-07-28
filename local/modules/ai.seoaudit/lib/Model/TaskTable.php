<?php

namespace Ai\SeoAudit\Model;

use Bitrix\Main\Entity;
use Bitrix\Main\ORM\Data\DataManager;

final class TaskTable extends DataManager
{
    public static function getTableName(): string
    {
        return 'b_ai_seo_task';
    }

    public static function getMap(): array
    {
        return [
            new Entity\IntegerField('ID', ['primary' => true, 'autocomplete' => true]),
            new Entity\IntegerField('TENANT_ID', ['required' => true]),
            new Entity\IntegerField('PROJECT_ID', ['required' => true]),
            new Entity\StringField('TYPE', ['required' => true]),
            new Entity\StringField('STATUS', ['required' => true]),
            new Entity\TextField('PAYLOAD'),
            new Entity\TextField('LAST_ERROR'),
            new Entity\DatetimeField('NEXT_RUN_AT'),
            new Entity\DatetimeField('CREATED_AT'),
            new Entity\DatetimeField('UPDATED_AT'),
            new Entity\ReferenceField('PROJECT', ProjectTable::class, ['=this.PROJECT_ID' => 'ref.ID']),
        ];
    }
}
