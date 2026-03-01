<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) {
    die();
}

use Bitrix\Main\Localization\Loc;

Loc::loadMessages(__FILE__);

$this->addExternalJs($templateFolder . '/script.js');
$this->addExternalCss($templateFolder . '/style.css');
?>
<div class="cf-form" id="<?= htmlspecialcharsbx($arResult['FORM_ID']) ?>">
    <form enctype="multipart/form-data">
        <?= bitrix_sessid_post() ?>
        <div class="cf-form__row">
            <label><?= Loc::getMessage('CUSTOM_FEEDBACK_FORM_FIELD_NAME') ?></label>
            <input type="text" name="NAME" required>
        </div>
        <div class="cf-form__row">
            <label><?= Loc::getMessage('CUSTOM_FEEDBACK_FORM_FIELD_PHONE') ?></label>
            <input type="tel" name="PHONE" required>
        </div>
        <div class="cf-form__row">
            <label><?= Loc::getMessage('CUSTOM_FEEDBACK_FORM_FIELD_EMAIL') ?></label>
            <input type="email" name="EMAIL" required>
        </div>
        <div class="cf-form__row">
            <span><?= Loc::getMessage('CUSTOM_FEEDBACK_FORM_FIELD_CONTACT_METHOD') ?></span>
            <label><input type="checkbox" name="CONTACT_METHOD[]" value="phone"> <?= Loc::getMessage('CUSTOM_FEEDBACK_FORM_CONTACT_PHONE') ?></label>
            <label><input type="checkbox" name="CONTACT_METHOD[]" value="email"> <?= Loc::getMessage('CUSTOM_FEEDBACK_FORM_CONTACT_EMAIL') ?></label>
            <label><input type="checkbox" name="CONTACT_METHOD[]" value="telegram"> <?= Loc::getMessage('CUSTOM_FEEDBACK_FORM_CONTACT_TELEGRAM') ?></label>
        </div>
        <div class="cf-form__row">
            <label><?= Loc::getMessage('CUSTOM_FEEDBACK_FORM_FIELD_MESSAGE') ?></label>
            <textarea name="MESSAGE" rows="4"></textarea>
        </div>
        <div class="cf-form__row">
            <label><?= Loc::getMessage('CUSTOM_FEEDBACK_FORM_FIELD_FILES') ?></label>
            <input type="file" name="FILES[]" multiple>
        </div>

        <input type="text" name="HP_FIELD" class="cf-form__hp" tabindex="-1" autocomplete="off">
        <input type="hidden" name="PAGE_URL" value="<?= htmlspecialcharsbx($APPLICATION->GetCurPageParam('', [], false)) ?>">
        <input type="hidden" name="PAGE_TITLE" value="<?= htmlspecialcharsbx($APPLICATION->GetTitle()) ?>">
        <input type="hidden" name="REFERER" value="<?= htmlspecialcharsbx((string)$_SERVER['HTTP_REFERER']) ?>">
        <input type="hidden" name="UTM_SOURCE" value="<?= htmlspecialcharsbx((string)($_GET['utm_source'] ?? '')) ?>">
        <input type="hidden" name="UTM_MEDIUM" value="<?= htmlspecialcharsbx((string)($_GET['utm_medium'] ?? '')) ?>">
        <input type="hidden" name="UTM_CAMPAIGN" value="<?= htmlspecialcharsbx((string)($_GET['utm_campaign'] ?? '')) ?>">
        <input type="hidden" name="UTM_TERM" value="<?= htmlspecialcharsbx((string)($_GET['utm_term'] ?? '')) ?>">
        <input type="hidden" name="UTM_CONTENT" value="<?= htmlspecialcharsbx((string)($_GET['utm_content'] ?? '')) ?>">
        <input type="hidden" name="YANDEX_CAPTCHA_TOKEN" value="">
        <input type="hidden" name="GOOGLE_CAPTCHA_TOKEN" value="">

        <?php if (!empty($arParams['YANDEX_SMARTCAPTCHA_SITE_KEY'])): ?>
            <div class="smart-captcha" data-sitekey="<?= htmlspecialcharsbx($arParams['YANDEX_SMARTCAPTCHA_SITE_KEY']) ?>"></div>
            <script src="https://smartcaptcha.yandexcloud.net/captcha.js" defer></script>
        <?php endif; ?>

        <?php if (!empty($arParams['GOOGLE_RECAPTCHA_SITE_KEY'])): ?>
            <div class="g-recaptcha" data-sitekey="<?= htmlspecialcharsbx($arParams['GOOGLE_RECAPTCHA_SITE_KEY']) ?>"></div>
            <script src="https://www.google.com/recaptcha/api.js" async defer></script>
        <?php endif; ?>

        <button type="submit"><?= Loc::getMessage('CUSTOM_FEEDBACK_FORM_SUBMIT') ?></button>
        <div class="cf-form__result" data-role="result"></div>
    </form>
</div>
<script>
BX.ready(function () {
    new CustomFeedbackForm({
        rootId: '<?= CUtil::JSEscape($arResult['FORM_ID']) ?>',
        componentName: 'custom:feedback.form',
        signedParameters: '<?= CUtil::JSEscape($arResult['SIGNED_PARAMETERS']) ?>',
        siteId: '<?= CUtil::JSEscape($arResult['SITE_ID']) ?>'
    });
});
</script>
