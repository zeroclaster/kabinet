<?
$config = [
    'SYSTEM_MESSAGE' =>[
    'postupil_otchet_na_proverku' => 'Поступил отчет на проверку – <a href="https://kupi-otziv.ru/kabinet/projects/reports/?t=#UF_TASK_ID#&id=#UF_QUEUE_ID#">открыть</a>',
        'postupil_text_na_proverku' => 'Поступил текст на проверку – <a href="https://kupi-otziv.ru/kabinet/projects/reports/?t=#UF_TASK_ID#&id=#UF_QUEUE_ID#">открыть</a>',
        'ispolnitel_ozhidaet_tekst' => 'Система ожидает от Вас текст – <a href="https://kupi-otziv.ru/kabinet/projects/reports/?t=#UF_TASK_ID#&id=#UF_QUEUE_ID#">открыть</a>',
        'zadacha otmenena ispolnitelem' => 'Задача отменена администратором: <a href="https://kupi-otziv.ru/kabinet/projects/reports/?t=#UF_TASK_ID#&id=#UF_QUEUE_ID#">ссылка на исполнения задачи</a>',
        'trebuetca_informacia' => 'Требуется информация для <a href="https://kupi-otziv.ru/kabinet/projects/reports/?t=#UF_TASK_ID#&id=#UF_QUEUE_ID#">исполнения задачи </a>. Проверьте, верна ли ссылка, которую вы указали в задаче, заполнен ли вами бриф проекта. Работа по задаче может быть приостановлена, просьба перейти в кабинет и дополнить задачу данными.',
        ],
    'FILTER'=>[
        'FILTER_DEFAULT' => [],
    ]
];

return $config;