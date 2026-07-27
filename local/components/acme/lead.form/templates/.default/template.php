<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) {
    die();
}

\Bitrix\Main\Page\Asset::getInstance()->addCss($this->GetFolder() . '/style.css');
\Bitrix\Main\Page\Asset::getInstance()->addJs($this->GetFolder() . '/script.js');
?>
<div class="acme-lead-form-wrapper">
    <form id="<?= htmlspecialcharsbx($arResult['FORM_ID']) ?>" class="acme-lead-form" method="post" enctype="multipart/form-data">
        <?= bitrix_sessid_post() ?>
        <input type="hidden" name="ACME_LEAD_FORM_AJAX" value="Y">
        <input type="text" name="company_name" value="" autocomplete="off" tabindex="-1" class="acme-lead-form__honeypot" aria-hidden="true">

        <label>
            <?= GetMessage('ACME_LEAD_FORM_FIELD_NAME') ?>
            <input type="text" name="NAME" required>
        </label>

        <label>
            <?= GetMessage('ACME_LEAD_FORM_FIELD_PHONE') ?>
            <input type="tel" name="PHONE" required>
        </label>

        <label>
            <?= GetMessage('ACME_LEAD_FORM_FIELD_EMAIL') ?>
            <input type="email" name="EMAIL">
        </label>

        <fieldset>
            <legend><?= GetMessage('ACME_LEAD_FORM_FIELD_CONTACT_METHOD') ?></legend>
            <label><input type="checkbox" name="CONTACT_METHOD[]" value="phone"> <?= GetMessage('ACME_LEAD_FORM_CONTACT_PHONE') ?></label>
            <label><input type="checkbox" name="CONTACT_METHOD[]" value="email"> <?= GetMessage('ACME_LEAD_FORM_CONTACT_EMAIL') ?></label>
            <label><input type="checkbox" name="CONTACT_METHOD[]" value="telegram"> <?= GetMessage('ACME_LEAD_FORM_CONTACT_TELEGRAM') ?></label>
        </fieldset>

        <label>
            <?= GetMessage('ACME_LEAD_FORM_FIELD_MESSAGE') ?>
            <textarea name="MESSAGE" rows="5"></textarea>
        </label>

        <label>
            <?= GetMessage('ACME_LEAD_FORM_FIELD_ATTACHMENTS') ?>
            <input type="file" name="ATTACHMENTS[]" multiple>
        </label>

        <input type="hidden" name="PAGE_URL" value="">
        <input type="hidden" name="PAGE_TITLE" value="">
        <input type="hidden" name="REFERER" value="">
        <input type="hidden" name="UTM_SOURCE" value="">
        <input type="hidden" name="UTM_MEDIUM" value="">
        <input type="hidden" name="UTM_CAMPAIGN" value="">
        <input type="hidden" name="UTM_TERM" value="">
        <input type="hidden" name="UTM_CONTENT" value="">

        <button type="submit"><?= GetMessage('ACME_LEAD_FORM_SUBMIT') ?></button>
        <div class="acme-lead-form__result" aria-live="polite"></div>
    </form>
</div>
