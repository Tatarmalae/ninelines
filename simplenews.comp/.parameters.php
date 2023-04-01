<?php

use Bitrix\Main\Localization\Loc;

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) {
    die();
}

$arComponentParameters = [
    'GROUPS'     => [
    ],
    'PARAMETERS' => [
        'IBLOCK_ID'  => [
            'PARENT'   => 'BASE',
            'NAME'     => Loc::getMessage('SIMPLENEWS_COMP_IBLOCK_ID'),
            'MULTIPLE' => 'N',
            'TYPE'     => 'STRING',
            'DEFAULT'  => '',
        ],
        'NEWS_COUNT' => [
            'PARENT'   => 'BASE',
            'NAME'     => Loc::getMessage('SIMPLENEWS_COMP_NEWS_COUNT'),
            'MULTIPLE' => 'N',
            'TYPE'     => 'STRING',
            'DEFAULT'  => '10',
        ],
        'CACHE_TIME' => [
            'DEFAULT' => 36000000,
        ],
    ],
];
