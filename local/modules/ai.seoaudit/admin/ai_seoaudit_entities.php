<?php

use Ai\SeoAudit\Model\ProjectTable;
use Ai\SeoAudit\Model\TaskTable;
use Ai\SeoAudit\Model\TenantTable;
use Bitrix\Main\Loader;
use Bitrix\Main\Localization\Loc;
use Bitrix\Main\Type\DateTime;

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

    if ($action === 'add_tenant') {
        TenantTable::add([
            'NAME' => (string) ($_POST['tenant_name'] ?? ''),
            'CODE' => (string) ($_POST['tenant_code'] ?? ''),
            'ACTIVE' => 'Y',
            'CREATED_AT' => new DateTime(),
        ]);
    }

    if ($action === 'add_project') {
        ProjectTable::add([
            'TENANT_ID' => (int) ($_POST['project_tenant_id'] ?? 0),
            'DOMAIN' => (string) ($_POST['project_domain'] ?? ''),
            'NAME' => (string) ($_POST['project_name'] ?? ''),
            'ACTIVE' => 'Y',
            'CREATED_AT' => new DateTime(),
        ]);
    }

    if ($action === 'add_task') {
        TaskTable::add([
            'TENANT_ID' => (int) ($_POST['task_tenant_id'] ?? 0),
            'PROJECT_ID' => (int) ($_POST['task_project_id'] ?? 0),
            'TYPE' => (string) ($_POST['task_type'] ?? 'seo_audit'),
            'STATUS' => 'queued',
            'PAYLOAD' => (string) ($_POST['task_payload'] ?? '{}'),
            'NEXT_RUN_AT' => new DateTime(),
            'CREATED_AT' => new DateTime(),
        ]);
    }
}

$tenants = TenantTable::getList(['order' => ['ID' => 'DESC'], 'limit' => 20])->fetchAll();
$projects = ProjectTable::getList(['order' => ['ID' => 'DESC'], 'limit' => 20])->fetchAll();
$tasks = TaskTable::getList(['order' => ['ID' => 'DESC'], 'limit' => 20])->fetchAll();

$APPLICATION->SetTitle(Loc::getMessage('AI_SEO_ENTITIES_TITLE'));
require $_SERVER['DOCUMENT_ROOT'] . '/bitrix/modules/main/include/prolog_admin_after.php';
?>
<h2><?=htmlspecialcharsbx(Loc::getMessage('AI_SEO_ENTITIES_ADD_TENANT'));?></h2>
<form method="post"><?php echo bitrix_sessid_post(); ?>
    <input type="hidden" name="action" value="add_tenant">
    <input type="text" name="tenant_name" placeholder="<?=htmlspecialcharsbx(Loc::getMessage('AI_SEO_ENTITIES_TENANT_NAME'));?>">
    <input type="text" name="tenant_code" placeholder="<?=htmlspecialcharsbx(Loc::getMessage('AI_SEO_ENTITIES_TENANT_CODE'));?>">
    <button class="adm-btn-save" type="submit"><?=htmlspecialcharsbx(Loc::getMessage('AI_SEO_COMMON_ADD'));?></button>
</form>

<h2><?=htmlspecialcharsbx(Loc::getMessage('AI_SEO_ENTITIES_ADD_PROJECT'));?></h2>
<form method="post"><?php echo bitrix_sessid_post(); ?>
    <input type="hidden" name="action" value="add_project">
    <input type="number" name="project_tenant_id" placeholder="<?=htmlspecialcharsbx(Loc::getMessage('AI_SEO_ENTITIES_TENANT_ID'));?>">
    <input type="text" name="project_name" placeholder="<?=htmlspecialcharsbx(Loc::getMessage('AI_SEO_ENTITIES_PROJECT_NAME'));?>">
    <input type="text" name="project_domain" placeholder="example.com">
    <button class="adm-btn-save" type="submit"><?=htmlspecialcharsbx(Loc::getMessage('AI_SEO_COMMON_ADD'));?></button>
</form>

<h2><?=htmlspecialcharsbx(Loc::getMessage('AI_SEO_ENTITIES_ADD_TASK'));?></h2>
<form method="post"><?php echo bitrix_sessid_post(); ?>
    <input type="hidden" name="action" value="add_task">
    <input type="number" name="task_tenant_id" placeholder="<?=htmlspecialcharsbx(Loc::getMessage('AI_SEO_ENTITIES_TENANT_ID'));?>">
    <input type="number" name="task_project_id" placeholder="<?=htmlspecialcharsbx(Loc::getMessage('AI_SEO_ENTITIES_PROJECT_ID'));?>">
    <input type="text" name="task_type" placeholder="serp_sync">
    <input type="text" name="task_payload" placeholder='{"keyword":"seo"}'>
    <button class="adm-btn-save" type="submit"><?=htmlspecialcharsbx(Loc::getMessage('AI_SEO_COMMON_ADD'));?></button>
</form>

<h2><?=htmlspecialcharsbx(Loc::getMessage('AI_SEO_ENTITIES_TENANTS'));?></h2>
<pre><?php echo htmlspecialcharsbx(print_r($tenants, true)); ?></pre>

<h2><?=htmlspecialcharsbx(Loc::getMessage('AI_SEO_ENTITIES_PROJECTS'));?></h2>
<pre><?php echo htmlspecialcharsbx(print_r($projects, true)); ?></pre>

<h2><?=htmlspecialcharsbx(Loc::getMessage('AI_SEO_ENTITIES_TASKS'));?></h2>
<pre><?php echo htmlspecialcharsbx(print_r($tasks, true)); ?></pre>
<?php require $_SERVER['DOCUMENT_ROOT'] . '/bitrix/modules/main/include/epilog_admin.php'; ?>
