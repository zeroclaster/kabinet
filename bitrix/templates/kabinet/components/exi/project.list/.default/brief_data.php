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
$infoManager = $sL->get('Kabinet.infoProject');
$detailsManager = $sL->get('Kabinet.detailsProject');
$targetManager = $sL->get('Kabinet.targetProject');

$data = $projectManager->getData();
$info_state = [];
$details_state = [];
$target_state = [];

if ($data) {
    foreach ($data as $project) {
        $info_state = array_merge($info_state, $infoManager->getData($project['ID']));
        $details_state = array_merge($details_state, $detailsManager->getData($project['ID']));
        $target_state = array_merge($target_state, $targetManager->getData($project['ID']));
    }
}
?>
const briefListStoreData = <?=CUtil::PhpToJSObject($data, false, true)?>;
const infoStoreData = <?=CUtil::PhpToJSObject($info_state, false, true)?>;
const detailsStoreData = <?=CUtil::PhpToJSObject($details_state, false, true)?>;
const targetStoreData = <?=CUtil::PhpToJSObject($target_state, false, true)?>;
const  brieflistStore = BX.Vue3.Pinia.defineStore('brieflist', {
    state: () => ({
            data:briefListStoreData,
            datainfo:infoStoreData,
            datadetails:detailsStoreData,
            datatarget:targetStoreData
    }),
    actions: {
        getRequireFields(PROJECT_ID){
	    const orderStore = orderlistStore();
			let ret_arr = [];

			let project = null;
            let info = null;
            let details = null;
            let target = null;
			for(element of this.data){
				if (element['ID'] != PROJECT_ID) continue;
				project = element;
				break;
			}
            for(element of this.datainfo){
                if (element['UF_PROJECT_ID'] != PROJECT_ID) continue;
                info = element;
                break;
            }
            for(element of this.datadetails){
                if (element['UF_PROJECT_ID'] != PROJECT_ID) continue;
                details = element;
                break;
            }
            for(element of this.datatarget){
                if (element['UF_PROJECT_ID'] != PROJECT_ID) continue;
                target = element;
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

            for(field in info){
                if(RequireFields.indexOf(field) != -1 && info[field] == ''){
                    ret_arr.push(field);
                }
            }

            for(field in details){
                if(RequireFields.indexOf(field) != -1 && details[field] == ''){
                    ret_arr.push(field);
                }
            }

            for(field in target){
                if(RequireFields.indexOf(field) != -1 && target[field] == ''){
                    ret_arr.push(field);
                }
            }
			
			return ret_arr;		
		},
    },
});
<?
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/epilog_after.php");