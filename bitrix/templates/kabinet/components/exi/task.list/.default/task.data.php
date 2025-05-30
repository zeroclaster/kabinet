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

$data = \Bitrix\Main\DI\ServiceLocator::getInstance()->get('Kabinet.Task')->getData();
//echo "<pre>";
//var_dump($data);
//echo "</pre>";

$task_state = CUtil::PhpToJSObject($data, false, true);
?>
    const taskListStoreData = <?=$task_state?>;
    const  tasklistStore = BX.Vue3.Pinia.defineStore('tasklist', {
            state: () => (
                        {
                        datatask:taskListStoreData,
                        testdata:[{ID:1,TITLE:'1111'},{ID:2,TITLE:'2222'}]
                    }),
            getters: {
                gettestdataID: (state) => {
                        return (ID) => state.testdata.find(item => item.ID === ID);  // ✅ Вернет элемент или undefined
                }
            },
            actions: {
                getTaskByProjectId(project_id) {
                    if (!this.datatask) return [];
                    return this.datatask.filter(task => task?.UF_PROJECT_ID == project_id);
                }
            }
    });
<?
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/epilog_after.php");
?>