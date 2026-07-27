<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) {
    die();
}
?>
<div class="ai-consultant" id="ai-consultant" data-site-id="<?=htmlspecialcharsbx($arResult['SITE_ID'])?>">
    <button class="ai-consultant__toggle" type="button"><?=htmlspecialcharsbx($arResult['TITLE'])?></button>
    <div class="ai-consultant__window" hidden>
        <div class="ai-consultant__messages" id="ai-consultant-messages">
            <div class="ai-consultant__message ai-consultant__message--assistant"><?=htmlspecialcharsbx($arResult['GREETING'])?></div>
        </div>
        <form class="ai-consultant__form" id="ai-consultant-form">
            <input type="text" name="message" placeholder="Напишите сообщение" required>
            <button type="submit">Отправить</button>
        </form>
        <form class="ai-consultant__lead" id="ai-consultant-lead" hidden>
            <input type="text" name="name" placeholder="Ваше имя" required>
            <input type="tel" name="phone" placeholder="Телефон" required>
            <textarea name="interest" placeholder="Что вас интересует?"></textarea>
            <button type="submit">Оставить контакты</button>
        </form>
        <div class="ai-consultant__rating" id="ai-consultant-rating" hidden>
            <span>Оцените качество консультации:</span>
            <button type="button" data-rating="5">5</button>
            <button type="button" data-rating="4">4</button>
            <button type="button" data-rating="3">3</button>
            <button type="button" data-rating="2">2</button>
            <button type="button" data-rating="1">1</button>
        </div>
    </div>
</div>
