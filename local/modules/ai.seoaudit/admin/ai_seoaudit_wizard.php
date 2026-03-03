<?php

use Bitrix\Main\Config\Option;
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

$moduleId = 'ai.seoaudit';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && check_bitrix_sessid()) {
    $fields = [
        'oauth_yandex_client_id',
        'oauth_yandex_client_secret',
        'oauth_yandex_refresh_token',
        'oauth_yandex_user_id',
        'oauth_gsc_client_id',
        'oauth_gsc_client_secret',
        'oauth_gsc_refresh_token',
        'openai_key',
        'openai_embedding_model',
        'embedding_provider',
        'pdf_engine',
        'wkhtmltopdf_binary',
    ];

    foreach ($fields as $field) {
        Option::set($moduleId, $field, (string) ($_POST[$field] ?? ''));
    }
}

$APPLICATION->SetTitle(Loc::getMessage('AI_SEO_WIZARD_TITLE'));
require $_SERVER['DOCUMENT_ROOT'] . '/bitrix/modules/main/include/prolog_admin_after.php';
?>
<form method="post">
    <?php echo bitrix_sessid_post(); ?>
    <table class="adm-detail-content-table edit-table">
        <tr><td colspan="2"><b><?=htmlspecialcharsbx(Loc::getMessage('AI_SEO_WIZARD_YANDEX'));?></b></td></tr>
        <tr><td>Client ID</td><td><input type="text" name="oauth_yandex_client_id" value="<?=htmlspecialcharsbx(Option::get($moduleId, 'oauth_yandex_client_id', ''));?>" size="60"></td></tr>
        <tr><td>Client Secret</td><td><input type="text" name="oauth_yandex_client_secret" value="<?=htmlspecialcharsbx(Option::get($moduleId, 'oauth_yandex_client_secret', ''));?>" size="60"></td></tr>
        <tr><td>Refresh Token</td><td><input type="text" name="oauth_yandex_refresh_token" value="<?=htmlspecialcharsbx(Option::get($moduleId, 'oauth_yandex_refresh_token', ''));?>" size="60"></td></tr>
        <tr><td>User ID</td><td><input type="text" name="oauth_yandex_user_id" value="<?=htmlspecialcharsbx(Option::get($moduleId, 'oauth_yandex_user_id', ''));?>" size="60"></td></tr>

        <tr><td colspan="2"><b><?=htmlspecialcharsbx(Loc::getMessage('AI_SEO_WIZARD_GSC'));?></b></td></tr>
        <tr><td>Client ID</td><td><input type="text" name="oauth_gsc_client_id" value="<?=htmlspecialcharsbx(Option::get($moduleId, 'oauth_gsc_client_id', ''));?>" size="60"></td></tr>
        <tr><td>Client Secret</td><td><input type="text" name="oauth_gsc_client_secret" value="<?=htmlspecialcharsbx(Option::get($moduleId, 'oauth_gsc_client_secret', ''));?>" size="60"></td></tr>
        <tr><td>Refresh Token</td><td><input type="text" name="oauth_gsc_refresh_token" value="<?=htmlspecialcharsbx(Option::get($moduleId, 'oauth_gsc_refresh_token', ''));?>" size="60"></td></tr>

        <tr><td colspan="2"><b>AI</b></td></tr>
        <tr><td>OpenAI API Key</td><td><input type="text" name="openai_key" value="<?=htmlspecialcharsbx(Option::get($moduleId, 'openai_key', ''));?>" size="60"></td></tr>
        <tr><td>Embedding Provider (openai/hash)</td><td><input type="text" name="embedding_provider" value="<?=htmlspecialcharsbx(Option::get($moduleId, 'embedding_provider', 'openai'));?>" size="60"></td></tr>
        <tr><td>OpenAI Embedding Model</td><td><input type="text" name="openai_embedding_model" value="<?=htmlspecialcharsbx(Option::get($moduleId, 'openai_embedding_model', 'text-embedding-3-small'));?>" size="60"></td></tr>

        <tr><td colspan="2"><b>PDF</b></td></tr>
        <tr><td>PDF engine (wkhtmltopdf/dompdf/tcpdf)</td><td><input type="text" name="pdf_engine" value="<?=htmlspecialcharsbx(Option::get($moduleId, 'pdf_engine', 'wkhtmltopdf'));?>" size="60"></td></tr>
        <tr><td>wkhtmltopdf binary</td><td><input type="text" name="wkhtmltopdf_binary" value="<?=htmlspecialcharsbx(Option::get($moduleId, 'wkhtmltopdf_binary', 'wkhtmltopdf'));?>" size="60"></td></tr>
    </table>
    <input type="submit" value="<?=htmlspecialcharsbx(Loc::getMessage('AI_SEO_WIZARD_SAVE'));?>" class="adm-btn-save">
</form>
<?php require $_SERVER['DOCUMENT_ROOT'] . '/bitrix/modules/main/include/epilog_admin.php'; ?>
