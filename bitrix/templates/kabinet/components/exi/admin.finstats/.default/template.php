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

Loc::loadMessages(__FILE__);
$this->setFrameMode(true);
?>

<div class="panel admin-fin-stats">
    <div class="panel-header">
        <div class="d-flex justify-content-between align-items-center flex-wrap group-10">
            <h3>Финансовая статистика исполнений по статусам</h3>
        </div>
    </div>

    <div class="panel-body">
        <?if(!empty($arResult["STATS"])):?>
            <div class="table-responsive">
                <table class="table finstats-table">
                    <thead>
                    <tr>
                        <th>ID статуса</th>
                        <th>Статус</th>
                        <th>Количество исполнений</th>
                        <th>Сумма средств</th>
                    </tr>
                    </thead>
                    <tbody>
                    <?foreach($arResult["STATS"] as $stat):?>
                        <tr>
                            <td><?=$stat["STATUS_ID"]?></td>
                            <td><?=$stat["STATUS_NAME"]?></td>
                            <td><?=$stat["COUNT"]?></td>
                            <td><?=$stat["SUM_FORMATTED"]?> руб.</td>
                        </tr>
                    <?endforeach;?>
                    </tbody>
                    <tfoot>
                    <tr class="table-info">
                        <td colspan="2"><strong>Итого:</strong></td>
                        <td><strong><?=$arResult["TOTAL_COUNT"]?></strong></td>
                        <td><strong><?=number_format($arResult["TOTAL_SUM"], 2, '.', ' ')?> руб.</strong></td>
                    </tr>
                    </tfoot>
                </table>
            </div>
        <?else:?>
            <div class="alert alert-info">
                Нет данных для отображения. Попробуйте изменить параметры фильтра.
            </div>
        <?endif;?>
    </div>
</div>