<?if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();?>


<?
//print_r($arResult);
?>

<?if (!empty($arResult)):?>
<ul class="rd-navbar-nav">

<?
$previousLevel = 0;
foreach($arResult as $arItem):?>

	<?if ($previousLevel && $arItem["DEPTH_LEVEL"] < $previousLevel):?>
		<?=str_repeat("</ul></li>", ($previousLevel - $arItem["DEPTH_LEVEL"]));?>
	<?endif?>

	<?if ($arItem["IS_PARENT"]):?>

		<?if ($arItem["DEPTH_LEVEL"] == 1):?>
			<li class="rd-navbar-nav-item <?if ($arItem["SELECTED"]):?>active<?else:?><?endif?> item-opened opened"><a <?if (!isset($arItem['PARAMS']['NOLINK'])):?>href="<?=$arItem["LINK"]?>"<?endif;?> class="rd-navbar-link rd-navbar-run-drop"><?if($arItem['PARAMS']['ICON']):?><span class="rd-navbar-icon <?=$arItem['PARAMS']['ICON']?>"></span><?endif;?><span class="rd-navbar-text"><?=$arItem["TEXT"]?></span></a>
				<ul class="rd-navbar-dropdown">
		<?else:?>
			<li class="rd-navbar-dropdown-item <?if ($arItem["SELECTED"]):?> active<?endif?>"><a <?if (!isset($arItem['PARAMS']['NOLINK'])):?>href="<?=$arItem["LINK"]?>"<?endif;?> class="rd-navbar-link"><span class="rd-navbar-text"><?=$arItem["TEXT"]?></span></a>
				<ul class="rd-navbar-dropdown">
		<?endif?>

	<?else:?>

		<?if ($arItem["PERMISSION"] > "D"):?>

			<?if ($arItem["DEPTH_LEVEL"] == 1):?>
				<li class="rd-navbar-nav-item <?if ($arItem["SELECTED"]):?>active<?else:?><?endif?>"><a <?if (!isset($arItem['PARAMS']['NOLINK'])):?>href="<?=$arItem["LINK"]?>"<?endif;?> class="rd-navbar-link"><?if($arItem['PARAMS']['ICON']):?><span class="rd-navbar-icon <?=$arItem['PARAMS']['ICON']?>"></span><?endif;?><span class="rd-navbar-text"><?=$arItem["TEXT"]?></span></a></li>
			<?else:?>
				<li class="rd-navbar-dropdown-item <?if ($arItem["SELECTED"]):?> active<?endif?>"><a <?if (!isset($arItem['PARAMS']['NOLINK'])):?>href="<?=$arItem["LINK"]?>"<?endif;?> class="rd-navbar-link"><?if($arItem['PARAMS']['ICON']):?><span class="rd-navbar-icon <?=$arItem['PARAMS']['ICON']?>"></span><?endif;?><span class="rd-navbar-text"><?=$arItem["TEXT"]?></span></a></li>
			<?endif?>

		<?else:?>

			<?if ($arItem["DEPTH_LEVEL"] == 1):?>
				<li class="rd-navbar-nav-item <?if ($arItem["SELECTED"]):?>active<?else:?><?endif?>"><a href="" class="rd-navbar-link" title="<?=GetMessage("MENU_ITEM_ACCESS_DENIED")?>"><?if($arItem['PARAMS']['ICON']):?><span class="rd-navbar-icon <?=$arItem['PARAMS']['ICON']?>"></span><?endif;?><span class="rd-navbar-text"><?=$arItem["TEXT"]?></span></a></li>
			<?else:?>
				<li class="rd-navbar-dropdown-item <?if ($arItem["SELECTED"]):?> active<?endif?>"><a href="" class="rd-navbar-link denied" title="<?=GetMessage("MENU_ITEM_ACCESS_DENIED")?>"><span class="rd-navbar-text"><?=$arItem["TEXT"]?></span></a></li>
			<?endif?>

		<?endif?>

	<?endif?>

	<?$previousLevel = $arItem["DEPTH_LEVEL"];?>

<?endforeach?>

<?if ($previousLevel > 1)://close last item tags?>
	<?=str_repeat("</ul></li>", ($previousLevel-1) );?>
<?endif?>

</ul>
<?endif?>