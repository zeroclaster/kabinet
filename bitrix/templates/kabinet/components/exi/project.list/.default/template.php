<?
/*
 * Copyright (c) 24.05.2024
 * Suharkov Sergey (sexiterra@mail.ru)
 */

use Bitrix\Main\Localization\Loc as Loc;
use Bitrix\Main\Page\Asset;

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


Loc::loadMessages(__FILE__);
$this->setFrameMode(true);

//echo "<pre>";
//print_R($arResult['ITEMS']);
//echo "</pre>";


CUtil::InitJSCore(array('window'));
?>
<h2>Ваши проекты</h2>
<div id="kabinetcontent" data-modalload="" data-usermessangerr="projectmainpage" data-dashboardprojectlist=""></div>

<script type="text/html" id="kabinet-content">
<div class="panel project-block">
    <div class="panel-body"><span v-if="data.length==0">У Вас пока нет проектов.</span> <a class="btn btn-primary mdi-plus icon-button" href="/kabinet/projects/breif/">Создать новый проект</a></div>
</div>

<div class="panel project-block mb-5" v-for="value in data">
          <div class="panel-body project-item-block">
              <div>Проект <span class="badge badge-warning">#{{value.ID}}</span></div>
                <div class="row table-row-equal">
                    <div class="col-lg-9 h2 thumbnail thumbnail-left" style="margin: 0;">{{value.UF_NAME}}</div>

                    <div class="col-lg-3 thumbnail thumbnail-right" v-if="getRequireFields(value.ID).length > 0">
                        <a class="btn btn-danger mdi-alert-outline icon-button" :href="'/kabinet/projects/breif/?id='+value.ID"><?=Loc::getMessage('PROJECT_FILL_ALL')?></a>
                    </div>
                    <div class="col-lg-3 thumbnail thumbnail-right" v-else>
                        <a class="btn btn-primary" :href="'/kabinet/projects/breif/?id='+value.ID"><i class="fa fa-list" aria-hidden="true"></i>&nbsp;<?=Loc::getMessage('PROJECT_FILL_ALL')?></a>
                    </div>
                </div>

                <div class="row">
                    <div class="col-9 status-project" v-html="projectStatus(value)"></div>
                </div>

                <div class="row task-project table-row-equal">
                    <div class="col-lg-9 thumbnail thumbnail-left">
                        <div class="h4">Задачи проекта:</div>
                        <div>Задачи и работы в рамках проекта:</div>
                        <div class="d-flex flex-wrap" v-if="value.UF_ORDER_ID">
                            <div v-for="task in datatask" class="order-item-block">
                                {{(order = data2[value.UF_ORDER_ID][task['UF_PRODUKT_ID']],null)}}
                                <a v-if="task.UF_PROJECT_ID == value.ID" :href="'/kabinet/projects/reports/?t='+task.ID">
                                <img class="img-thumbnail mt-0" :src="order['PREVIEW_PICTURE_SRC']" :alt="order['NAME']">
                                </a>

                                <div v-if="task.UF_PROJECT_ID == value.ID" class="alert-status iphone-style-1" v-html="taskStatus_b(task.ID)"></div>
                                <div v-if="order" class="alert-counter iphone-style-1">{{showAlertCounter(getTaskID(value.ID,order.ID))}}</div>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-3 align-self-center thumbnail thumbnail-right">
                        <a class="btn btn-primary mdi-menu-right icon-button-right icon-i-button" :href="'/kabinet/projects/planning/?p='+value.ID" v-if="!Array.isArray(data2[value.UF_ORDER_ID])"><i class="fa fa-calendar" aria-hidden="true"></i>&nbsp;планирование</a>
                    </div>
                </div>

                <div class="row table-row-equal">
                    <div class="col-lg-9 thumbnail thumbnail-left">
                        <div class="h4">Финансы</div>
                        <div>
                            <?/*
                            <div class="text-danger">Средств на балансе хватит до {{futureSpending}}</div>
                            */?>

                            <!-- $billing->actualMonthExpenses($item['ID']) -->
                            <?/*
                            <div>Расходы в этом месяце ({{actualMonthExpensesMonth(value.ID)}}): <b>{{actualMonthExpenses(value.ID)}}</b> рублей.</div>
                            */?>
                            <div>Расход в текущем месяце: <b>{{actualMonthExpenses(value.ID)}}</b> рублей.</div>
                            <div>Бюджет на текущий месяц: <b>{{actualMonthBudget(value.ID)}}</b> рублей.</div>
                            <div>Бюджет на следующий месяц  {{nextMonthExpensesDate(value.ID)}}: <b>{{nextMonthExpenses(value.ID)}}</b> рублей.</div>

                            <div v-if="lastMonthExpenses(value.ID)>0">Расходы в прошлом месяце ({{lastMonthExpensesMonth(value.ID)}}): <b>{{lastMonthExpenses(value.ID)}}</b> рублей.</div>
                        </div>
                    </div>
                    <div class="col-lg-3 align-self-center thumbnail thumbnail-right">
                        <a v-if="nextMonthExpenses(value.ID)>databilling.UF_VALUE" class="btn btn-danger mdi-alert-outline icon-button" href="/kabinet/finance/">&nbsp;пополнить</a>
                        <a v-else class="btn btn-primary icon-i-button" href="/kabinet/finance/"><i class="fa fa-credit-card-alt" aria-hidden="true"></i>&nbsp;пополнить</a>
                    </div>
                </div>


                <div class="row table-row-equal" v-if="alertcount(value.ID)>0">
                    <div class="col-lg-9 thumbnail thumbnail-left">
                        <div class="h4"> Требует вашего внимания: {{alertcount(value.ID)}}</div>
                        <div>
                            В проекте есть уведомления, которые требуют вашей реакции.
                        </div>
                    </div>
                    <div class="col-lg-3 align-self-center thumbnail thumbnail-right">
                        <a class="btn btn-danger mdi-alert-outline icon-button" :href="'/kabinet/projects/?id='+value.ID">проверить</a>
                    </div>
                </div>

              <messangerperformances___ :projectID="value.ID" :taskID="0" :targetUserID="datauser.ID" :queue_id="0"/>

            </div> <!-- <div class="panel-body project-item-block"> -->
</div>

<!-- Modal -->
<div class="modal fade" :id="'exampleModal'" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h3 class="modal-title fs-5" id="exampleModalLabel">{{modaldata.title}}</h3>
            </div>
            <div class="modal-body">
                <div style="overflow:visible;height: 400px;">
                    <div v-for="product in data3" class="d-flex justify-content-between mb-3">
                        <div><img class="img-thumbnail" :src="product['PREVIEW_PICTURE_SRC']" :alt="product['NAME']" style="width: 50px;"></div>
                        <div class="align-self-center" style="width: 50%;">{{product.NAME}}</div>
                        <div class="align-self-center">{{product.PRICE}}</div>
                        <div class="align-self-center"><button type="button" @click="increment(product)">+</button>{{product.COUNT}}<button type="button" @click="decrease(product)">-</button></div>
                        <div class="align-self-center">
                            <button class="btn btn-block btn-sm btn-info" type="button" @click="chooseadd(product)">ДОБАВИТЬ</button>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" @click="closemodal">Закрыть</button>
            </div>
        </div>
    </div>
</div>

<!-- Modal -->
<div class="modal fade" :id="'exampleModal2'" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h3 class="modal-title fs-5" id="exampleModalLabel">{{modal2data.title}}</h3>
            </div>
            <div class="modal-body">
                <div v-if="modal2data.message == ''">{{modal2data.question}}</div>
                <div>{{modal2data.message}}</div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-primary" @click="removeproduct(modal2data.basketitem,modal2data.order_id)">Удалить</button>
                <button type="button" class="btn btn-secondary" @click="closemodal2">Закрыть</button>
            </div>
        </div>
    </div>
</div>
</script>

<?
(\KContainer::getInstance())->get('catalogStore','orderStore','briefStore','taskStore','queueStore','billingStore','userStore');
\Bitrix\Main\Page\Asset::getInstance()->addJs(SITE_TEMPLATE_PATH."/components/exi/project.list/.default/brief_list.js");
?>
<?
$message_state = CUtil::PhpToJSObject($arResult["MESSAGE_DATA"], false, true);
?>
<script>
    components.projectlist22 = {
        selector: '[data-dashboardprojectlist]',
        script: [
            '../../kabinet/components/exi/task.list/.default/task_status.js',
        ],
        init:null
    }


    const alert_project_count = <?=CUtil::PhpToJSObject($arResult['ALERT_PROJECT_COUNT'], false, true)?>;
    const task_alert = <?=CUtil::PhpToJSObject($arResult['TASK_ALERT'], false, true)?>;

    const  messageStore2 = BX.Vue3.Pinia.defineStore('messagelist2', {
        state: () => ({datamessage:<?=$message_state?>}),
    });

    window.addEventListener("components:ready", function(event) {
        var m = <?=CUtil::PhpToJSObject(['VIEW_COUNT' => $arParams['MESSAGE_COUNT'],], false, true)?>;
        m.TEMPLATE = messangerTemplate2;
        m.messageStore = messageStore2;

        messangerperformances___ = messanger_vuecomponent.start(m);


        project_list.start(<?=CUtil::PhpToJSObject([
            'PROJECT_ID'=>0,
            'CONTAINER' => '#kabinetcontent',
            'TEMPLATE' => '#kabinet-content',
            'NEXT_MONTH_EXPENSES' => $arResult['NEXT_MONTH_EXPENSES'],
            'ACTUAL_MONTH_EXPENSES' => $arResult['ACTUAL_MONTH_EXPENSES'],
            'ACTUAL_MONTH_BUDGET' => $arResult['ACTUAL_MONTH_BUDGET'],
            'LAST_MONTH_EXPENSES' => $arResult['LAST_MONTH_EXPENSES'],
            'FUTURE_SPENDING' => $arResult['FUTURE_SPENDING'],
        ], false, true)?>);

    });
</script>