<?php

namespace Ai\SeoAudit\Model;

use Bitrix\Main\Entity;
use Bitrix\Main\ORM\Data\DataManager;

final class ReportTable extends DataManager
{
    public static function getTableName(): string
    {
        return 'b_ai_seo_report';
    }

    public static function getMap(): array
    {
        return [
            new Entity\IntegerField('ID', ['primary' => true, 'autocomplete' => true]),
            new Entity\IntegerField('TENANT_ID', ['required' => true]),
            new Entity\IntegerField('PROJECT_ID', ['required' => true]),
            new Entity\StringField('TYPE', ['required' => true]),
            new Entity\StringField('FORMAT', ['required' => true]),
            new Entity\StringField('PATH', ['required' => true]),
            new Entity\DatetimeField('CREATED_AT'),
        ];
    }
}
