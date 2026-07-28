<?php

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

$APPLICATION->SetTitle(Loc::getMessage('AI_SEO_MARKETPLACE_TITLE'));
require $_SERVER['DOCUMENT_ROOT'] . '/bitrix/modules/main/include/prolog_admin_after.php';
?>
<div class="adm-detail-content-wrap">
    <div class="adm-detail-content">
        <h2><?=htmlspecialcharsbx(Loc::getMessage('AI_SEO_MARKETPLACE_MAIN_PLANS'));?></h2>
        <table class="adm-list-table">
            <tr class="adm-list-table-header">
                <td><?=htmlspecialcharsbx(Loc::getMessage('AI_SEO_MARKETPLACE_PLAN'));?></td>
                <td><?=htmlspecialcharsbx(Loc::getMessage('AI_SEO_MARKETPLACE_PRICE'));?></td>
                <td><?=htmlspecialcharsbx(Loc::getMessage('AI_SEO_MARKETPLACE_VALUE'));?></td>
            </tr>
            <tr><td>Start</td><td>4 900 ₽/мес</td><td><?=htmlspecialcharsbx(Loc::getMessage('AI_SEO_MARKETPLACE_START'));?></td></tr>
            <tr><td>Pro</td><td>14 900 ₽/мес</td><td><?=htmlspecialcharsbx(Loc::getMessage('AI_SEO_MARKETPLACE_PRO'));?></td></tr>
            <tr><td>Agency</td><td>39 900 ₽/мес</td><td><?=htmlspecialcharsbx(Loc::getMessage('AI_SEO_MARKETPLACE_AGENCY'));?></td></tr>
            <tr><td>Enterprise</td><td><?=htmlspecialcharsbx(Loc::getMessage('AI_SEO_MARKETPLACE_REQUEST'));?></td><td><?=htmlspecialcharsbx(Loc::getMessage('AI_SEO_MARKETPLACE_ENTERPRISE'));?></td></tr>
        </table>

        <h3><?=htmlspecialcharsbx(Loc::getMessage('AI_SEO_MARKETPLACE_TRIAL'));?></h3>
        <p><?=htmlspecialcharsbx(Loc::getMessage('AI_SEO_MARKETPLACE_TRIAL_DESC'));?></p>

        <h3><?=htmlspecialcharsbx(Loc::getMessage('AI_SEO_MARKETPLACE_AI_VALUE'));?></h3>
        <ul>
            <li><?=htmlspecialcharsbx(Loc::getMessage('AI_SEO_MARKETPLACE_AI_FORECAST'));?></li>
            <li><?=htmlspecialcharsbx(Loc::getMessage('AI_SEO_MARKETPLACE_AI_AUTOMATION'));?></li>
            <li><?=htmlspecialcharsbx(Loc::getMessage('AI_SEO_MARKETPLACE_AI_RAG'));?></li>
        </ul>

        <h3><?=htmlspecialcharsbx(Loc::getMessage('AI_SEO_MARKETPLACE_AGENCY_BLOCK'));?></h3>
        <p><?=htmlspecialcharsbx(Loc::getMessage('AI_SEO_MARKETPLACE_AGENCY_DESC'));?></p>

        <h3><?=htmlspecialcharsbx(Loc::getMessage('AI_SEO_MARKETPLACE_CALC_TITLE'));?></h3>
        <p><?=htmlspecialcharsbx(Loc::getMessage('AI_SEO_MARKETPLACE_CALC_DESC'));?></p>
        <div style="display:flex;gap:12px;flex-wrap:wrap;align-items:end;">
            <div>
                <label><?=htmlspecialcharsbx(Loc::getMessage('AI_SEO_MARKETPLACE_CALC_TRAFFIC'));?></label><br>
                <input id="calc-traffic" type="number" value="10000">
            </div>
            <div>
                <label><?=htmlspecialcharsbx(Loc::getMessage('AI_SEO_MARKETPLACE_CALC_GROWTH'));?></label><br>
                <input id="calc-growth" type="number" value="20">
            </div>
            <div>
                <label><?=htmlspecialcharsbx(Loc::getMessage('AI_SEO_MARKETPLACE_CALC_CPC'));?></label><br>
                <input id="calc-cpc" type="number" value="35">
            </div>
            <div>
                <button class="adm-btn-save" onclick="calcBenefit()" type="button"><?=htmlspecialcharsbx(Loc::getMessage('AI_SEO_MARKETPLACE_CALC_BTN'));?></button>
            </div>
        </div>
        <p id="calc-result" style="margin-top:10px;font-weight:bold;"></p>
    </div>
</div>
<script>
function calcBenefit() {
    const traffic = Number(document.getElementById('calc-traffic').value || 0);
    const growth = Number(document.getElementById('calc-growth').value || 0) / 100;
    const cpc = Number(document.getElementById('calc-cpc').value || 0);
    const added = Math.round(traffic * growth);
    const savings = Math.round(added * cpc);
    document.getElementById('calc-result').innerText = '<?=CUtil::JSEscape(Loc::getMessage('AI_SEO_MARKETPLACE_CALC_RESULT'));?>'
        .replace('#ADDED#', added.toLocaleString('ru-RU'))
        .replace('#SAVINGS#', savings.toLocaleString('ru-RU'));
}
</script>
<?php require $_SERVER['DOCUMENT_ROOT'] . '/bitrix/modules/main/include/epilog_admin.php'; ?>
