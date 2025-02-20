<?
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

?>

<div id="projectdetailcontent" data-modalload=""></div>
<script type="text/html" id="project-detail">
    <div class="panel project-item-block">
        <div class="panel-body">
            <div class="row">
                <div class="col-md-10">

                    <div class="row mb-5 task-project table-row-equal">
                        <div class="col-md-8 thumbnail thumbnail-left">
                            <div class="h4">Задачи проекта</div>
                            <div class="d-flex flex-wrap">
                                <div v-for="PRODUKT in data2[project.UF_ORDER_ID]" class="order-item-block">
                                    <div style="display: none;">
                                        product id: {{PRODUKT.ID}}
                                        order id: {{project.UF_ORDER_ID}}
                                    </div>
                                    <img class="img-thumbnail mt-0" :src="PRODUKT['PREVIEW_PICTURE_SRC']" :alt="PRODUKT['NAME']">

                                <?/*
                                    <div class="block-remove-butt"><button class="order-remove-button" type="button" @click="removeProductModal(PRODUKT)"><i class="fa fa-times" aria-hidden="true"></i></button></div>
                               */?>
                                    <div class="alert-counter iphone-style-1">{{showAlertCounter(getTaskID(project.ID,PRODUKT.ID))}}</div>
                                </div>
                                <button type="button" class="add-butt-order" @click="addbuttorder(project)"></button>
                            </div>
                        </div>
                        <div class="col-md-4 thumbnail thumbnail-right">
                                <a class="btn btn-primary" href="/kabinet/projects/planning/?p=<?=$arParams["PROJECT_ID"]?>"  v-if="!Array.isArray(data2[project.UF_ORDER_ID])">Планирование задач</a>
                        </div>
                    </div>


                    <div class="row mb-5 table-row-equal" v-if="getRequireFields(PROJECT_ID).length > 0">
                        <div class="col-md-8 thumbnail thumbnail-left">
                            <div class="h4">Бриф с информацией о компании, товарах и услугах</div>
                            <div class="small">Заполните Бриф на основе которого мы будем создавать описания компании в справочниках и каталогах сайтов-отзовиков. Информация о товарах и услугах компании, фотографии - для подробных и убедительных отзывов.</div>
                        </div>
                        <div class="col-md-4 thumbnail thumbnail-right">
                            ️ <a class="btn btn-danger mdi-alert-outline icon-button" href="/kabinet/projects/breif/?id=<?=$arParams["PROJECT_ID"]?>">Заполнить</a>
                        </div>
                    </div>

                    <div class="row mb-5 table-row-equal" v-else>
                        <div class="col-md-8 thumbnail thumbnail-left">
                            <div class="h4">Бриф с информацией о компании, товарах и услугах</div>
                        </div>
                        <div class="col-md-4 thumbnail thumbnail-right">
                            ️ <a class="btn btn-primary" href="/kabinet/projects/breif/?id=<?=$arParams["PROJECT_ID"]?>">Заполнить</a>
                        </div>
                    </div>

                    <div class="row mb-5 table-row-equal">
                        <div class="col-md-8 thumbnail thumbnail-left">
                            <div class="h4">Финансы</div>
                            <div v-if="nextMonthExpenses(project.ID)">
                                Запланированые расходы на ближайший месяц {{nextMonthExpensesDate(project.ID)}}:
                                {{nextMonthExpenses(project.ID)}} рублей.
                                Средств на балансе хватит до {{futureSpending}}
                            </div>
                        </div>
                        <div class="col-md-4 thumbnail thumbnail-right">
                            <a v-if="nextMonthExpenses(project.ID)>databilling.UF_VALUE" class="btn btn-danger mdi-alert-outline icon-button" href="/kabinet/finance/">пополнить</a>
                            <a v-else class="btn btn-primary" href="/kabinet/finance/">пополнить</a>
                        </div>
                    </div>

                    <div class="row" v-if="alertcount(project.ID)>0">
                        <div class="col-8">
                            <div class="h4"> Требует вашего внимания: {{alertcount(project.ID)}}</div>
                            <div>
                                В проекте есть уведомления, которые требуют вашей реакции.
                            </div>
                        </div>
                        <div class="col-md-4 align-self-center">
                            <a class="btn btn-danger mdi-alert-outline icon-button" href="/kabinet/notifications/">проверить</a>
                        </div>
                    </div>

                </div>
            </div>
        </div>
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
                            <div><a :href="product.LINK" target="_blank"><img class="img-thumbnail" :src="product['PREVIEW_PICTURE_SRC']" :alt="product['NAME']" style="width: 67%;"></a></div>
                            <div class="align-self-center" style="width: 50%;"><a :href="product.LINK" target="_blank">{{product.NAME}}</a></div>
                            <div class="align-self-center">{{product.PRICE}}</div>

                            <?/*
                            <div class="align-self-center count-button-change"><button class="btn btn-warning plus-btn" type="button" @click="increment(product)">+</button><input type="text" class="count-product-input" v-model="product.COUNT"><button class="btn btn-warning minus-btn" type="button" @click="decrease(product)">-</button></div>
                            */?>

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
(\KContainer::getInstance())->get('catalogStore','orderStore','briefStore','taskStore','queueStore','billingStore');
\Bitrix\Main\Page\Asset::getInstance()->addJs(SITE_TEMPLATE_PATH."/components/exi/project.list/.default/brief_list.js");
?>
<script>
    const alert_project_count = <?=CUtil::PhpToJSObject($arResult['ALERT_PROJECT_COUNT'], false, true)?>;
    const task_alert = <?=CUtil::PhpToJSObject($arResult['TASK_ALERT'], false, true)?>;
    window.addEventListener("components:ready", function(event) {
        project_list.start(<?=CUtil::PhpToJSObject([
            'PROJECT_ID'=>$arParams["PROJECT_ID"],
            'CONTAINER' => '#projectdetailcontent',
            'TEMPLATE' => '#project-detail',
            'NEXT_MONTH_EXPENSES' => $arResult['NEXT_MONTH_EXPENSES'],
            'FUTURE_SPENDING' => $arResult['FUTURE_SPENDING'],
        ], false, true)?>);
    });
</script>