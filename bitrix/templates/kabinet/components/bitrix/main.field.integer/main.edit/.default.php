<?php

if(!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) die();

/**
 * @var IntegerUfComponent $component
 * @var array $arResult
 */

$component = $this->getComponent();
?>

	<?php
	foreach($arResult['value'] as $value)
	{
		?>

			 <input
				 <?= $component->getHtmlBuilder()->buildTagAttributes($value['attrList']) ?>
			 >

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
