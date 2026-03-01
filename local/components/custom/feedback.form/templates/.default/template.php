<?php

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) {
    die();
}
?>
<?php if (!empty($arResult['ERRORS'])): ?>
    <div class="feedback-errors">
        <?php foreach ($arResult['ERRORS'] as $error): ?>
            <p><?=htmlspecialcharsbx($error)?></p>
        <?php endforeach; ?>
    </div>
<?php endif; ?>

<?php if ($arResult['SUCCESS']): ?>
    <div class="feedback-success">Спасибо! Отзыв отправлен.</div>
<?php else: ?>
    <form method="post">
        <?=bitrix_sessid_post()?>
        <label>
            Имя
            <input type="text" name="NAME" required>
        </label>
        <label>
            Email
            <input type="email" name="EMAIL" required>
        </label>
        <label>
            Текст отзыва
            <textarea name="TEXT" required></textarea>
        </label>
        <label>
            Оценка
            <select name="RATING" required>
                <option value="">Выберите</option>
                <?php for ($i = 1; $i <= 5; $i++): ?>
                    <option value="<?=$i?>"><?=$i?></option>
                <?php endfor; ?>
            </select>
        </label>
        <button type="submit">Отправить</button>
    </form>
<?php endif; ?>
