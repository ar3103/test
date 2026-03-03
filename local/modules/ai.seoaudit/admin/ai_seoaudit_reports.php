<?php

use Ai\SeoAudit\Application\Rag\RagService;
use Ai\SeoAudit\Application\Rag\VectorStoreService;
use Ai\SeoAudit\Application\Reporting\ReportService;
use Ai\SeoAudit\Model\ReportTable;
use Bitrix\Main\Loader;

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

$APPLICATION->SetTitle('Reports + RAG Knowledge Base');
require $_SERVER['DOCUMENT_ROOT'] . '/bitrix/modules/main/include/prolog_admin_after.php';
?>
<h2>Генерация SEO-отчета (HTML/PDF)</h2>
<form method="post"><?php echo bitrix_sessid_post(); ?>
    <input type="hidden" name="action" value="generate_report">
    <input type="number" name="tenant_id" placeholder="Tenant ID">
    <input type="number" name="project_id" placeholder="Project ID">
    <button class="adm-btn-save" type="submit">Сгенерировать</button>
</form>

<h2>Индексация документа в Vector Store</h2>
<form method="post"><?php echo bitrix_sessid_post(); ?>
    <input type="hidden" name="action" value="index_doc">
    <input type="number" name="tenant_id" placeholder="Tenant ID">
    <input type="number" name="project_id" placeholder="Project ID">
    <input type="text" name="doc_url" placeholder="https://example.com/page" size="60"><br><br>
    <textarea name="doc_content" rows="5" cols="100" placeholder="Текст страницы"></textarea><br>
    <button class="adm-btn-save" type="submit">Индексировать</button>
</form>

<h2>RAG-вопрос по базе знаний</h2>
<form method="post"><?php echo bitrix_sessid_post(); ?>
    <input type="hidden" name="action" value="ask_rag">
    <input type="number" name="project_id" placeholder="Project ID">
    <input type="text" name="question" placeholder="Как улучшить кластеризацию?" size="80">
    <button class="adm-btn-save" type="submit">Спросить</button>
</form>

<h2>Результат</h2>
<pre><?php echo htmlspecialcharsbx(print_r($result, true)); ?></pre>

<h2>Последние отчеты</h2>
<pre><?php echo htmlspecialcharsbx(print_r($reports, true)); ?></pre>
<?php require $_SERVER['DOCUMENT_ROOT'] . '/bitrix/modules/main/include/epilog_admin.php'; ?>
