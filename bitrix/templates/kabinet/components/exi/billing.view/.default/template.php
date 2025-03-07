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

$nextmouth= (new \Bitrix\Main\Type\DateTime)->add("+1 months");
[$nextMouthStart,$nextMouthEnd]  = \PHelp::nextMonth();
?>
<!-- отображение -->
<div id="billing-detalie" class="col-md-12" data-loadtable=""></div>

<!-- шаблон -->
<script type="text/html" id="kabinet-content">
<div class="panel billing-view">
    <div class="panel-body">
        <div class="row">
            <div class="col-md-2">
                <div class="billing-info d-flex align-items-center flex-column">
                    <div>Текущий баланс, руб:</div>
                    <div class="money-total">{{databilling.UF_VALUE_ORIGINAL}}</div>
                </div>

                <div class="mt-4 text-center"><a class="btn btn-primary" :href="'/kabinet/finance/deposit/'+usr_id_const"><i class="fa fa-credit-card-alt" aria-hidden="true"></i> Пополнить баланс</a></div>

            </div>
            <div class="col-md-5 info">
                <?if($arResult['RESERVED']):?><div class="d-flex info"><div class="mr-3">Всего запланировано задач на:</div> <div class="bold"><?=$arResult['RESERVED']?> руб.</div></div><?endif;?>

                <div class="d-flex"><div class="mr-3">Расход в текущем месяце:</div> <div class="bold"><?=$arResult['ACTUAL_MONTH_EXPENSES']?> руб.</div></div>
                <div class="d-flex"><div class="mr-3">Бюджет на текущий месяц:</div> <div class="bold"><?=$arResult['ACTUAL_MONTH_BUDGET']?> руб.</div></div>
                <div class="d-flex"><div class="mr-3">Бюджет на следующий месяц с <?=$nextMouthStart->format("d.m.Y")?> по <?=$nextMouthEnd->format("d.m.Y")?>:</div> <div class="bold"><?=$arResult['EXPENSES_NEXT_MONTH']?> руб.</div></div>
                <?if($arResult['RECOMMEND_UP_BALANCE']>0):?>
                    <div class="d-flex mt-3"><div class="mr-3">Рекомендуем пополнить на:</div> <div class="bold"><?=$arResult['RECOMMEND_UP_BALANCE']?> руб.</div></div>
                <?endif;?>

                <?/*
                <div class="d-flex"><div class="mr-3">Запланированные расходы на следующий месяц, <?=\PHelp::monthName($nextmouth->format("n"))?>:</div> <div class="bold"><?=$arResult['EXPENSES_NEXT_MONTH']?> рублей.</div></div>
                */?>

                <?/*
                <?if($arResult['FUTURE_SPENDING']):?><div class="d-flex" v-if="databilling.UF_VALUE_ORIGINAL>0"><div class="mr-3">Средств хватит до </div> <div class="bold"><?=$arResult['FUTURE_SPENDING']?></div></div><?endif;?>
                */?>

            </div>
        </div>
    </div>
</div>  <!-- <div class="panel billing-view"> -->

<div class="h3">История операций</div>
<div class="panel billing-history-list">
    <div class="panel-header">
        <div class="d-flex justify-content-between align-items-center flex-wrap group-10">
        </div>
    </div>

    <div class="panel-body">

        <table class="table">
            <thead>
            <tr>
                <th scope="col" style="width: 10%">Дата</th>
                <th scope="col">Операция</th>
                <th scope="col">id</th>
                <th scope="col">Проект</th>
                <th scope="col" style="width: 5%">Сумма, руб.</th>
            </tr>
            </thead>
            <tbody>
            <tr v-for="history of historybillingdata">
                <th scope="row">{{history.UF_PUBLISH_DATE_ORIGINAL.FORMAT3}}</th>
                <td>
                    {{history.UF_OPERATION_ORIGINAL}}
                    {{task(history).UF_NAME}}
                </td>
                <td>{{history.ID}}</td>
                <td>
                    <div v-if="project(history)">
                        <a :href="'/kabinet/projects/?id='+project(history).ID" target="_blank">{{project(history).UF_NAME}}</a>
                    </div>
                </td>
                <td>{{history.UF_VALUE_ORIGINAL}}</td>
            </tr>
            </tbody>
        </table>

        <div class="text-right mt-1">
            показать по: <input name="viewcount" type="text" v-model="countview" style="width: 35px;">
        </div>
        <div class="d-flex justify-content-center">
            <div class="d-flex align-items-center">Найдено {{total}}, показано {{viewedcount}}</div>
			<?
			// TODO AKULA сделать перезагзузку страницы с новым количеством отображеиня
			?>
            <div v-if="showloadmore" class="ml-3"><button class="btn btn-primary" type="button" @click="moreload">Показать еще +{{countview}}</button></div>
        </div>

    </div>
</div>
</script>
<!-- конец шаблона -->

<?
(\KContainer::getInstance())->get('briefStore');
(\KContainer::getInstance())->get('taskStore');
(\KContainer::getInstance())->get('billingStore');
(\KContainer::getInstance())->get('userStore');
\Bitrix\Main\Page\Asset::getInstance()->addJs(SITE_TEMPLATE_PATH."/components/exi/billing.view/.default/billing.view.js");
?>

<script>
    const  historylistStore = BX.Vue3.Pinia.defineStore('historylist', {
        state: () => ({historybillingdata:<?=CUtil::PhpToJSObject($arResult['HISTORY_DATA'], false, true)?>})
    });

    window.addEventListener("components:ready", function(event) {
        billing_view.start(<?=CUtil::PhpToJSObject([
            'FILTER' => $arParams["FILTER"],
            'CONTAINER' => '#billing-detalie',
            'TEMPLATE' => '#kabinet-content',
            "viewcount"=>$arParams["COUNT"],
            "total"=>$arResult["TOTAL"],
        ], false, true)?>);
    });
</script>