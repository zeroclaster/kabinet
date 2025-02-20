<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();
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
$this->setFrameMode(true);

$cssClass = "";
if($arParams["STYLE"] =='errortext') $cssClass = 'alert-danger';
if($arParams["STYLE"] =='notetext') $cssClass = 'alert-info';
?>

<section class="section-xs">
    <div class="container-fluid">
        <div class="row row-30">
            <div class="col-md-6">
                <?echo "<div class=\"alert {$cssClass}\">{$arParams["MESSAGE"]}</div>";?>
            </div>
        </div>
    </div>
</section>

