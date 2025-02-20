<?php

if(!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

use Bitrix\Main\Localization\Loc;
use Bitrix\Main\Text\HtmlFilter;
use Bitrix\Main\UserField\Types\BaseType;
use Bitrix\Main\UserField\Types\EnumType;

/**
 * @var EnumUfComponent $component
 * @var array $arResult
 */
$component = $this->getComponent();
$isMultiple = $arResult['isMultiple'];

//$arResult['attrList']['class'] = 'dual-listbox';
$arResult['attrList']['data-placeholder'] = 'Начните вводить или выберите из списка';


//\Dbg::var_dump($arResult['attrList']);

$arResult['attrList']["class"] = $arResult['attrList']["class"].' select2';
?>
	<?php
	if ($arResult['isEnabled'])
	{
		$multipleClass = ($isMultiple ? '-multiselect' : '-select');

			?>
				<select
					<?= $component->getHtmlBuilder()->buildTagAttributes($arResult['attrList']) ?>
					<?if($arResult["userField"]['MANDATORY'] == 'Y'):?>
						<?
						$id_ = $arResult["userField"]["ENTITY_ID"]."_".$arResult["userField"]["FIELD_NAME"];
						?>
					<?endif;?>
				>
				<?php
				if(
					isset($arResult['userField']['USER_TYPE']['FIELDS'])
					&& is_array($arResult['userField']['USER_TYPE']['FIELDS'])
				)
				{
					$isWasSelect = false;
					foreach($arResult['userField']['USER_TYPE']['FIELDS'] as $key => $val)
					{
						$isSelected = (in_array($key, $arResult['value']) && (!$isWasSelect || $isMultiple));
						$isWasSelect = $isWasSelect || $isSelected;
						?>
						<option
							value="<?= HtmlFilter::encode($key) ?>"
							<?= ($isSelected ? ' selected="selected"' : '') ?>
						><?= $val ?></option>
						<?php
					}
				}
				?>
				</select>

			<?php
	}
	else
	{
		$arResult['additionalParameters']['mode'] = BaseType::MODE_VIEW;
		$arResult['additionalParameters']['showInputs'] = true;
		$field = new \Bitrix\Main\UserField\Renderer($arResult['userField'], $arResult['additionalParameters']);
		print $field->render();
	}
	?>
