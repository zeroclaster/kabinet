<?if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();?>

<?if (!empty($arResult)):?>
<ul class="nav flex-column">

<?
$previousLevel = 0;
foreach($arResult as $arItem):?>

	<?if ($previousLevel && $arItem["DEPTH_LEVEL"] < $previousLevel):?>
		<?=str_repeat("</ul></li>", ($previousLevel - $arItem["DEPTH_LEVEL"]));?>
	<?endif?>

	<?if ($arItem["IS_PARENT"]):?>

		<?if ($arItem["DEPTH_LEVEL"] == 1):?>
			<li class="nav-item"><a href="<?=$arItem["LINK"]?>" class="nav-link <?if ($arItem["SELECTED"]):?>active<?else:?><?endif?>"><span data-feather="home" class="align-text-bottom"></span><?=$arItem["TEXT"]?></a>
				<ul class="root-item">
		<?else:?>
			<li class="nav-item"><a href="<?=$arItem["LINK"]?>" class="nav-link  <?if ($arItem["SELECTED"]):?> active<?endif?>"><span data-feather="home" class="align-text-bottom"></span><?=$arItem["TEXT"]?></a>
				<ul>
		<?endif?>

	<?else:?>

		<?if ($arItem["PERMISSION"] > "D"):?>

			<?if ($arItem["DEPTH_LEVEL"] == 1):?>
				<li class="nav-item"><a href="<?=$arItem["LINK"]?>" class="nav-link <?if ($arItem["SELECTED"]):?>active<?else:?>r<?endif?>"><span data-feather="home" class="align-text-bottom"></span><?=$arItem["TEXT"]?></a></li>
			<?else:?>
				<li class="nav-item"><a href="<?=$arItem["LINK"]?>" <?if ($arItem["SELECTED"]):?> class="nav-link active"<?endif?>><span data-feather="home" class="align-text-bottom"></span><?=$arItem["TEXT"]?></a></li>
			<?endif?>

		<?else:?>

			<?if ($arItem["DEPTH_LEVEL"] == 1):?>
				<li class="nav-item"><a href="" class="nav-link <?if ($arItem["SELECTED"]):?>active<?else:?>root-item<?endif?>" title="<?=GetMessage("MENU_ITEM_ACCESS_DENIED")?>"><span data-feather="home" class="align-text-bottom"></span><?=$arItem["TEXT"]?></a></li>
			<?else:?>
				<li class="nav-item"><a href="" class="nav-link denied" title="<?=GetMessage("MENU_ITEM_ACCESS_DENIED")?>"><span data-feather="home" class="align-text-bottom"></span><?=$arItem["TEXT"]?></a></li>
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