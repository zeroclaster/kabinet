<?php
use Bitrix\Main\DI\ServiceLocator as SL;

return [
    'controllers' => [
        'value' => [
            'namespaces' => [
                '\\Bitrix\\kabinet\\Controller' => 'evn',
            ],
        ],
        'readonly' => true,
    ]
];
