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
//var_dump($arResult['fieldValues']);
//echo "</pre>";
?>

<?/*
<input class="form-control" id="standardInput" type="text" placeholder="назовите Ваш проект...">
*/?>
<?php
foreach($arResult['fieldValues'] as $value)
{
    ?>
            <?php if($value['tag'] === 'input'): ?>
                <input
                    <?= $component->getHtmlBuilder()->buildTagAttributes($value['attrList'])?>
                >
            <?php else: ?>
            <div class="richtext-height-200">
                <richtext :original="<?=$value['attrList']['original'] ?>" v-model="<?=$value['attrList']["v-model"] ?>"/>
            </div>

<?/*
                <textarea
					<?= $component->getHtmlBuilder()->buildTagAttributes($value['attrList']) ?>
    data-ckeditor
    data-ckeditor-field="<?=$arResult["userField"]['ID']?>"
    data-ckeditor-object="project"
    ><?= HtmlFilter::encode($value['attrList']['value']) ?></textarea>
*/?>

            <?php endif; ?>
    <?php
}

if(
    isset($arResult['userField']['MULTIPLE'])
    && $arResult['userField']['MULTIPLE'] === 'Y'
    &&
    (
        !isset($arResult['additionalParameters']['SHOW_BUTTON'])
        || $arResult['additionalParameters']['SHOW_BUTTON'] !== 'N'
    )
)
{
    print $component->getHtmlBuilder()->getCloneButton($arResult['fieldName']);
}
?>
