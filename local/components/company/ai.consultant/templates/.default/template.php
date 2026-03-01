<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) {
    die();
}

$this->addExternalCss($templateFolder . '/style.css');
$this->addExternalJs($templateFolder . '/script.js');
?>
<div class="ai-consultant" id="ai-consultant-root"
     data-component-name="<?=htmlspecialcharsbx($arResult['COMPONENT_NAME'])?>"
     data-signed-params="<?=htmlspecialcharsbx($arResult['SIGNED_PARAMS'])?>">
    <button class="ai-consultant__toggle" type="button">Онлайн-консультант</button>

    <div class="ai-consultant__panel" hidden>
        <div class="ai-consultant__header">AI-консультант</div>

        <div class="ai-consultant__messages" id="ai-consultant-messages">
            <div class="ai-consultant__message ai-consultant__message--bot">
                Здравствуйте! Я помогу по вашему вопросу. Подскажите, как вас зовут, телефон и что вас интересует?
            </div>
        </div>

        <div class="ai-consultant__contacts">
            <input type="text" id="ai-name" placeholder="Имя">
            <input type="tel" id="ai-phone" placeholder="Телефон">
            <input type="text" id="ai-topic" placeholder="Что интересует?">
        </div>

        <form class="ai-consultant__form" id="ai-consultant-form">
            <textarea id="ai-consultant-input" placeholder="Введите сообщение" required></textarea>
            <button type="submit">Отправить</button>
        </form>

        <div class="ai-consultant__rating" id="ai-consultant-rating" hidden>
            <span>Оцените качество консультации:</span>
            <button data-rating="5">5</button>
            <button data-rating="4">4</button>
            <button data-rating="3">3</button>
            <button data-rating="2">2</button>
            <button data-rating="1">1</button>
        </div>
    </div>
</div>
