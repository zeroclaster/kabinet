<?
$config = [
    'SYSTEM_MESSAGE' =>[
    'postupil_otchet_na_proverku' => 'Поступил отчет на проверку <a href="/kabinet/projects/reports/?t=#UF_TASK_ID#&id=#UF_QUEUE_ID#">смотреть</a>',
        'postupil_text_na_proverku' => 'Поступил текст на проверку <a href="/kabinet/projects/reports/?t=#UF_TASK_ID#&id=#UF_QUEUE_ID#">смотреть</a>',
        'ispolnitel_ozhidaet_tekst' => 'Исполнитель ожидает от Вас текст <a href="/kabinet/projects/reports/?t=#UF_TASK_ID#&id=#UF_QUEUE_ID#">смотреть</a>',
        'zadacha otmenena ispolnitelem' => 'Задача отменена администратором: <a href="/kabinet/projects/reports/?t=#UF_TASK_ID#&id=#UF_QUEUE_ID#">ссылка на исполнения задачи</a>',
        ],
    'FILTER'=>[
        'FILTER_DEFAULT' => [],
    ]
];

return $config;