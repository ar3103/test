# Bitrix мультирегиональность (готовый каркас)

В репозитории добавлен каркас мультирегиональности для Bitrix, закрывающий требования:

- генерация поддоменов и подпапок;
- разные цены и контент в разрезе страны/города;
- поддержка стран и отделений;
- перевод языков;
- перевод полей (labels/placeholders) по языку региона;
- генерация sitemap и robots;
- формы на основе инфоблоков;
- подмена SEO-тегов;
- поддержка cookie;
- геоопределение + редирект + popup выбора региона;
- централизованное управление регионами;
- поддержка наценки.

## Где что лежит

- `local/php_interface/init.php` — подключение и bootstrap.
- `local/php_interface/classes/MultiRegion/Config.php` — единый справочник регионов.
- `RegionResolver.php` — определение региона (cookie/host/folder/geo) и редиректы.
- `RegionManager.php` — фасад для всего функционала.
- `PriceManager.php` — региональные цены и наценка.
- `SeoManager.php` — подмена meta/title.
- `LanguageManager.php` — словарь переводов + перевод полей.
- `SitemapRobotsGenerator.php` — генерация sitemap.xml и robots.txt.
- `IblockFormManager.php` — конфиг форм инфоблока с привязкой к региону и переводом полей.

## Пример использования цены

```php
$basePrice = 1000;
$regionalPrices = [
    'ru-moscow' => 950,
    'kz-almaty' => 6200,
];

$price = $regionManager->resolveProductPrice($basePrice, $context['regionCode'], $regionalPrices);
// ['amount' => ..., 'currency' => ..., 'markupPercent' => ...]
```

## Пример подмены SEO

```php
$regionManager->applySeo($context['regionCode'], static function (string $key, string $value): void {
    global $APPLICATION;

    if ($key === 'title') {
        $APPLICATION->SetTitle($value);
        return;
    }

    $APPLICATION->SetPageProperty($key, $value);
});
```

## Пример перевода полей формы

```php
$formManager = new \MultiRegion\IblockFormManager();
$formConfig = $formManager->getRegionFormConfig($context['regionCode']);

// $formConfig['FIELD_LABELS']['NAME'] => Имя / Name / Name
// $formConfig['FIELD_PLACEHOLDERS']['EMAIL'] => Введите e-mail / Enter e-mail / E-Mail eingeben
```

## Пример popup региона

На фронте показывайте popup, если:

```php
if ($context['popupRequired']) {
    // показать popup с выбором страны/города
}
```

## Генерация sitemap/robots

```php
$generator = new \MultiRegion\SitemapRobotsGenerator();

file_put_contents($_SERVER['DOCUMENT_ROOT'] . '/sitemap.xml',
    $generator->generateRegionalSitemap(['/catalog/', '/contacts/'])
);

file_put_contents($_SERVER['DOCUMENT_ROOT'] . '/robots.txt',
    $generator->generateRobotsTxt()
);
```
