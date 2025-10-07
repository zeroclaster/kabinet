<?
use Bitrix\Main\Localization\Loc as Loc;

if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();
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

$sL = \Bitrix\Main\DI\ServiceLocator::getInstance();
$runnerManager = $sL->get('Kabinet.Runner');


$SEARCH_RESULT = $arResult['SEARCH_RESULT'];

// for debugg!
//\Dbg::print_r($SEARCH_RESULT);

Loc::loadMessages(__FILE__);
$this->setFrameMode(true);
?>

<form action="" name="filterform1" enctype="multipart/form-data" method="post">
    <div class="row justify-content-md-center">
        <div class="col-md-6">
            <div class="row form-group">
                <div class="col-sm-3 text-sm-right">
                    <label class="col-form-label col-form-label-sm" for="search-client">Клиент</label>
                </div>
                <div class="col-sm-9">
                    <input id="clientidsearch" name="clientidsearch" type="hidden">
                    <input value="<?=$SEARCH_RESULT['clienttextsearch']?>" name="clienttextsearch" id="search-client" class="form-control form-control-sm" type="text" placeholder="начните вводить или выберите из списка" data-typehead=''>
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="row form-group">
                <div class="col-sm-3 text-sm-right">
                    <label class="col-form-label col-form-label-sm" for="search-project">Проект</label>
                </div>
                <div class="col-sm-9">
                    <input id="projectidsearch" name="projectidsearch" type="hidden">
                    <input value="<?=$SEARCH_RESULT['projecttextsearch']?>" name="projecttextsearch" id="search-project" class="form-control form-control-sm" type="text" placeholder="начните вводить или выберите из списка" data-typehead='[]'>
                </div>
            </div>
        </div>
    </div>
    <div class="row justify-content-md-center">
        <div class="col-md-6">
            <div class="row form-group">
                <div class="col-sm-3 text-sm-right">
                    <label class="col-form-label col-form-label-sm" for="search-task">Задачи</label>
                </div>
                <div class="col-sm-9">
                    <input id="taskidsearch" name="taskidsearch" type="hidden">
                    <input value="<?=$SEARCH_RESULT['tasktextsearch']?>" name="tasktextsearch" id="search-task" class="form-control form-control-sm" type="text" placeholder="начните вводить или выберите из списка" data-typehead='[]'>
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="row form-group">
                <div class="col-sm-8 text-sm-right">
                    <label class="col-form-label col-form-label-sm" for="search-executionid">Найти исполнение, #</label>
                </div>
                <div class="col-sm-4">
                    <input id="executionidsearch" name="executionidsearch" type="hidden">
                    <input value="<?=$SEARCH_RESULT['executiontextsearch']?>" name="executiontextsearch" id="search-executionid" class="form-control form-control-sm" type="text" placeholder="введите номер">
                </div>
            </div>
        </div>
    </div>

    <div class="row justify-content-md-center">
        <div class="col-md-6">
            <div class="row form-group">
                <div class="col-sm-3 text-sm-right">
                    <label class="col-form-label col-form-label-sm" for="search-statusexecution">Со статусом:</label>
                </div>
                <div class="col-sm-9">

                    <div class="status-checkboxes" style="margin-bottom: 10px;">
                        <?foreach ($runnerManager->getStatusList() as $idstatus => $titlestatus):?>
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox"
                                       name="statusexecutionsearch[]"
                                       value="<?=$idstatus?>"
                                       id="status-<?=$idstatus?>"
                                       <?if(is_array($SEARCH_RESULT['statusexecutionsearch']) && in_array($idstatus, $SEARCH_RESULT['statusexecutionsearch'])):?>checked<?endif;?>
                                       <?if(!is_array($SEARCH_RESULT['statusexecutionsearch']) && is_numeric($SEARCH_RESULT['statusexecutionsearch']) && $SEARCH_RESULT['statusexecutionsearch'] == $idstatus):?>checked<?endif;?>>
                                <label class="form-check-label" for="status-<?=$idstatus?>" style="font-size: 0.875rem;">
                                    <?=$titlestatus?>
                                </label>
                            </div>
                        <?endforeach;?>
                    </div>

                </div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="row form-group">
                <div class="col-sm-4 text-sm-right">
                    <label class="col-form-label col-form-label-sm" for="search-planedaterangefrom">Плановая дата публикации</label>
                </div>
                <div class="col-sm-8">
                    <div class="d-flex">
                        <div>
                            <input value="<?=$SEARCH_RESULT['planedaterangesearchfrom']?>" name="planedaterangesearchfrom" id="search-planedaterangefrom" class="form-control form-control-sm" type="text" style="width: 123px;">
                        </div>
                        <div class="d-flex align-items-center ml-3 mr-3"> - </div>
                       <div>
                            <input value="<?=$SEARCH_RESULT['planedaterangesearchto']?>" name="planedaterangesearchto" id="search-planedaterangeto" class="form-control form-control-sm" type="text" style="width: 123px;">
                       </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row justify-content-md-center">
        <div class="col-md-6">
            <div class="row form-group">
                <div class="col-sm-3 text-sm-right">
                </div>
                <div class="col-sm-9">
                <ul class="list-unstyled alert-filter-block">
                    <li class="text-primary">
                        <input id="adminattention" type="radio" name="attention" value="adminattention" <?if($SEARCH_RESULT['attention'] == 'adminattention') echo "checked"?>>
                        <label class="btn btn-link" style="padding: 0" for="adminattention">Требует внимания администратора</label>
                    </li>
                    <li class="text-primary">
                        <input id="clientattention" type="radio" name="attention" value="clientattention" <?if($SEARCH_RESULT['attention'] == 'clientattention') echo "checked"?>>
                        <label class="btn btn-link" style="padding: 0" for="clientattention">Требует внимания клинета</label>
                    </li>
                    <li class="text-primary">
                        <input id="hitchstade" type="radio" name="attention" value="hitchstade" <?if($SEARCH_RESULT['attention'] == 'hitchstade') echo "checked"?>>
                        <label class="btn btn-link" style="padding: 0" for="hitchstade">С просроченными стадиями</label>
                    </li>
					<?/*
                    <li class="text-primary">
                        <input id="futurehitch" type="radio" name="attention" value="futurehitch" <?if($SEARCH_RESULT['attention'] == 'futurehitch') echo "checked"?>>
                        <label class="btn btn-link" style="padding: 0" for="futurehitch">Будут просрочены в течение    3   дней</label>
                    </li>
					*/?>
                </ul>
                </div>
            </div>


                    <div class="row form-group">
                        <div class="col-sm-3 text-sm-right">
                            <label class="col-form-label col-form-label-sm" for="search-responsible">Ответственный</label>
                        </div>
                        <div class="col-sm-9">
                            <input id="responsibleidsearch" name="responsibleidsearch" type="hidden">
                            <input value="<?=$SEARCH_RESULT['responsibletextsearch']?>" name="responsibletextsearch" id="search-responsible" class="form-control form-control-sm" type="text" placeholder="начните вводить или выберите из списка" data-typehead=''>
                        </div>
                    </div>



        </div>
        <div class="col-md-6">
            <div class="row form-group">
                <div class="col-sm-4 text-sm-right">
                    <label class="col-form-label col-form-label-sm" for="search-publicdatefrom">Дата публикации</label>
                </div>
                <div class="col-sm-8">
                    <div class="d-flex">
                        <div>
                            <input value="<?=$SEARCH_RESULT['publicdatefromsearch']?>" name="publicdatefromsearch" id="search-publicdatefrom" class="form-control form-control-sm" type="text" style="width: 123px;">
                        </div>
                        <div class="d-flex align-items-center ml-3 mr-3"> - </div>
                        <div>
                            <input value="<?=$SEARCH_RESULT['publicdatetosearch']?>" name="publicdatetosearch" id="search-publicdateto" class="form-control form-control-sm" type="text" style="width: 123px;">
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row justify-content-md-center">
        <div class="col-md-6">
        </div>
        <div class="col-md-6">
            <div class="row form-group">
                <div class="col-sm-4 text-sm-right">
                    <label class="col-form-label col-form-label-sm" for="search-account">Имя аккаунта</label>
                </div>
                <div class="col-sm-8">
                   <input value="<?=$SEARCH_RESULT['accountsearch']?>" name="accountsearch" id="search-account" class="form-control form-control-sm" type="text">
                </div>
            </div>
            <div class="row form-group">
                <div class="col-sm-4 text-sm-right">
                    <label class="col-form-label col-form-label-sm" for="search-login">Логин</label>
                </div>
                <div class="col-sm-8">
                    <input value="<?=$SEARCH_RESULT['loginsearch']?>" name="loginsearch" id="search-login" class="form-control form-control-sm" type="text">
                </div>
            </div>
            <div class="row form-group">
                <div class="col-sm-4 text-sm-right">
                    <label class="col-form-label col-form-label-sm" for="search-ip">IP размещения</label>
                </div>
                <div class="col-sm-8">
                    <input value="<?=$SEARCH_RESULT['ipsearch']?>" name="ipsearch" id="search-ip" class="form-control form-control-sm" type="text">
                </div>
            </div>
        </div>
    </div>


    <div class="row justify-content-md-center">
        <div class="col-md-8 text-center">
            <button type="submit" class="btn btn-primary mr-5">Показать</button>
            <button type="button" id="clearfilter" class="btn btn-outline-secondary">Сбросить фильтр</button>
        </div>
    </div>
</form>


<?
$jsParams = [
        'SEARCH_RESULT' => $arResult['SEARCH_RESULT']
];
?>
<script>
    // installe
    filter1.init(<?=CUtil::PhpToJSObject($jsParams, false, true)?>);
</script>