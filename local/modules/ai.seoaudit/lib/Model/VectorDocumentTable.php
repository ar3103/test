<?php

namespace Ai\SeoAudit\Model;

use Bitrix\Main\Entity;
use Bitrix\Main\ORM\Data\DataManager;

final class VectorDocumentTable extends DataManager
{
    public static function getTableName(): string
    {
        return 'b_ai_seo_vector_document';
    }

    public static function getMap(): array
    {
        return [
            new Entity\IntegerField('ID', ['primary' => true, 'autocomplete' => true]),
            new Entity\IntegerField('TENANT_ID', ['required' => true]),
            new Entity\IntegerField('PROJECT_ID', ['required' => true]),
            new Entity\StringField('URL', ['required' => true]),
            new Entity\TextField('CONTENT', ['required' => true]),
            new Entity\TextField('EMBEDDING', ['required' => true]),
            new Entity\DatetimeField('CREATED_AT'),
        ];
    }
}
