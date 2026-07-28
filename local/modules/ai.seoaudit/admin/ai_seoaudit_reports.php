<?php

use Ai\SeoAudit\Application\Rag\RagService;
use Ai\SeoAudit\Application\Rag\VectorStoreService;
use Ai\SeoAudit\Application\Reporting\ReportService;
use Ai\SeoAudit\Model\ReportTable;
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

$result = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST' && check_bitrix_sessid()) {
    $action = (string) ($_POST['action'] ?? '');

    if ($action === 'generate_report') {
        $tenantId = (int) ($_POST['tenant_id'] ?? 0);
        $projectId = (int) ($_POST['project_id'] ?? 0);
        $result = ReportService::generate($tenantId, $projectId, [
            'project_id' => $projectId,
            'generated_at' => date('c'),
            'positions' => 'demo',
            'serp_visibility' => 'demo',
        ]);
    }

    if ($action === 'index_doc') {
        VectorStoreService::upsertDocument(
            (int) ($_POST['tenant_id'] ?? 0),
            (int) ($_POST['project_id'] ?? 0),
            (string) ($_POST['doc_url'] ?? ''),
            (string) ($_POST['doc_content'] ?? '')
        );
    }

    if ($action === 'ask_rag') {
        $result = RagService::answer((int) ($_POST['project_id'] ?? 0), (string) ($_POST['question'] ?? ''));
    }
}

$reports = ReportTable::getList(['order' => ['ID' => 'DESC'], 'limit' => 20])->fetchAll();

$APPLICATION->SetTitle(Loc::getMessage('AI_SEO_REPORTS_TITLE'));
require $_SERVER['DOCUMENT_ROOT'] . '/bitrix/modules/main/include/prolog_admin_after.php';
?>
<h2><?=htmlspecialcharsbx(Loc::getMessage('AI_SEO_REPORTS_GENERATE'));?></h2>
<form method="post"><?php echo bitrix_sessid_post(); ?>
    <input type="hidden" name="action" value="generate_report">
    <input type="number" name="tenant_id" placeholder="<?=htmlspecialcharsbx(Loc::getMessage('AI_SEO_REPORTS_TENANT_ID'));?>">
    <input type="number" name="project_id" placeholder="<?=htmlspecialcharsbx(Loc::getMessage('AI_SEO_REPORTS_PROJECT_ID'));?>">
    <button class="adm-btn-save" type="submit"><?=htmlspecialcharsbx(Loc::getMessage('AI_SEO_REPORTS_GENERATE_BTN'));?></button>
</form>

<h2><?=htmlspecialcharsbx(Loc::getMessage('AI_SEO_REPORTS_INDEX'));?></h2>
<form method="post"><?php echo bitrix_sessid_post(); ?>
    <input type="hidden" name="action" value="index_doc">
    <input type="number" name="tenant_id" placeholder="<?=htmlspecialcharsbx(Loc::getMessage('AI_SEO_REPORTS_TENANT_ID'));?>">
    <input type="number" name="project_id" placeholder="<?=htmlspecialcharsbx(Loc::getMessage('AI_SEO_REPORTS_PROJECT_ID'));?>">
    <input type="text" name="doc_url" placeholder="https://example.com/page" size="60"><br><br>
    <textarea name="doc_content" rows="5" cols="100" placeholder="<?=htmlspecialcharsbx(Loc::getMessage('AI_SEO_REPORTS_DOC_PLACEHOLDER'));?>"></textarea><br>
    <button class="adm-btn-save" type="submit"><?=htmlspecialcharsbx(Loc::getMessage('AI_SEO_REPORTS_INDEX_BTN'));?></button>
</form>

<h2><?=htmlspecialcharsbx(Loc::getMessage('AI_SEO_REPORTS_RAG'));?></h2>
<form method="post"><?php echo bitrix_sessid_post(); ?>
    <input type="hidden" name="action" value="ask_rag">
    <input type="number" name="project_id" placeholder="<?=htmlspecialcharsbx(Loc::getMessage('AI_SEO_REPORTS_PROJECT_ID'));?>">
    <input type="text" name="question" placeholder="<?=htmlspecialcharsbx(Loc::getMessage('AI_SEO_REPORTS_QUESTION'));?>" size="80">
    <button class="adm-btn-save" type="submit"><?=htmlspecialcharsbx(Loc::getMessage('AI_SEO_REPORTS_ASK_BTN'));?></button>
</form>

<h2><?=htmlspecialcharsbx(Loc::getMessage('AI_SEO_REPORTS_RESULT'));?></h2>
<pre><?php echo htmlspecialcharsbx(print_r($result, true)); ?></pre>

<h2><?=htmlspecialcharsbx(Loc::getMessage('AI_SEO_REPORTS_LAST'));?></h2>
<pre><?php echo htmlspecialcharsbx(print_r($reports, true)); ?></pre>
<?php require $_SERVER['DOCUMENT_ROOT'] . '/bitrix/modules/main/include/epilog_admin.php'; ?>
