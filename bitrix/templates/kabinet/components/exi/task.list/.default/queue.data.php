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
$all_id_task_list = array_column($sL->get('Kabinet.Task')->getData(), 'ID');

// все исполнения пользователя
$Queue_state = CUtil::PhpToJSObject($sL->get('Kabinet.Runner')->getData($all_id_task_list ), false, true);
?>

        // TODO вынести скрипт в отдельный файл
        const calendarData = <?=$Queue_state?>;
        const  calendarStore = BX.Vue3.Pinia.defineStore('calendarQueue', {
            state: () => ({datacalendarQueue:calendarData}),
            actions: {
                updatecalendare(queue=[],project_id=0)
                {

                    const task = tasklistStore();
                    let tasklist = [];
                    if (project_id){
                        for(const val of task.datatask){
                           if (val.UF_PROJECT_ID == project_id) tasklist.push(val);
                        }
                    }else{
                        tasklist = task.datatask;
                     }

                    if (!queue.length){
                        queue = this.datacalendarQueue;
                    }

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
                    console.log(fullCalendar.fullCalendar);


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