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
$projectManager = $sL->get('Kabinet.Project');
$data = $projectManager->getData();
$brief_state = CUtil::PhpToJSObject($data, false, true);
?>
const briefListStoreData = <?=$brief_state?>;
const  brieflistStore = BX.Vue3.Pinia.defineStore('brieflist', {
    state: () => ({data:briefListStoreData}),
    actions: {
        getRequireFields(PROJECT_ID){
			const orderStore = orderlistStore();
			let ret_arr = [];

			let project = null;
			for(element of this.data){
				if (element['ID'] != PROJECT_ID) continue;
				project = element;
				break;
			}
			
		
			if (!project) return [];
			
			const RequireFields = orderStore.getRequireFields(project.UF_ORDER_ID);
			if (RequireFields.length == 0) return []; 
			
			for(field in project){
				if(RequireFields.indexOf(field) != -1 && project[field] == ''){
					ret_arr.push(field);
				}
			}
			
			return ret_arr;		
		},
    },
});
<?
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/epilog_after.php");