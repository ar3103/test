<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) {
    die();
}

$this->includeComponentClass('class.php');
$component = new AcmeLeadFormComponent($this);
$component->executeComponent();
