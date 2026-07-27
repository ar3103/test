<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) {
    die();
}

$arComponentDescription = [
    'NAME' => GetMessage('ACME_LEAD_FORM_COMPONENT_NAME'),
    'DESCRIPTION' => GetMessage('ACME_LEAD_FORM_COMPONENT_DESC'),
    'PATH' => [
        'ID' => 'acme',
        'NAME' => GetMessage('ACME_LEAD_FORM_COMPONENT_SECTION'),
    ],
];
