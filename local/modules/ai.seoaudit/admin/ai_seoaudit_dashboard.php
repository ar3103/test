<?php

use Ai\SeoAudit\Infrastructure\Api\ApiProviderRegistry;
use Ai\SeoAudit\Model\ProjectTable;
use Ai\SeoAudit\Model\TaskTable;
use Ai\SeoAudit\Model\TenantTable;
use Bitrix\Main\Loader;

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
    static fn($provider) => $provider->getCode() . ($provider->isConfigured() ? ' ✅' : ' ❌'),
    ApiProviderRegistry::searchProviders()
);

$APPLICATION->SetTitle('AI SEO Audit Dashboard');
require $_SERVER['DOCUMENT_ROOT'] . '/bitrix/modules/main/include/prolog_admin_after.php';
?>
<div class="adm-detail-content-wrap">
    <div class="adm-detail-content">
        <h2>SEO Dashboard</h2>
        <p>Tenants: <b><?=$tenantCount;?></b> | Projects: <b><?=$projectCount;?></b> | Queued tasks: <b><?=$queuedTaskCount;?></b></p>
        <p>Search API провайдеры: <?=htmlspecialcharsbx(implode(', ', $providers));?></p>
        <ul>
            <li>REST интеграции Яндекс/GSC подключены.</li>
            <li>Генерация HTML/PDF SEO-отчетов доступна в разделе Reports & RAG.</li>
            <li>Векторная база знаний и RAG доступны в разделе Reports & RAG.</li>
        </ul>
    </div>
</div>
<?php require $_SERVER['DOCUMENT_ROOT'] . '/bitrix/modules/main/include/epilog_admin.php'; ?>
