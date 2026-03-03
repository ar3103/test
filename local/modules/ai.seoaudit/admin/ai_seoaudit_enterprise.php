<?php

use Ai\SeoAudit\Application\EnterpriseProvisioningService;
use Ai\SeoAudit\Model\SubscriptionTable;
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

if ($_SERVER['REQUEST_METHOD'] === 'POST' && check_bitrix_sessid()) {
    $action = (string) ($_POST['action'] ?? '');
    $tenantId = (int) ($_POST['tenant_id'] ?? 0);

    if ($action === 'activate' && $tenantId > 0) {
        EnterpriseProvisioningService::activate($tenantId, (string) ($_POST['contract_ref'] ?? ''), [
            'projects' => (string) ($_POST['override_projects'] ?? 'unlimited'),
            'keywords_per_project' => (string) ($_POST['override_keywords'] ?? 'unlimited'),
        ]);
    }

    if ($action === 'suspend' && $tenantId > 0) {
        EnterpriseProvisioningService::suspend($tenantId);
    }
}

$subscriptions = SubscriptionTable::getList(['order' => ['ID' => 'DESC'], 'limit' => 50])->fetchAll();

$APPLICATION->SetTitle(Loc::getMessage('AI_SEO_ENTERPRISE_TITLE'));
require $_SERVER['DOCUMENT_ROOT'] . '/bitrix/modules/main/include/prolog_admin_after.php';
?>
<h2><?=htmlspecialcharsbx(Loc::getMessage('AI_SEO_ENTERPRISE_ACTIVATE'));?></h2>
<form method="post"><?php echo bitrix_sessid_post(); ?>
    <input type="hidden" name="action" value="activate">
    <input type="number" name="tenant_id" placeholder="Tenant ID">
    <input type="text" name="contract_ref" placeholder="Contract ref">
    <input type="text" name="override_projects" placeholder="Projects limit (e.g. unlimited)">
    <input type="text" name="override_keywords" placeholder="Keywords/project limit">
    <button class="adm-btn-save" type="submit"><?=htmlspecialcharsbx(Loc::getMessage('AI_SEO_ENTERPRISE_ACTIVATE_BTN'));?></button>
</form>

<h2><?=htmlspecialcharsbx(Loc::getMessage('AI_SEO_ENTERPRISE_SUSPEND'));?></h2>
<form method="post"><?php echo bitrix_sessid_post(); ?>
    <input type="hidden" name="action" value="suspend">
    <input type="number" name="tenant_id" placeholder="Tenant ID">
    <button class="adm-btn" type="submit"><?=htmlspecialcharsbx(Loc::getMessage('AI_SEO_ENTERPRISE_SUSPEND_BTN'));?></button>
</form>

<h2><?=htmlspecialcharsbx(Loc::getMessage('AI_SEO_ENTERPRISE_LIST'));?></h2>
<pre><?php echo htmlspecialcharsbx(print_r($subscriptions, true)); ?></pre>
<?php require $_SERVER['DOCUMENT_ROOT'] . '/bitrix/modules/main/include/epilog_admin.php'; ?>
