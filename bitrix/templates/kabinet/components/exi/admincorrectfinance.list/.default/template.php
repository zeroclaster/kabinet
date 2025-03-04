<?
use Bitrix\Main\Localization\Loc as Loc;

if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();
/** @var array $arParams */
/** @var array $arResult */
/** @global CMain $APPLICATION */
/** @global CUser $USER */
/** @global CDatabase $DB */
/** @var CBitrixComponentTemplate $this */
/** @var string $templateName */
/** @var string $templateFile */
/** @var string $templateFolder */
/** @var string $componentPath */
/** @var CBitrixComponent $component */

$sL = \Bitrix\Main\DI\ServiceLocator::getInstance();
$runnerManager = $sL->get('Kabinet.Runner');
$messanger = $sL->get('Kabinet.Messanger');

Loc::loadMessages(__FILE__);
$this->setFrameMode(true);
?>


<div id="kabinetcontent" data-loadtable="" data-modalload=""></div>

<script type="text/html" id="kabinet-content">
    <div class="panel admin-execution-list">
        <div class="panel-header">
            <div class="d-flex justify-content-between align-items-center flex-wrap group-10">
            </div>
        </div>

        <div class="panel-body">
<table class="table">
    <thead>
    <tr>
        <th scope="col">Планирование</th>
        <th scope="col">Исполнение</th>
        <th scope="col">Сумма</th>
        <th scope="col">действия</th>
    </tr>
    </thead>
    <tbody>
    <tr v-for="(runner,runnerindex) in datarunner">
        {{(UF_AUTHOR_ID=datatask[runner.UF_TASK_ID].UF_AUTHOR_ID,null)}}
        {{(UF_PROJECT_ID=datatask[runner.UF_TASK_ID].UF_PROJECT_ID,null)}}
        <td style="width: 20%">
            <div>
                <div class="mb-3">
                    <div>Клиент:</div>
                    <div class="text-primary">{{dataclient[UF_AUTHOR_ID].PRINT_NAME}} (#{{dataclient[UF_AUTHOR_ID].ID}})</div>
                    <div><a href="mailto:{{dataclient[UF_AUTHOR_ID].EMAIL}}"></a></div>
                </div>
                <div class="mb-3">
                    <div>Проект:</div>
                    <div class="text-primary">{{dataproject[UF_PROJECT_ID].UF_NAME}}</div>
                </div>
                <div class="mb-3">
                    <div>Задача:</div>
                    <div class="text-primary">{{datatask[runner.UF_TASK_ID].UF_NAME}}</div>
                    <div style="font-size: 11px;">
                        <div class="info-blk">Согласование: <span>{{viewListFieldTitle(datatask[runner.UF_TASK_ID],'UF_COORDINATION')}}</span></div>
                        <div class="info-blk">Отчетность: <span>{{viewListFieldTitle(datatask[runner.UF_TASK_ID],'UF_REPORTING')}}</span></div>
                        <div class="info-blk">Тип процесса: <span>{{viewListFieldTitle(datatask[runner.UF_TASK_ID],'UF_CYCLICALITY')}}</span></div>
                    </div>
                </div>
            </div>

        </td>

        <td width="20%">
            <div>Исполнение #{{runner.ID}}<div class="alert alert-danger" role="alert" v-if="runner.UF_HITCH == 1">Просроченная задача</div></div>

            <div class="mb-3" v-if="runner.UF_ELEMENT_TYPE == 'multiple'">
                Количество запланированных исполнений: {{datatask[runner.UF_TASK_ID].UF_NUMBER_STARTS}}
            </div>
            
            <div class="mb-3" v-if="datatask[runner.UF_TASK_ID].UF_JUSTFIELD">
                <div class="">{{dataorder[UF_AUTHOR_ID][dataproject[UF_PROJECT_ID].UF_ORDER_ID][datatask[runner.UF_TASK_ID].UF_PRODUKT_ID].JUST_FILED.VALUE}}:</div>
                <input class="form-control" type="text" :value="datatask[runner.UF_TASK_ID].UF_JUSTFIELD">
            </div>

        </td>
        <td style="">
            {{runner.UF_MONEY_RESERVE}} руб.
        </td>
        <td style="">
            <correctFinance :tindex="runnerindex" v-model="runner.UF_MONEY_RESERVE"/>
        </td>
    </tr>
    </tbody>
</table>
        </div>
    </div>
    <div class="text-right mt-1">
        показать по: <input name="viewcount" type="text" v-model="countview" style="width: 35px;">
    </div>
    <div class="d-flex justify-content-center">
    <div class="d-flex align-items-center">Найдено {{total}}, показано {{viewedcount}}</div>
    <div v-if="showloadmore" class="ml-3"><button class="btn btn-primary" type="button" @click="moreload">Показать еще +{{countview}}</button></div>
    </div>
</script>

<?
(\KContainer::getInstance())->get('catalogStore');
?>
<?ob_start();?>
<script>
    const clientListStoreData = <?=CUtil::PhpToJSObject($arResult["CLIENT_DATA"], false, true)?>;
	const projectListStoreData = <?=CUtil::PhpToJSObject($arResult["PROJECT_DATA"], false, true)?>;
	const taskListStoreData = <?=CUtil::PhpToJSObject($arResult["TASK_DATA"], false, true)?>;
	const orderListStoreData = <?=CUtil::PhpToJSObject($arResult["ORDER_DATA"], false, true)?>;
	const runnerListStoreData = <?=CUtil::PhpToJSObject($arResult["RUNNER_DATA"], false, true)?>;
	const filterclientlist = <?=CUtil::PhpToJSObject($arParams["FILTER"], false, true)?>;


    const  clientlistStore = BX.Vue3.Pinia.defineStore('clientlist', {
        state: () => ({dataclient:clientListStoreData}),
    });

    const  projectlistStore = BX.Vue3.Pinia.defineStore('projectlist', {
        state: () => ({dataproject:projectListStoreData}),
    });

    const  orderlistStore = BX.Vue3.Pinia.defineStore('orderlist', {
        state: () => ({dataorder:orderListStoreData}),
    });

    const  runnerlistStore = BX.Vue3.Pinia.defineStore('runnerlist', {
        state: () => ({datarunner:runnerListStoreData}),
    });

    const  tasklistStore = BX.Vue3.Pinia.defineStore('tasklist', {
        state: () => ({datatask:taskListStoreData}),
    });


    const  messageStore = BX.Vue3.Pinia.defineStore('messagelist', {
    state: () => ({datamessage:<?=CUtil::PhpToJSObject($arResult["MESSAGE_DATA"], false, true)?>}),
    });
	
</script>
    <script type="text/javascript" src="<?=SITE_TEMPLATE_PATH?>/components/exi/profile.user/admin/user.data.php"></script>
    <script type="text/javascript" src="<?=$templateFolder?>/admin_correct_finance.js"></script>

    <script>
	    var messangerperformances = null;

	    // Заглушка, т.к. используется в клиентской части
        const  brieflistStore = BX.Vue3.Pinia.defineStore('brieflist', {
            state: () => ({data:[]}),
        });

        window.addEventListener("components:ready", function(event) {
				admin_correct_finance.start(<?=CUtil::PhpToJSObject([
						"viewcount"=>$arParams["COUNT"],
					"total"=>$arResult["TOTAL"],
				], false, true)?>);
        });
    </script>
<?
$addScriptinPage = trim(ob_get_contents());
ob_end_clean();
$addscript = (\KContainer::getInstance())->get('addscript');
if (!$addscript) $addscript = [];
$addscript[] = $addScriptinPage;
(\KContainer::getInstance())->maked($addscript,'addscript');
?>