<?php

if(!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) die();

use Bitrix\Main\UserField\Types\BooleanType;
use Bitrix\Main\Localization\Loc;
use Bitrix\Main\Text\HtmlFilter;

/**
 * @var BooleanUfComponent $component
 * @var array $arResult
 */
$component = $this->getComponent();
$value = $arResult['value'];



// for debug
//echo "<pre>";
//var_dump($arResult);
//echo "</pre>";


$arResult['userField']['SETTINGS']['DISPLAY'] = BooleanType::DISPLAY_RADIO;
?>


		<?php
		if($arResult['userField']['SETTINGS']['DISPLAY'] === BooleanType::DISPLAY_DROPDOWN)
		{
			?>
			<select
				class="fields boolean"
				name="<?= $arResult['fieldName'] ?>"
			>
				<?php
				foreach($arResult['valueList'] as $key => $title)
				{
					?>
					<option
						value="<?= (int)$key ?>"
						<?= ($value === $key ? ' selected="selected"' : '') ?>
					><?= htmlspecialcharsbx($title) ?></option>
					<?php
				}
				?>
			</select>
			<?php
		}
		else if($arResult['userField']['SETTINGS']['DISPLAY'] === BooleanType::DISPLAY_CHECKBOX)
		{
			$label = Loc::getMessage('MAIN_YES');
			if (!empty($arResult['userField']['EDIT_FORM_LABEL']))
			{
				$label = $arResult['userField']['EDIT_FORM_LABEL'];
			}
			elseif(isset($arResult['userField']['SETTINGS']['LABEL_CHECKBOX']))
			{
				if(is_array($arResult['userField']['SETTINGS']['LABEL_CHECKBOX']))
				{
					$arResult['userField']['SETTINGS']['LABEL_CHECKBOX'] =
						$arResult['userField']['SETTINGS']['LABEL_CHECKBOX'][LANGUAGE_ID];
				}

				if($arResult['userField']['SETTINGS']['LABEL_CHECKBOX'] !== '')
				{
					$label = $arResult['userField']['SETTINGS']['LABEL_CHECKBOX'];
				}
			}
			?>
			<div class="custom-control custom-switch">
                          <input class="custom-control-input" name="<?= $arResult['fieldName'] ?>" type="checkbox" id="<?=$arResult['additionalParameters']["attribute"]["id"]?>" <?= $value ? ' checked' : '' ?>/>
                          <label class="custom-control-label" for="<?=$arResult['additionalParameters']["attribute"]["id"]?>"></label>
           </div>			
			<input
				class="fields boolean"
				type="hidden"
				value="0"
				name="<?= $arResult['fieldName'] ?>"
			>
			<?php
		}
		else if($arResult['userField']['SETTINGS']['DISPLAY'] === BooleanType::DISPLAY_RADIO)
		{
			$first = true;
			foreach($arResult['valueList'] as $key => $title)
			{
				if($first)
				{
					$first = false;
				}
				elseif($arResult['userField']['SETTINGS']['MULTIPLE'] === 'N')
				{
					print $component->getHtmlBuilder()->getMultipleValuesSeparator();
				}
				?>
				<label>
					<input
						type="radio"
						class="fields boolean"
						value="<?= (int)$key ?>"
						name="<?= $arResult['fieldName'] ?>"
						<?= ($value === $key ? ' checked="checked"' : '') ?>
					/>
					<?= htmlspecialcharsbx($title) ?>
				 </label>
				<?php
			}
		}
		?>
