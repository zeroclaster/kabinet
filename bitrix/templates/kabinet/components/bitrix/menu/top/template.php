<?if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();?>


<?
//print_r($arResult);
?>

<?if (!empty($arResult)):?>
<ul class="rd-navbar-nav">

<?
$previousLevel = 0;
foreach($arResult as $arItem):?>

	<?if ($previousLevel && $arItem['PARAMS']["DEPTH_LEVEL"] < $previousLevel):?>
		<?=str_repeat("</ul></li>", ($previousLevel - $arItem['PARAMS']["DEPTH_LEVEL"]));?>
	<?endif?>

	<?if ($arItem['PARAMS']["IS_PARENT"]):?>

		<?if ($arItem['PARAMS']["DEPTH_LEVEL"] == 1):?>
			<li class="rd-navbar-nav-item <?if ($arItem["SELECTED"]):?>active<?else:?><?endif?> item-opened opened">
                <a <?if (!isset($arItem['PARAMS']['NOLINK'])):?>href="<?=$arItem["LINK"]?>"<?endif;?> class="rd-navbar-link rd-navbar-run-drop">
                        <?if($arItem['PARAMS']['ICON']):?><span class="rd-navbar-icon <?=$arItem['PARAMS']['ICON']?>"></span><?endif;?>
                        <span class="rd-navbar-text"><?=$arItem["TEXT"]?></span>
                </a>
				<ul class="rd-navbar-dropdown">
		<?else:?>
			<li class="rd-navbar-dropdown-item <?if ($arItem["SELECTED"]):?> active<?endif?>">
                    <a <?if (!isset($arItem['PARAMS']['NOLINK'])):?>href="<?=$arItem["LINK"]?>"<?endif;?> class="rd-navbar-link">
                        <span class="rd-navbar-text"><?=$arItem["TEXT"]?></span>
                    </a>
				<ul class="rd-navbar-dropdown">
		<?endif?>

	<?else:?>

		<?if ($arItem["PERMISSION"] > "D"):?>

			<?if ($arItem['PARAMS']["DEPTH_LEVEL"] == 1):?>
				<li class="rd-navbar-nav-item <?if ($arItem["SELECTED"]):?>active<?else:?><?endif?>">
                    <a <?if (!isset($arItem['PARAMS']['NOLINK'])):?>href="<?=$arItem["LINK"]?>"<?endif;?> class="rd-navbar-link">
                        <?if($arItem['PARAMS']['ICON']):?><span class="rd-navbar-icon <?=$arItem['PARAMS']['ICON']?>"></span><?endif;?>
                        <?
                        if ($arItem['PARAMS']['IMAGE']) echo '<img src="' . $arItem['PARAMS']['IMAGE'] . '" alt="" style="width: 20px; height: 20px; margin-right: 5px;">';
                        ?>
                        <span class="rd-navbar-text"><?=$arItem["TEXT"]?></span>
                    </a>
                </li>
			<?else:?>
				<li class="rd-navbar-dropdown-item <?if ($arItem["SELECTED"]):?> active<?endif?>">
                    <?
                    $class = '';
                    if ($arItem['PARAMS']['ACTION'] === 'add_service') $class = 'menu-item-add-service';
                    ?>
                    <a <?if (!isset($arItem['PARAMS']['NOLINK'])):?>href="<?=$arItem["LINK"]?>"<?endif;?> class="rd-navbar-link <?=$class?>">
                        <?if($arItem['PARAMS']['ICON']):?><span class="rd-navbar-icon <?=$arItem['PARAMS']['ICON']?>"></span><?endif;?>
                        <?
                        if ($arItem['PARAMS']['IMAGE']) echo '<img src="' . $arItem['PARAMS']['IMAGE'] . '" alt="" style="width: 20px; height: 20px; margin-right: 5px;">';
                        ?>
                        <span class="rd-navbar-text"><?=$arItem["TEXT"]?></span>
                    </a>
                </li>
			<?endif?>

		<?else:?>

			<?if ($arItem['PARAMS']["DEPTH_LEVEL"] == 1):?>
				<li class="rd-navbar-nav-item <?if ($arItem["SELECTED"]):?>active<?else:?><?endif?>"><a href="" class="rd-navbar-link" title="<?=GetMessage("MENU_ITEM_ACCESS_DENIED")?>"><?if($arItem['PARAMS']['ICON']):?><span class="rd-navbar-icon <?=$arItem['PARAMS']['ICON']?>"></span><?endif;?><span class="rd-navbar-text"><?=$arItem["TEXT"]?></span></a></li>
			<?else:?>
				<li class="rd-navbar-dropdown-item <?if ($arItem["SELECTED"]):?> active<?endif?>"><a href="" class="rd-navbar-link denied" title="<?=GetMessage("MENU_ITEM_ACCESS_DENIED")?>"><span class="rd-navbar-text"><?=$arItem["TEXT"]?></span></a></li>
			<?endif?>

		<?endif?>

	<?endif?>

	<?$previousLevel = $arItem['PARAMS']["DEPTH_LEVEL"];?>

<?endforeach?>

<?if ($previousLevel > 1)://close last item tags?>
	<?=str_repeat("</ul></li>", ($previousLevel-1) );?>
<?endif?>

</ul>
<?endif?>

<script>
    // Добавьте этот код в шаблон меню или в footer
    document.addEventListener('DOMContentLoaded', function() {
        // Обработчик для кнопок "Заказать услугу"
        const addServiceLinks = document.querySelectorAll('.menu-item-add-service');

        addServiceLinks.forEach(link => {
            link.addEventListener('click', function(e) {
                e.preventDefault();

                    // Создаем скрытую форму
                    const form = document.createElement('form');
                    form.method = 'POST';
                    form.action = this.href;

                    // Добавляем скрытое поле с action
                    const actionInput = document.createElement('input');
                    actionInput.type = 'hidden';
                    actionInput.name = 'action';
                    actionInput.value = 'add_service';
                    form.appendChild(actionInput);

                    // Добавляем форму в документ и отправляем
                    document.body.appendChild(form);
                    form.submit();

            });
        });
    });
</script>
