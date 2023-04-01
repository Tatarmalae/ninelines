<?php

use Bitrix\Main\Localization\Loc;

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) {
    die();
}

$arComponentDescription = [
    'NAME'        => Loc::getMessage('SIMPLENEWS_COMP_NAME'),
    'DESCRIPTION' => Loc::getMessage('SIMPLENEWS_COMP_DESCRIPTION'),
    'SORT'        => 100,
    'PATH'        => [
        'ID'   => 'ninelines_components',
        'NAME' => Loc::getMessage('SIMPLENEWS_COMP_CATALOG_SECTION'),
        'SORT' => 10,
    ],
    'CACHE_PATH'  => 'Y',
    'COMPLEX'     => 'N',
];
