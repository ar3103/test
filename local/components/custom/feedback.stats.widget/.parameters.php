<?php
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true) die();

$arComponentParameters = [
    "GROUPS" => [],
    "PARAMETERS" => [
        "SITE_ID" => [
            "PARENT" => "BASE",
            "NAME" => "ID сайта",
            "TYPE" => "STRING",
            "DEFAULT" => SITE_ID
        ]
    ]
];
?>
