<?php

use Ai\SeoAudit\Infrastructure\Api\ApiProviderRegistry;
use Ai\SeoAudit\Model\ProjectTable;
use Ai\SeoAudit\Model\TaskTable;
use Ai\SeoAudit\Model\TenantTable;
use Bitrix\Main\Loader;
use Bitrix\Main\Localization\Loc;

Loc::loadMessages(__FILE__);
require_once $_SERVER['DOCUMENT_ROOT'] . '/bitrix/modules/main/include/prolog_admin_before.php';

if (!Loader::includeModule('ai.seoaudit')) {
    require $_SERVER['DOCUMENT_ROOT'] . '/bitrix/modules/main/include/prolog_admin_after.php';
    echo 'Module ai.seoaudit is not installed';
    require $_SERVER['DOCUMENT_ROOT'] . '/bitrix/modules/main/include/epilog_admin.php';
    return;
}

$tenantCount = (int) TenantTable::getCount();
$projectCount = (int) ProjectTable::getCount();
$queuedTaskCount = (int) TaskTable::getCount(['=STATUS' => 'queued']);
$providers = array_map(
    static fn ($provider) => $provider->getCode() . ($provider->isConfigured() ? ' ✅' : ' ❌'),
    ApiProviderRegistry::searchProviders()
);

$APPLICATION->SetTitle(Loc::getMessage('AI_SEO_DASHBOARD_TITLE'));
require $_SERVER['DOCUMENT_ROOT'] . '/bitrix/modules/main/include/prolog_admin_after.php';
?>
<div class="adm-detail-content-wrap">
    <div class="adm-detail-content">
        <h2><?=htmlspecialcharsbx(Loc::getMessage('AI_SEO_DASHBOARD_TITLE'));?></h2>
        <p><?=htmlspecialcharsbx(Loc::getMessage('AI_SEO_DASHBOARD_STATS', ['#TENANTS#' => $tenantCount, '#PROJECTS#' => $projectCount, '#TASKS#' => $queuedTaskCount]));?></p>
        <p><?=htmlspecialcharsbx(Loc::getMessage('AI_SEO_DASHBOARD_PROVIDERS'));?>: <?=htmlspecialcharsbx(implode(', ', $providers));?></p>
    </div>
</div>
<?php require $_SERVER['DOCUMENT_ROOT'] . '/bitrix/modules/main/include/epilog_admin.php'; ?>
