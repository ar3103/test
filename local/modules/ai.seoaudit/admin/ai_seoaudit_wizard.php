<?php

use Bitrix\Main\Config\Option;
use Bitrix\Main\Loader;

require_once $_SERVER['DOCUMENT_ROOT'] . '/bitrix/modules/main/include/prolog_admin_before.php';

if (!Loader::includeModule('ai.seoaudit')) {
    require $_SERVER['DOCUMENT_ROOT'] . '/bitrix/modules/main/include/prolog_admin_after.php';
    echo 'Module ai.seoaudit is not installed';
    require $_SERVER['DOCUMENT_ROOT'] . '/bitrix/modules/main/include/epilog_admin.php';
    return;
}

$moduleId = 'ai.seoaudit';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && check_bitrix_sessid()) {
    Option::set($moduleId, 'yandex_token', (string) ($_POST['YANDEX_TOKEN'] ?? ''));
    Option::set($moduleId, 'gsc_credentials', (string) ($_POST['GSC_CREDENTIALS'] ?? ''));
    Option::set($moduleId, 'openai_key', (string) ($_POST['OPENAI_KEY'] ?? ''));
    Option::set($moduleId, 'local_llm_endpoint', (string) ($_POST['LOCAL_LLM_ENDPOINT'] ?? ''));
}

$APPLICATION->SetTitle('AI SEO Audit Setup Wizard');
require $_SERVER['DOCUMENT_ROOT'] . '/bitrix/modules/main/include/prolog_admin_after.php';
?>
<form method="post">
    <?php echo bitrix_sessid_post(); ?>
    <table class="adm-detail-content-table edit-table">
        <tr>
            <td width="40%">Yandex Webmaster Token</td>
            <td><input type="text" name="YANDEX_TOKEN" value="<?=htmlspecialcharsbx(Option::get($moduleId, 'yandex_token', ''));?>" size="60"></td>
        </tr>
        <tr>
            <td>Google Search Console Credentials (JSON)</td>
            <td><textarea name="GSC_CREDENTIALS" rows="4" cols="60"><?=htmlspecialcharsbx(Option::get($moduleId, 'gsc_credentials', ''));?></textarea></td>
        </tr>
        <tr>
            <td>OpenAI API Key</td>
            <td><input type="text" name="OPENAI_KEY" value="<?=htmlspecialcharsbx(Option::get($moduleId, 'openai_key', ''));?>" size="60"></td>
        </tr>
        <tr>
            <td>Local LLM Endpoint</td>
            <td><input type="text" name="LOCAL_LLM_ENDPOINT" value="<?=htmlspecialcharsbx(Option::get($moduleId, 'local_llm_endpoint', ''));?>" size="60"></td>
        </tr>
    </table>
    <input type="submit" value="Сохранить" class="adm-btn-save">
</form>
<?php require $_SERVER['DOCUMENT_ROOT'] . '/bitrix/modules/main/include/epilog_admin.php'; ?>
