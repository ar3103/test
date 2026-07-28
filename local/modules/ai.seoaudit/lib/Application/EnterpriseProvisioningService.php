<?php

namespace Ai\SeoAudit\Application;

use Ai\SeoAudit\Model\PlanOverrideTable;
use Ai\SeoAudit\Model\SubscriptionTable;
use Bitrix\Main\Type\DateTime;

final class EnterpriseProvisioningService
{
    public static function activate(int $tenantId, string $contractRef, array $overrides = []): void
    {
        SubscriptionTable::add([
            'TENANT_ID' => $tenantId,
            'PLAN_CODE' => 'enterprise',
            'STATUS' => 'active',
            'STARTS_AT' => new DateTime(),
            'CONTRACT_REF' => $contractRef,
            'CREATED_AT' => new DateTime(),
        ]);

        foreach ($overrides as $code => $value) {
            if ($code === '') {
                continue;
            }
            PlanOverrideTable::add([
                'TENANT_ID' => $tenantId,
                'CODE' => (string) $code,
                'VALUE' => (string) $value,
                'CREATED_AT' => new DateTime(),
            ]);
        }
    }

    public static function suspend(int $tenantId): void
    {
        $row = SubscriptionTable::getList([
            'filter' => ['=TENANT_ID' => $tenantId, '=STATUS' => 'active'],
            'order' => ['ID' => 'DESC'],
            'limit' => 1,
        ])->fetch();

        if (!$row) {
            return;
        }

        SubscriptionTable::update((int) $row['ID'], [
            'STATUS' => 'suspended',
            'ENDS_AT' => new DateTime(),
            'UPDATED_AT' => new DateTime(),
        ]);
    }
}
