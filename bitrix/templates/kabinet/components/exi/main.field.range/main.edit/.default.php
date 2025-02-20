<?php

if(!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) die();

/**
 * @var DoubleUfComponent $component
 * @var array $arResult
 */
$component = $this->getComponent();

$vueField = $arResult["additionalParameters"]["VMODEFIELD"].'.'.$arResult["userField"]["FIELD_NAME"];
$vueFieldRESTRICTION = $arResult["additionalParameters"]["VMODEFIELD"].'.'.$arResult["userField"]["FIELD_NAME"] .'_RESTRICTION';
?>

<span class='fields range-gender-input field-wrap'>
	<?php
	foreach($arResult['value'] as $value)
	{
		?>
        {{(manval = percentMan(),null)}}
        {{(femaleval = percentFemale(),null)}}
        <div class="d-flex justify-content-between figure-block">
                <div class="figure-gender man-figure" :style="sizeMan()"><i class="fa fa-male" aria-hidden="true"></i></div>
                <div class="figure-gender female-figure" :style="sizeFemale()"><i class="fa fa-female" aria-hidden="true"></i></div>
            </div>
		<span class='fields integer field-item'>
			<input :style="split_percent()"
				<?= $component->getHtmlBuilder()->buildTagAttributes($value['attrList']) ?>
			>
		</span>
        <div class="d-flex justify-content-between">
            <div>{{manval}}</div><div>{{femaleval}}</div>
        </div>
		<?php
	}
	if(
		$arResult['userField']['MULTIPLE'] === 'Y'
		&&
		$arResult['additionalParameters']['SHOW_BUTTON'] !== 'N'
	)
	{
		print $component->getHtmlBuilder()->getCloneButton($arResult['fieldName']);
	}
	?>
</span>