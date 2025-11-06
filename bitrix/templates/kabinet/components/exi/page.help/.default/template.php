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

$uid = $arParams["CODE"];
?>

<section  id="block<?=$uid?>" class="section-xs help-page-module" style="display: none;">
        <div class="container-fluid">
        <div class="row row-30">
            <div class="col-md-12">
                <div class="panel">
                    <div class="panel-header">
                        <div class="d-flex justify-content-between align-items-center flex-wrap group-10">
                            <div class="h4"><i class="fa fa-question-circle text-warning" aria-hidden="true"></i> Помощь</div>
                        </div>
                    </div>
                    <div class="panel-body">
                        <?=$arResult['DATA']['UF_TEXT']?>
                    </div>
                </div>
            </div>
        </div>
    

    <div class="close-button"><a href="#"><i class="fa fa-times" aria-hidden="true"></i>
        </a></div>
        </div>
</section>
<?
$COOKIE_PREFIX = \COption::GetOptionString("main", "cookie_name", "BITRIX_SM");
if ($COOKIE_PREFIX == '')
$COOKIE_PREFIX = "BX";
?>
<script>
    new pagehelp(<?=CUtil::PhpToJSObject([
        "CODE"=>$arParams["CODE"],
        "CONTAINER_ID" => "block".$uid,
        "cookie_prefix" => $COOKIE_PREFIX,
    ], false, true)?>);
</script>
