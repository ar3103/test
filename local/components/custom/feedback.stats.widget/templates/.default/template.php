<?php if (!empty($arResult['STATS'])): ?>
    <div id="feedback-widget">
        <h3>Статистика отзывов (Сайт: <?=htmlspecialcharsbx($arResult['SITE_ID'])?>)</h3>
        <label for="siteFilter">Фильтр по сайту:</label>
        <input id="siteFilter" type="text" value="<?=htmlspecialcharsbx($arResult['SITE_ID'])?>" style="margin: 0 0 12px 8px;" />

        <canvas id="feedbackChart" width="600" height="300"></canvas>

        <table border="1" cellpadding="5" cellspacing="0" style="border-collapse: collapse; width:100%; margin-top:20px;">
            <thead>
                <tr>
                    <th>Заказ</th>
                    <th>Ссылка на форму</th>
                    <th>Переходы (Clicks)</th>
                    <th>Заполнено (Filled)</th>
                    <th>Дата создания</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($arResult['STATS'] as $s): ?>
                    <tr>
                        <td><?=htmlspecialcharsbx($s['ORDER_ID'])?></td>
                        <td>
                            <a href="<?=htmlspecialcharsbx($s['SHORT_URL'])?>" target="_blank" rel="noopener noreferrer">
                                <?=htmlspecialcharsbx($s['SHORT_URL'])?>
                            </a>
                        </td>
                        <td><?=htmlspecialcharsbx($s['CLICKS'])?></td>
                        <td><?=htmlspecialcharsbx($s['FILLED'])?></td>
                        <td><?=htmlspecialcharsbx($s['CREATED_AT'])?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
        const ctx = document.getElementById('feedbackChart').getContext('2d');

        const labels = [
            <?php foreach ($arResult['STATS'] as $s): ?>
                "Заказ <?=CUtil::JSEscape((string) $s['ORDER_ID'])?>",
            <?php endforeach; ?>
        ];

        const clicksData = [
            <?php foreach ($arResult['STATS'] as $s): ?>
                <?= (int) $s['CLICKS'] ?>,
            <?php endforeach; ?>
        ];

        const filledData = [
            <?php foreach ($arResult['STATS'] as $s): ?>
                <?= (int) $s['FILLED'] ?>,
            <?php endforeach; ?>
        ];

        new Chart(ctx, {
            type: 'bar',
            data: {
                labels: labels,
                datasets: [
                    {
                        label: 'Переходы (Clicks)',
                        data: clicksData,
                        backgroundColor: 'rgba(54, 162, 235, 0.7)'
                    },
                    {
                        label: 'Заполнено (Filled)',
                        data: filledData,
                        backgroundColor: 'rgba(75, 192, 192, 0.7)'
                    }
                ]
            },
            options: {
                responsive: true,
                plugins: {legend: {position: 'top'}},
                scales: {y: {beginAtZero: true}}
            }
        });
    </script>
    <script src="<?=$templateFolder?>/template.js"></script>
<?php else: ?>
    <p>Нет данных для отображения.</p>
<?php endif; ?>
