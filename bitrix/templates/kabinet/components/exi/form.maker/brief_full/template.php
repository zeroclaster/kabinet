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

use Bitrix\Main\Localization\Loc as Loc;


Loc::loadMessages(__FILE__);
$this->setFrameMode(true);

// for debug!!
//echo "<pre>";
//print_r($arResult);
//echo "</pre>";


//echo "<pre>";
//print_r($arResult['additionals']);
//echo "</pre>";

if(!$_REQUEST['id'])
    $action = 'create';
else
    $action = 'edit';

$sL = \Bitrix\Main\DI\ServiceLocator::getInstance();
$projectManager = $sL->get('Kabinet.Project');

$testFields =  array (
    'HLBLOCK_4_UF_NAME' => 'iuytiuytiuy',
    'HLBLOCK_8_UF_TOPICS_LIST' => 
    array (
      0 => '',
      1 => '1',
      2 => '2',
    ),
    'HLBLOCK_8_UF_PROJECT_GOAL' => 'ytruyrtuyrtuyt',
    'HLBLOCK_8_UF_SITE' => '',
    'HLBLOCK_8_UF_OFFICIAL_NAME' => '',
    'HLBLOCK_8_UF_REVIEWS_NAME' => '',
    'HLBLOCK_8_UF_CONTACTS_PUBLIC' => '',
    'HLBLOCK_8_UF_COMP_PREVIEW_TEXT' => '',
    'HLBLOCK_8_UF_COMP_DESCRIPTION_TEXT' => '',
    'HLBLOCK_8_UF_ORG_ADDRESS' => '',
    'HLBLOCK_8_UF_WORKING_HOURS' => '',
    'HLBLOCK_9_UF_ABOUT_REVIEW' => '',
    'HLBLOCK_9_UF_POSITIVE_SIDES' => 
    array (
      0 => 'ytruytruyrt',
      1 => '6798769876',
      2 => '769876ytiuy',
    ),
    'HLBLOCK_9_UF_MINUSES' => 
    array (
      0 => '',
    ),
    'HLBLOCK_9_UF_ORDER_PROCESS' => 
    array (
      0 => '',
    ),
    'HLBLOCK_9_UF_EXAMPLES_REVIEWS' => '',
    'HLBLOCK_9_UF_MENTION_REVIEWS' => 
    array (
      0 => '',
    ),
    'HLBLOCK_9_UF_KEYWORDS' => '',
    'HLBLOCK_12_UF_TARGET_AUDIENCE' => '',
    'HLBLOCK_12_UF_COUNTRY' => '',
    'HLBLOCK_12_UF_REGION' => '',
    'HLBLOCK_12_UF_CITY' => '',
    'HLBLOCK_12_UF_RATIO_GENDERS' => '',
    'HLBLOCK_4_UF_ADDITIONAL_WISHES' => '',
  );


// Берем основные поля объекта
$f = $projectManager->retrieveOriginalFields($testFields);
// Берем дополнителеные поля объекта
$f2 = $projectManager->retrieveAdditionalsFields($testFields,PROJECTSINFO);
$f3 = $projectManager->retrieveAdditionalsFields($testFields,PROJECTSDETAILS);
$f4 = $projectManager->retrieveAdditionalsFields($testFields,TARGETAUDIENCE);

//$res = $projectManager->add(array_merge($f,$f2,$f3,$f4));

//$projectManager->delete(10);

//echo "<pre>";
//var_dump($arResult);
//echo "</pre>";
?>

<div class="panel">
    <div class="panel-header">
        <h4 class="panel-title"></h4>
    </div>
    <div class="panel-body">

<form @submit="checkForm"
      <?if($_REQUEST['id']) echo ":set=\"id={$_REQUEST['id']}\"";?>
      :set2="action='<?=$action?>'"
      action=""
      method="post"
      enctype="multipart/form-data"
>

    <? foreach ($arParams['GROUPS'] as $key => $GROUP_TITLE):?>
        <div class="form-group">
            <div class="text-center h3 mb-3 mt-5"><?=$GROUP_TITLE?></div>
        </div>
        <? foreach ($arParams['GROUP'.$key] as $fieldParams):?>
            <div class="row form-group">
                <div class="col-sm-3 text-sm-right">
                    <label class="col-form-label" for="<?=$component->makeId($fieldParams)?>"><?=$fieldParams['FIELD_TITLE_VIEW']?></label>
                </div>
                <div class="col-sm-6"><?=$fieldParams['PUBLIC_EDIT']?></div>
                <div class="col-sm-3 form-help-message"><?=$fieldParams["HELP_MESSAGE"]?></div>
            </div>

        <?endforeach;?>
    <?endforeach;?>


    <div class="row form-group">
        <div class="col-sm-3 text-sm-right">
        </div>
        <div class="col-sm-6">
            <input  class="btn btn-block btn-primary" id="standardInput" type="submit">
        </div>
    </div>

</form>

    </div>
</div>

<?
$fieldsToJS = ['fields'=>[]];
foreach ($arParams['GROUPS'] as $key => $GROUP_TITLE){
	foreach ($arParams['GROUP'.$key] as $fieldParams){
			
		$id_ = $component->makeId($fieldParams);
		//$required = ($fieldParams['MANDATORY'] == 'Y')? true:false;
		
		//if($fieldParams["MULTIPLE"] == 'Y') $id_ = $id_."[]";

        $REGEXP_Clear = "";
        $REGEXP = $fieldParams['SETTINGS']['REGEXP'];
        if ($REGEXP) {
            preg_match('#^\\/(.*)\\/#isu', $REGEXP, $matches);
            if ($matches) {
                $REGEXP_Clear = $matches[1];
            }
        }
		$fieldsToJS['fields'][$id_] = [
            'value'=>$fieldParams["VALUE"],
            'preg'=>$REGEXP_Clear,
            'required'=>($fieldParams['MANDATORY'] == 'Y')? true:false,
            'EDIT_FORM_LABEL'=>$fieldParams['EDIT_FORM_LABEL']
        ];
	}
}

// for debug!
//\Dbg::print_r($fieldsToJS);


$brief_state = CUtil::PhpToJSObject($fieldsToJS, false, true);
?>
<?ob_start();?>
<script>
const briefFormStoreData = <?=$brief_state?>;
</script>
<script type="text/javascript" src="/bitrix/templates/kabinet/assets/js/kabinet/brief_form.js?17097084012751"></script>
<?
//\Bitrix\Main\Page\Asset::getInstance()->addJs(SITE_TEMPLATE_PATH."/assets/js/kabinet/brief_form.js");
?>
<?
$addScriptinPage = trim(ob_get_contents());
ob_end_clean();
$addscript = (\KContainer::getInstance())->get('addscript');
if (!$addscript) $addscript = [];
$addscript[] = $addScriptinPage;
(\KContainer::getInstance())->maked($addscript,'addscript');
?>		