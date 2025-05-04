<?
$configadmin = [
    'STATUS' =>[
		'ALERT' => [
			3,		//Ожидается текст от клиента
			5,		//На согласование (у клиента)
			8		//Отчет на проверке у клиента
			],
        'LIST' => [
            0=>"Запланирован",
            1=>"Взят в работу",
            2=>"Пишется текст",
            3=>"Ожидается текст от клиента",
            4=>"В работе у специалиста",
            5=>"На согласовании (у клиента)",
            6=>"Публикация",
            7=>"Готовится отчет",
            8=>"Отчет на проверке у клиента",
            9=>"Выполнено",
            10=>"Отменено"
        ],
        'CSS'=>[
            0=>"fc-event-warning",
            1=>"fc-event-success",
            2=>"fc-event-success",
            3=>"fc-event-success",
            4=>"fc-event-success",
            5=>"fc-event-success",
            6=>"fc-event-success",
            7=>"fc-event-success",
            8=>"fc-event-success",
            9=>"fc-event-light",
            10=>"fc-event-danger"
        ],
    ]
];

$a = $GLOBALS["USER_FIELD_MANAGER"]->getUserFields('HLBLOCK_14',null,LANGUAGE_ID);
$fields = array_keys($a);
$fields[] = 'ID';

$allowFileds = array_diff($fields,['UF_OPERATION',]);

$configadmin['ALLOW_FIELDS'] = $allowFileds;

$c1 = include __DIR__ . '/../config.php';
$config = array_merge($c1,$configadmin);
return $configadmin;