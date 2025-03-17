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
<div id="billing-detalie" class="col-md-12" data-loadtable=""></div>
<script type="text/html" id="billing-view-template">
<div class="panel billing-view">
    <div class="panel-header">
        <div class="d-flex justify-content-between align-items-center flex-wrap group-10">
            <div class="h2">Финансы</div>
        </div>
    </div>
    <div class="panel-body">
        <div class="row">
            <div class="col-md-2">
                <div class="billing-info d-flex align-items-center flex-column">
                    <div>Текущий баланс, руб:</div>
                    <div class="money-total">{{databilling.UF_VALUE_ORIGINAL}}</div>
                </div>
                <div class="mt-4 text-center">
                    <a :class="'btn btn-primary icon-i-button '+isAlertFinance" href="/kabinet/finance/deposit/"><i class="fa fa-credit-card-alt" aria-hidden="true"></i>&nbsp;Пополнить баланс</a>
                </div>
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
                <?if($arResult['FUTURE_SPENDING']):?><div class="d-flex" v-if="databilling.UF_VALUE_ORIGINAL>0"><div class="mr-3">Средств хватит до </div> <div class="bold"><?=$arResult['FUTURE_SPENDING']?></div></div><?endif;?>
                */?>

                <div><a href="/kabinet/finance/">История операций</a></div>
            </div>
        </div>

    </div>
</div>
</script>



<?
(\KContainer::getInstance())->get('briefStore','taskStore','billingStore','userStore');
\Bitrix\Main\Page\Asset::getInstance()->addJs(SITE_TEMPLATE_PATH."/components/exi/billing.view/.default/billing.view.js");
?>

<script>
    const  historylistStore = BX.Vue3.Pinia.defineStore('historylist', {
        state: () => ({historybillingdata:<?=CUtil::PhpToJSObject($arResult['HISTORY_DATA'], false, true)?>})
    });


    window.addEventListener("components:ready", function(event) {
        billing_view.start(<?=CUtil::PhpToJSObject([
            'EXPENSES_NEXT_MONTH' => $arResult['EXPENSES_NEXT_MONTH'],
            'FILTER' => $arParams["FILTER"],
            'CONTAINER' => '#billing-detalie',
            'TEMPLATE' => '#billing-view-template',
            "viewcount"=>$arParams["COUNT"],
            "total"=>$arResult["TOTAL"],
        ], false, true)?>);
    });

</script>