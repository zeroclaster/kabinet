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


Loc::loadMessages(__FILE__);
$this->setFrameMode(true);
?>


<div id="kabinetcontent" data-datetimepicker="" data-loadtable="" data-modalload=""></div>

<script type="text/html" id="kabinet-content">
    <div class="panel">
        <div class="panel-header">
            <div class="d-flex justify-content-between align-items-center flex-wrap group-10">
            </div>
        </div>

        <div class="panel-body">
<table class="table">
    <thead>
    <tr>
        <th scope="col">Клиент</th>
        <th scope="col">Проекты</th>
        <th scope="col">Задачи</th>
        <th scope="col">Исполнения</th>
    </tr>
    </thead>
    <tbody>
    <tr v-for="(client,clientindex) in dataclient">
        <td>
            <div>
				<div class="font-weight-bold">Клиент:</div>
                <div class="text-primary h4">{{client.NAME}} <span class="badge badge-warning"># {{client.ID}}</span></div>
				<div class="">E-mail: <a :href="'mailto:'+client.EMAIL">{{client.EMAIL}}</a></div>
			</div>
			<div class="mt-4">
				<div class="font-weight-bold">Кабинет:</div>
				<ul class="list-unstyled">
					<li><a :href="'/kabinet/profile/?usr='+client.ID">Профиль <i class="fa fa-angle-right" aria-hidden="true"></i></a></li>
					<li><a :href="'/kabinet/?usr='+client.ID">Дашборд и проекты <i class="fa fa-angle-right" aria-hidden="true"></i></a></li>
					<li><a :href="'/kabinet/finance/?usr='+client.ID">Финансы <i class="fa fa-angle-right" aria-hidden="true"></i></a></li>
				</ul>
			</div>
        </td>
        <td>
		
			<div class="mb-5" v-for="project in dataproject[client.ID]">
			
			<div class="font-weight-bold h4">{{project.UF_NAME}}</div>
				<ul class="list-unstyled">
					<li><a :href="'/kabinet/projects/breif/?id='+project.ID+'&usr='+client.ID">Бриф <i class="fa fa-angle-right" aria-hidden="true"></i></a></li>
					<li><a :href="'/kabinet/projects/planning/?p='+project.ID+'&usr='+client.ID">Планирование задач <i class="fa fa-angle-right" aria-hidden="true"></i></a></li>
				</ul>
			
			</div>
		
		</td>
        <td>
			<div v-for="project in dataproject[client.ID]">
				
				<div v-if="typeof datatask[client.ID] == 'undefined'">У клиента еще нет задач</div>

				<div v-for="task in datatask[client.ID]">	
					<div v-if="task.UF_PROJECT_ID == project.ID" class="mb-4">
                            {{badTask(task.ID,client.ID,project.UF_ORDER_ID,task.UF_PRODUKT_ID)}}
							<div class="d-flex" v-if="typeof dataorder[client.ID][project.UF_ORDER_ID][task.UF_PRODUKT_ID] !='undefined'">
                              <div><img :src="dataorder[client.ID][project.UF_ORDER_ID][task.UF_PRODUKT_ID].PREVIEW_PICTURE_SRC"></div>
								<div class="ml-3">
									<div class="h4" style="margin-top: 0;"><a :href="'/kabinet/projects/reports/?t='+task.ID+'&usr='+client.ID">{{task.UF_NAME}}</a></div>
									<div class="">Стоимость: <span class="text-danger" style="font-size: 23px;">{{task.FINALE_PRICE}} <span class="text-danger" v-if="task.UF_CYCLICALITY == 2">руб./месяц</span><span class="text-danger" v-if="task.UF_CYCLICALITY != 2">руб.</span></span></div>						
									<div class="info-blk">Количество: <span>{{task.UF_NUMBER_STARTS}}</span></div>
									<div class="info-blk">Дата создания: <span>{{task.UF_PUBLISH_DATE_ORIGINAL.FORMAT1}}</span></div>
									<div class="info-blk">Дата завершения: <span>{{task.UF_DATE_COMPLETION_ORIGINAL.FORMAT1}}</span></div>
                                    <div class="info-blk">Согласование: <span>{{viewListFieldTitle(task,'UF_COORDINATION')}}</span></div>
                                    <div class="info-blk">Отчетность: <span>{{viewListFieldTitle(task,'UF_REPORTING')}}</span></div>
                                    <div class="info-blk">Тип процесса: <span>{{viewListFieldTitle(task,'UF_CYCLICALITY')}}</span></div>
								</div>
							</div>					
					</div>
				</div>
			</div>
		</td>
        <td>

                <form action="/kabinet/admin/performances/" method="post">
                    <input type="hidden" name="clientidsearch" :value="client.ID">
                    <div class="form-group select-status" v-for="(TitleStatus,idStatus) in statusCatalog()">
                        <div class="form-check">
                            <input
                                    @change="$event.target.form.submit()"
                                    name="statusexecutionsearch"
                                    class="form-check-input"
                                    :id="$id(idStatus)"
                                    type="radio"
                                    :value="idStatus"
                            >

                            <label class="form-check-label text-primary" :for="$id(idStatus)">{{TitleStatus}} - <span class="badge badge-secondary">{{getClientExecution(client.ID).filter(execution => execution.UF_STATUS === idStatus).length}}</span></label>
                        </div>
                    </div>
                </form>


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


<script type="text/javascript" src="<?=$templateFolder?>/adminclient_list.js"></script>
<script>
	const filterclientlist = <?=CUtil::PhpToJSObject($arParams["FILTER"], false, true)?>;
    const PHPPARAMS = <?=CUtil::PhpToJSObject([
        "viewcount"=>$arParams["COUNT"],
        "total"=>$arResult["TOTAL"],
        "statuslistdata" => $runnerManager->getStatusList(),
    ], false, true)?>;
    window.addEventListener("components:ready", function(event) {
        const adminClientListApplication = BX.Vue3.BitrixVue.createApp(adminclient_list);

        adminClientListApplication._component.data = () => ({
            countview: PHPPARAMS.viewcount,
            total: PHPPARAMS.total,
            showloadmore: true,
            dataclient: <?=CUtil::PhpToJSObject($arResult["CLIENT_DATA"], false, true)?>,
            dataproject: <?=CUtil::PhpToJSObject($arResult["PROJECT_DATA"], false, true)?>,
            datatask: <?=CUtil::PhpToJSObject($arResult["TASK_DATA"], false, true)?>,
            dataorder: <?=CUtil::PhpToJSObject($arResult["ORDER_DATA"], false, true)?>,
            datarunner: <?=CUtil::PhpToJSObject($arResult["RUNNER_DATA"], false, true)?>
        });

        adminClientListApplication.config.globalProperties.statusCatalog = () => PHPPARAMS['statuslistdata'];
        configureVueApp(adminClientListApplication);
    });


    /*


    const adminclient_list = {
    data() {
        return {
            countview: PHPPARAMS.viewcount,
            total: PHPPARAMS.total,
            showloadmore: true,
            // Инициализируем пустые структуры данных
            dataclient: [],
            dataproject: {},
            datatask: {},
            dataorder: {},
            datarunner: {}
        }
    },
    // ... остальные свойства компонента
};

window.addEventListener("components:ready", () => {
    // Подготавливаем данные из PHP
    const componentData = {
        data() {
            return {
                dataclient: CUtil::PhpToJSObject($arResult["CLIENT_DATA"], false, true)?>,
                dataproject: CUtil::PhpToJSObject($arResult["PROJECT_DATA"], false, true)?>,
                datatask: CUtil::PhpToJSObject($arResult["TASK_DATA"], false, true)?>,
                dataorder: CUtil::PhpToJSObject($arResult["ORDER_DATA"], false, true)?>,
                datarunner: CUtil::PhpToJSObject($arResult["RUNNER_DATA"], false, true)?>
            }
        }
    };

    // Создаем приложение
    const adminClientListApplication = BX.Vue3.BitrixVue.createApp(adminclient_list);

    // Мерджим данные в компонент
    Object.assign(adminclient_list.data, componentData.data);

    // Настраиваем глобальные свойства
    adminClientListApplication.config.globalProperties.statusCatalog = () => PHPPARAMS.statuslistdata;

    // Конфигурируем приложение
    configureVueApp(adminClientListApplication);
});
     */
</script>
