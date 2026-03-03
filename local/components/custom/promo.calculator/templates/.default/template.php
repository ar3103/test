<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) {
    die();
}

use Bitrix\Main\Localization\Loc;

Loc::loadMessages(__FILE__);

$promoTypesJson = htmlspecialcharsbx(json_encode($arResult['PROMO_TYPES'], JSON_UNESCAPED_UNICODE));
$i18nJson = htmlspecialcharsbx(json_encode([
    'status_sending' => Loc::getMessage('PROMO_CALCULATOR_STATUS_SENDING'),
    'status_error_process' => Loc::getMessage('PROMO_CALCULATOR_STATUS_ERROR_PROCESS'),
    'status_error_network' => Loc::getMessage('PROMO_CALCULATOR_STATUS_ERROR_NETWORK'),
], JSON_UNESCAPED_UNICODE));

$this->addExternalCss($templateFolder . '/style.css');
$this->addExternalJs($templateFolder . '/script.js');
?>
<div class="promo-calculator" data-promo-types="<?= $promoTypesJson ?>" data-management-percent="<?= (float)$arResult['MANAGEMENT_PERCENT'] ?>" data-i18n="<?= $i18nJson ?>">
    <form class="promo-calculator__form" method="post">
        <?= bitrix_sessid_post() ?>
        <input type="hidden" name="promo_calculator_action" value="submit">

        <label><?= Loc::getMessage('PROMO_CALCULATOR_FIELD_PROMO_TYPE') ?>
            <select name="promo_type" required>
                <option value=""><?= Loc::getMessage('PROMO_CALCULATOR_OPTION_SELECT_TYPE') ?></option>
                <?php foreach ($arResult['PROMO_TYPES'] as $type => $rate): ?>
                    <option value="<?= htmlspecialcharsbx($type) ?>"><?= htmlspecialcharsbx($type) ?> (<?= (float)$rate ?> <?= Loc::getMessage('PROMO_CALCULATOR_RUB_PER_DAY') ?>)</option>
                <?php endforeach; ?>
            </select>
        </label>

        <label><?= Loc::getMessage('PROMO_CALCULATOR_FIELD_REQUIRED_STAFF') ?>
            <select name="required_staff[]" multiple required size="6">
                                <?php foreach ($arResult['REQUIRED_STAFF_OPTIONS'] as $staffOption): ?>
                    <option value="<?= htmlspecialcharsbx($staffOption) ?>"><?= htmlspecialcharsbx($staffOption) ?></option>
                <?php endforeach; ?>
            </select>
        </label>

        <label><?= Loc::getMessage('PROMO_CALCULATOR_FIELD_PEOPLE_COUNT') ?>
            <input type="number" name="people_count" min="1" value="1" required>
        </label>

        <label><?= Loc::getMessage('PROMO_CALCULATOR_FIELD_HOURS_COUNT') ?>
            <input type="number" name="hours_count" min="1" value="1" required>
        </label>

        <label><?= Loc::getMessage('PROMO_CALCULATOR_FIELD_DAYS_COUNT') ?>
            <input type="number" name="days_count" min="1" value="1" required>
        </label>

        <label><?= Loc::getMessage('PROMO_CALCULATOR_FIELD_NAME') ?>
            <input type="text" name="name" required>
        </label>

        <label><?= Loc::getMessage('PROMO_CALCULATOR_FIELD_PHONE') ?>
            <input type="tel" name="phone" required>
        </label>

        <label><?= Loc::getMessage('PROMO_CALCULATOR_FIELD_EMAIL') ?>
            <input type="email" name="email" required>
        </label>

        <label><?= Loc::getMessage('PROMO_CALCULATOR_FIELD_MESSAGE') ?>
            <textarea name="message" rows="4"></textarea>
        </label>

        <button type="submit"><?= Loc::getMessage('PROMO_CALCULATOR_SUBMIT') ?></button>
    </form>

    <div class="promo-calculator__result" aria-live="polite">
        <h3><?= Loc::getMessage('PROMO_CALCULATOR_RESULT_TITLE') ?></h3>
        <p class="promo-calculator__total"><span data-total>0</span> <?= Loc::getMessage('PROMO_CALCULATOR_CURRENCY_RUB') ?></p>
        <ul>
            <li><?= Loc::getMessage('PROMO_CALCULATOR_RESULT_BASE') ?>: <span data-base>0</span> <?= Loc::getMessage('PROMO_CALCULATOR_CURRENCY_RUB') ?></li>
            <li><?= Loc::getMessage('PROMO_CALCULATOR_RESULT_PERSONNEL_TAX') ?>: <span data-personnel-tax>0</span> <?= Loc::getMessage('PROMO_CALCULATOR_CURRENCY_RUB') ?></li>
            <li><?= Loc::getMessage('PROMO_CALCULATOR_RESULT_PERSONNEL_TOTAL') ?>: <span data-personnel-total>0</span> <?= Loc::getMessage('PROMO_CALCULATOR_CURRENCY_RUB') ?></li>
            <li><?= Loc::getMessage('PROMO_CALCULATOR_RESULT_MANAGEMENT') ?>: <span data-management>0</span> <?= Loc::getMessage('PROMO_CALCULATOR_CURRENCY_RUB') ?></li>
            <li><?= Loc::getMessage('PROMO_CALCULATOR_RESULT_AGENCY') ?>: <span data-agency>0</span> <?= Loc::getMessage('PROMO_CALCULATOR_CURRENCY_RUB') ?></li>
            <li><?= Loc::getMessage('PROMO_CALCULATOR_RESULT_SUBTOTAL') ?>: <span data-subtotal>0</span> <?= Loc::getMessage('PROMO_CALCULATOR_CURRENCY_RUB') ?></li>
            <li><?= Loc::getMessage('PROMO_CALCULATOR_RESULT_TAXES') ?>: <span data-taxes>0</span> <?= Loc::getMessage('PROMO_CALCULATOR_CURRENCY_RUB') ?></li>
        </ul>
        <p class="promo-calculator__disclaimer">
            <?= Loc::getMessage('PROMO_CALCULATOR_DISCLAIMER') ?>
        </p>
        <a class="promo-calculator__download" data-download hidden><?= Loc::getMessage('PROMO_CALCULATOR_DOWNLOAD_LINK') ?></a>
        <p class="promo-calculator__status" data-status></p>
    </div>
</div>
