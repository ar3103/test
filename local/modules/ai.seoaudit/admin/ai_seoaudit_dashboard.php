<?php

use Bitrix\Main\Loader;

require_once $_SERVER['DOCUMENT_ROOT'] . '/bitrix/modules/main/include/prolog_admin_before.php';

if (!Loader::includeModule('ai.seoaudit')) {
    require $_SERVER['DOCUMENT_ROOT'] . '/bitrix/modules/main/include/prolog_admin_after.php';
    echo 'Module ai.seoaudit is not installed';
    require $_SERVER['DOCUMENT_ROOT'] . '/bitrix/modules/main/include/epilog_admin.php';
    return;
}

$APPLICATION->SetTitle('AI SEO Audit Dashboard');
require $_SERVER['DOCUMENT_ROOT'] . '/bitrix/modules/main/include/prolog_admin_after.php';
?>
<div class="adm-detail-content-wrap">
    <div class="adm-detail-content">
        <h2>SEO Dashboard</h2>
        <p>Здесь отображаются KPI: позиции, SERP volatility, прогноз трафика и статус задач агентов.</p>
        <ul>
            <li>Трекинг позиций: готово к подключению очередей.</li>
            <li>Технический аудит: готово к интеграции с отчетами.</li>
            <li>AI блок: подключите OpenAI или локальную LLM в мастере настройки.</li>
        </ul>
    </div>
</div>
<?php require $_SERVER['DOCUMENT_ROOT'] . '/bitrix/modules/main/include/epilog_admin.php'; ?>
