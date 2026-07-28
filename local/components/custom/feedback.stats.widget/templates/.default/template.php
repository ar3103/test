<?php if(!empty($arResult['STATS'])): ?>
<div id="feedback-widget">
    <h3>Статистика отзывов (Сайт: <?=$arResult['SITE_ID']?>)</h3>
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
            <?php foreach($arResult['STATS'] as $s): ?>
                <tr>
                    <td><?=$s['ORDER_ID']?></td>
                    <td><a href="<?=$s['SHORT_URL']?>" target="_blank"><?=$s['SHORT_URL']?></a></td>
                    <td><?=$s['CLICKS']?></td>
                    <td><?=$s['FILLED']?></td>
                    <td><?=$s['CREATED_AT']?></td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
const ctx = document.getElementById('feedbackChart').getContext('2d');

const labels = [
    <?php foreach($arResult['STATS'] as $s): ?>
        "Заказ <?=$s['ORDER_ID']?>",
    <?php endforeach; ?>
];

const clicksData = [
    <?php foreach($arResult['STATS'] as $s): ?>
        <?=$s['CLICKS']?>,
    <?php endforeach; ?>
];

const filledData = [
    <?php foreach($arResult['STATS'] as $s): ?>
        <?=$s['FILLED']?>,
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
        plugins: { legend: { position: 'top' } },
        scales: { y: { beginAtZero: true } }
    }
});
</script>
<?php else: ?>
<p>Нет данных для отображения.</p>
<?php endif; ?>
