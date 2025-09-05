<?
$DOCUMENT_ROOT = $_SERVER["DOCUMENT_ROOT"];

define("NO_KEEP_STATISTIC", true);
define("NOT_CHECK_PERMISSIONS",true);

/*
* Используется в \bitrix\php_interface\init.php
* необходим что бы подключать модуль кабинета CModule::IncludeModule('kabinet');
*/
define("KABINET_SCRIPT",true);

require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_before.php");
header('Content-type: application/javascript; charset=utf-8');
header('Cache-Control: public, max-age=31536000');
header('Pragma: cache');
header('Expires: '.gmdate('D, d M Y H:i:s \G\M\T', time() + 31536000));

//@set_time_limit(86400);	// 24 часа
//@ignore_user_abort(true);

//ini_set('error_reporting', E_ALL);
//ini_set('display_errors', 1);
//ini_set('display_startup_errors', 1);

$sL = \Bitrix\Main\DI\ServiceLocator::getInstance();

// все задачи пользователя
//$all_id_task_list = array_column($sL->get('Kabinet.Task')->getData(), 'ID');

// все исполнения пользователя
//$Queue_state = CUtil::PhpToJSObject($sL->get('Kabinet.Runner')->getData($all_id_task_list ), false, true);



$runnerManager =\Bitrix\Main\DI\ServiceLocator::getInstance()->get('Kabinet.Runner');
$user = \Bitrix\Main\DI\ServiceLocator::getInstance()->get('user');
$user_id = $user->get('ID');

$select = $runnerManager->getSelectFields();
$HLBClass = \Bitrix\Kabinet\taskrunner\datamanager\FulfillmentTable::class;
$Queue = $HLBClass::getlist([
    'select'=>$select,
    'filter'=>['TASK.UF_AUTHOR_ID'=>$user_id],
    //'order' => ['ID'=>'DESC'],
])->fetchAll();

//echo \Bitrix\Main\Entity\Query::getLastQuery();
$Queue_state = CUtil::PhpToJSObject($runnerManager->remakeFulfiData($Queue), false, true);
?>

        // TODO вынести скрипт в отдельный файл
        const calendarData = <?=$Queue_state?>;
        const  calendarStore = BX.Vue3.Pinia.defineStore('calendarQueue', {
            state: () => ({datacalendarQueue:calendarData}),
            actions: {
                getEventsByTaskId(task_id) {
                        if (!this.datacalendarQueue) return [];
                        return this.datacalendarQueue.filter(event => event?.UF_TASK_ID == task_id);
                },
                updatecalendare(queue=[],project_id=0)
                {
                    const task = tasklistStore();

                    // берем все задачи или только конкретного проекта
                    let tasklist = project_id ? task.getTaskByProjectId(project_id) : task.datatask;
                    if (!queue.length) queue = this.datacalendarQueue;

                    const taskMap = new Map(tasklist.map(task => [task.ID, task]));

                    // передираем все исполнения пользователя
                    const newData = queue
                    .map(({ UF_TASK_ID, UF_PLANNE_DATE_ORIGINAL, UF_STATUS_ORIGINAL, ID }) => {
                            //ищим задачу по исполнению
                            const taskfind = taskMap.get(UF_TASK_ID);
                            if (!taskfind) return null;
                            return {
                                title: taskfind.UF_NAME,
                                start: UF_PLANNE_DATE_ORIGINAL.FORMAT2,
                                className: UF_STATUS_ORIGINAL.CSS,
                                url: `/kabinet/projects/reports/?t=${taskfind.ID}&queue=${ID}`
                            };
                    })
                    .filter(Boolean);

                    let fullCalendar = $("#calendar1");

                    fullCalendar.fullCalendar('removeEvents');
                    fullCalendar.fullCalendar('removeEventSources');
                    // добавляем все исполнения пользователся в календарь
                    fullCalendar.fullCalendar( 'addEventSource', newData );

                    // 1. Создаем Set из UF_TASK_ID для мгновенной проверки
                    const tasksWithExecutions = new Set(queue.map(elm => elm.UF_TASK_ID));

                    // перебираем все задачи
                    const { planned, inprogress, done, canceled } = tasklist.reduce(
                            (acc, val) => {
                                    // если у задачи нет исполнений то переходим к след. задачи
                                    if (queue.length > 0 && !tasksWithExecutions.has(val.ID)) return acc;
                                    acc.planned += parseInt(val.QUEUE_STATIST[0].COUNT) || 0;
                                    acc.inprogress += parseInt(val.QUEUE_STATIST[1].COUNT) || 0;
                                    acc.done += parseInt(val.QUEUE_STATIST[2].COUNT) || 0;
                                    acc.canceled += parseInt(val.QUEUE_STATIST[3].COUNT) || 0;
                                    return acc;
                            },
                            { planned: 0, inprogress: 0, done: 0, canceled: 0 }
                    );

                    BX.adjust(BX('done_calendar_counter'), {text: done});
                    BX.adjust(BX('inprogress_calendar_counter'), {text: inprogress});
                    BX.adjust(BX('planned_calendar_counter'), {text: planned});
                    //пока не используется
                    //BX.adjust(BX('canceled_calendar_counter'), {text: canceled});
                },
                // вывод календаря на странице отчеты по исполнениям
                // сейчас не выводится
				updatecalendareReports(queue=[])
				{
					const task = tasklistStore();
					let tasklist = task.datatask;				
					let newData = [];
					var taskfind;
					queue.forEach(function (element) {
						taskfind = null;
						for(const val of tasklist){
							if (val.ID == element.UF_TASK_ID) taskfind = val;
						}

						if (!taskfind) return;

						newData.push({
									'title':taskfind.UF_NAME,
									"start":element.UF_PLANNE_DATE_ORIGINAL.FORMAT2,
									"className":element.UF_STATUS_ORIGINAL.CSS,
									//"url": '/kabinet/projects/planning/?p='+taskfind.UF_PROJECT_ID+'#produkt'+taskfind.UF_PRODUKT_ID
									"url": '/kabinet/projects/reports/?t='+taskfind.ID+'&queue='+element.ID
									});
					});

					let fullCalendar = $("#calendar1");
					fullCalendar.fullCalendar('removeEvents');
					fullCalendar.fullCalendar('removeEventSources');
					

					fullCalendar.fullCalendar( 'addEventSource', newData );

					let done_count = 0;
					let inprogress_count = 0;
					let planned_count = 0;
					let canceled_count = 0;
					//обновляем статистику
					for(const val of tasklist){

                        if (queue.length>0){
                                let isExist = false;
                                for(const elm of queue) {
                                            if (val.ID == elm.UF_TASK_ID)
                                                    isExist = true;
                                }
                                if (!isExist) continue;
                        }
						
						// Запланированы
						planned_count = planned_count + parseInt(val.QUEUE_STATIST[0].COUNT);

						// Выполняются
						inprogress_count = inprogress_count + parseInt(val.QUEUE_STATIST[1].COUNT);

						// Выполнено
						done_count = done_count + parseInt(val.QUEUE_STATIST[2].COUNT);

						// Отменено
						canceled_count = canceled_count + parseInt(val.QUEUE_STATIST[3].COUNT);
					}

					BX.adjust(BX('done_calendar_counter'), {text: done_count});
					BX.adjust(BX('inprogress_calendar_counter'), {text: inprogress_count});
					BX.adjust(BX('planned_calendar_counter'), {text: planned_count});
					//пока не используется
					//BX.adjust(BX('canceled_calendar_counter'), {text: canceled_count});
				},
			},			
        });

<?
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/epilog_after.php");