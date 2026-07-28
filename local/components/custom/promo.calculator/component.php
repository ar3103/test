<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) {
    die();
}

$componentClass = __DIR__ . '/class.php';
if (file_exists($componentClass)) {
    require_once $componentClass;
}

$component = new PromoCalculatorComponent($this);
$component->executeComponent();
