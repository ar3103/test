<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) {
    die();
}

$promoTypesJson = htmlspecialcharsbx(json_encode($arResult['PROMO_TYPES'], JSON_UNESCAPED_UNICODE));
$this->addExternalCss($templateFolder . '/style.css');
$this->addExternalJs($templateFolder . '/script.js');
?>
<div class="promo-calculator" data-promo-types="<?= $promoTypesJson ?>" data-management-percent="<?= (float)$arResult['MANAGEMENT_PERCENT'] ?>">
    <form class="promo-calculator__form" method="post">
        <?= bitrix_sessid_post() ?>
        <input type="hidden" name="promo_calculator_action" value="submit">

        <label>Тип промо акции
            <select name="promo_type" required>
                <option value="">Выберите тип</option>
                <?php foreach ($arResult['PROMO_TYPES'] as $type => $rate): ?>
                    <option value="<?= htmlspecialcharsbx($type) ?>"><?= htmlspecialcharsbx($type) ?> (<?= (float)$rate ?> руб./день)</option>
                <?php endforeach; ?>
            </select>
        </label>

        <label>Требуемый персонал
            <select name="required_staff" required>
                <option value="">Выберите персонал</option>
                <?php foreach ($arResult['REQUIRED_STAFF_OPTIONS'] as $staffOption): ?>
                    <option value="<?= htmlspecialcharsbx($staffOption) ?>"><?= htmlspecialcharsbx($staffOption) ?></option>
                <?php endforeach; ?>
            </select>
        </label>

        <label>Количество человек
            <input type="number" name="people_count" min="1" value="1" required>
        </label>

        <label>Количество часов
            <input type="number" name="hours_count" min="1" value="1" required>
        </label>

        <label>Количество дней
            <input type="number" name="days_count" min="1" value="1" required>
        </label>

        <label>Имя
            <input type="text" name="name" required>
        </label>

        <label>Телефон
            <input type="tel" name="phone" required>
        </label>

        <label>Почта
            <input type="email" name="email" required>
        </label>

        <label>Текст сообщения
            <textarea name="message" rows="4"></textarea>
        </label>

        <button type="submit">Рассчитать и скачать КП</button>
    </form>

    <div class="promo-calculator__result" aria-live="polite">
        <h3>ПРИМЕРНАЯ СТОИМОСТЬ РАБОТЫ</h3>
        <p class="promo-calculator__total"><span data-total>0</span> руб.</p>
        <ul>
            <li>База за персонал: <span data-base>0</span> руб.</li>
            <li>Налоги за персонал: <span data-personnel-tax>0</span> руб.</li>
            <li>Итого за персонал: <span data-personnel-total>0</span> руб.</li>
            <li>Менеджмент: <span data-management>0</span> руб.</li>
            <li>АК 15%: <span data-agency>0</span> руб.</li>
            <li>Итого с менеджментом и АК: <span data-subtotal>0</span> руб.</li>
            <li>Налоги: <span data-taxes>0</span> руб.</li>
        </ul>
        <p class="promo-calculator__disclaimer">
            Стоимость может меняться в зависимости от некоторых факторов. Для более точного расчета обратитесь к менеджеру. Не является публичной офертой.
        </p>
        <a class="promo-calculator__download" data-download hidden>Скачать коммерческое предложение</a>
        <p class="promo-calculator__status" data-status></p>
    </div>
</div>
