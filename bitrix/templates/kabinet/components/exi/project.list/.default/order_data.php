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
$data = $projectManager->orderData();
$brief_state = CUtil::PhpToJSObject($data, false, true);
?>
const orderListStoreData = <?=$brief_state?>;
const orderlistStore = BX.Vue3.Pinia.defineStore('orderlist', {
        state: () => ({data2:orderListStoreData}),
		actions: {
				getRequireFields(order_id)
				{					
					var ret_array = [];
					const order = this.data2[order_id];
					for(element_id in order){
						let REQUIRE_FIELDS_BRIEF = order[element_id]['REQUIRE_FIELDS_BRIEF'];
						if (REQUIRE_FIELDS_BRIEF.VALUE_XML_ID)
							REQUIRE_FIELDS_BRIEF.VALUE_XML_ID.forEach(function(item){
								if(ret_array.indexOf(item) == -1){
									ret_array.push(item);
								}
							});
					}

                    ret_array.push('UF_NAME');
                    ret_array.push('UF_PROJECT_GOAL');
                    ret_array.push('UF_TOPICS_LIST');
					
					return ret_array;
				},
		},
});
<?
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/epilog_after.php");