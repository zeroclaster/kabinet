<?
$configadmin = [
    'FILTER'=>[
    'FILTER_DEFAULT' => ['!UF_TYPE' => \Bitrix\Kabinet\messanger\Messanger::SYSTEM_MESSAGE],
        ]
];

$c1 = include __DIR__ . '/../config.php';
$config = array_merge($c1,$configadmin);

return $config;