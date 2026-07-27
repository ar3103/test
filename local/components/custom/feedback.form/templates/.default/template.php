<?php

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) {
    die();
}
?>
<?php if (!empty($arResult['SUCCESS'])): ?>
    <div class="feedback-form__success">Спасибо! Ваш отзыв отправлен.</div>
<?php else: ?>
    <?php if (!empty($arResult['ERROR'])): ?>
        <div class="feedback-form__error"><?=htmlspecialcharsbx($arResult['ERROR'])?></div>
    <?php endif; ?>
    <form method="post" class="feedback-form">
        <?=bitrix_sessid_post()?>
        <div>
            <label>Имя</label>
            <input type="text" name="NAME" required>
        </div>
        <div>
            <label>Email</label>
            <input type="email" name="EMAIL" required>
        </div>
        <div>
            <label>Текст отзыва</label>
            <textarea name="TEXT" required></textarea>
        </div>
        <div>
            <label>Оценка</label>
            <select name="RATING" required>
                <option value="5">5</option>
                <option value="4">4</option>
                <option value="3">3</option>
                <option value="2">2</option>
                <option value="1">1</option>
            </select>
        </div>
        <button type="submit" name="submit_feedback" value="Y">Отправить</button>
    </form>
<?php endif; ?>
