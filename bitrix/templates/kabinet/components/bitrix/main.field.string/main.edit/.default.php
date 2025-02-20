<?php

if(!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) die();

use Bitrix\Main\Text\HtmlFilter;

/**
 * @var StringUfComponent $component
 * @var array $arResult
 */

$component = $this->getComponent();

// for debug
//echo "<pre>";
//var_dump($arResult["userField"]);
//echo "</pre>";

$MULTIPLE = isset($arResult['userField']['MULTIPLE']) && $arResult['userField']['MULTIPLE'] === 'Y';

if($arResult['userField']['SETTINGS']['ROWS'] < 2) $tag = 'input';
else $tag = 'textarea';

$vueField = $arResult["additionalParameters"]["VMODEFIELD"].'.'.$arResult["userField"]["FIELD_NAME"];
$vueFieldRESTRICTION = $arResult["additionalParameters"]["VMODEFIELD"].'.'.$arResult["userField"]["FIELD_NAME"] .'_RESTRICTION';
?>
<?if(!$MULTIPLE):?>

        <?php if($tag === 'input'): ?>
        <div class="mb-2">
				<input
					<?= $component->getHtmlBuilder()->buildTagAttributes($arResult["additionalParameters"]["attribute"])?>
				>
                <div class="limitcount" v-if="showlimitcount(<?=$arResult["additionalParameters"]["VMODEFIELDSETTINGS"]?>.<?=$arResult["userField"]["FIELD_NAME"]?>)">{{limitchars(<?=$vueField?>,<?=$arResult["additionalParameters"]["VMODEFIELDSETTINGS"]?>.<?=$arResult["userField"]["FIELD_NAME"]?>)}}</div>
        </div>
    <?php else: ?>
        <div class="mb-2 stretch-input-block" :data-value="<?=$vueField?>">
				<textarea
					<?= $component->getHtmlBuilder()->buildTagAttributes($arResult["additionalParameters"]["attribute"]) ?>

                    @input="(event) => event.target.parentNode.dataset.value = event.target.value"
				></textarea>
            <div class="limitcount" v-if="showlimitcount(<?=$arResult["additionalParameters"]["VMODEFIELDSETTINGS"]?>.<?=$arResult["userField"]["FIELD_NAME"]?>)">{{limitchars(<?=$vueField?>,<?=$arResult["additionalParameters"]["VMODEFIELDSETTINGS"]?>.<?=$arResult["userField"]["FIELD_NAME"]?>)}}</div>
        </div>
    <?php endif; ?>

<?else:?>
    <?
    unset($arResult["additionalParameters"]["attribute"]["v-model"]);
    ?>
    <div v-for="item in <?=$vueField?>" class="mb-2">
            <?php if($tag === 'input'): ?>
            <input
                <?= $component->getHtmlBuilder()->buildTagAttributes($arResult["additionalParameters"]["attribute"])?>
                v-model="item.VALUE"
            >
        <?php else: ?>
            <textarea
					<?= $component->getHtmlBuilder()->buildTagAttributes($arResult["additionalParameters"]["attribute"]) ?>
                    v-model="item.VALUE"
            ></textarea>
        <?php endif; ?>
    </div>

	<?if(!isset($arResult['additionalParameters']['SHOW_BUTTON']) || $arResult['additionalParameters']['SHOW_BUTTON'] !== 'N')
	{?>
        <button class="btn btn-primary btn-sm" type="button" @click="moreitems(<?=$vueField?>)">+</button>
        <?php
        /*
        print $component->getHtmlBuilder()->getCloneButton($arResult['fieldName']);
        */
        ?>

	<?
    }
	?>
<?endif;?>