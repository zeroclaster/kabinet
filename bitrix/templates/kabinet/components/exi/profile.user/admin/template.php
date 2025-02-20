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

Loc::loadMessages(__FILE__);
$this->setFrameMode(true);

$sL = \Bitrix\Main\DI\ServiceLocator::getInstance();
$ClientManager = $sL->get('Kabinet.Client');
?>

<div id="kabinetcontent" class="form-group">
</div>

<script type="text/html" id="kabinet-content">
        <div class="panel user-profile-form" v-if="datauser">
            <div class="panel-header">
                <div class="d-flex justify-content-between align-items-center flex-wrap group-10">
                </div>
            </div>
            <div class="panel-body">
                <div class="d-flex justify-content-center">
                    <div class="personal-photo" style="width: 300px;">
                        <img :src="datauser.PERSONAL_PHOTO_ORIGINAL_300x300.src" :alt="datauser.PRINT_NAME" class="img-thumbnail">
                        <div class="profile-edit-photo">
                            <i class="fa fa-pencil" aria-hidden="true"></i>
                            <input type="file" @change="onChangeFile">
                        </div>
                    </div>
                </div>

                <div class="row justify-content-md-center">
                   <div class="col-md-7">
                       <div class="mt-5">
                           <div class="h3">Контактная информация</div>
                           <div class="row form-group">
                               <div class="col-sm-3 text-sm-right">
                                   <label class="col-form-label" :for="$id('name')">Имя</label>
                               </div>
                               <div class="col-sm-9">
                                   <input class="form-control" :id="$id('name')" type="text" placeholder="" v-model="datauser.NAME">
                               </div>
                           </div>
                           <div class="row form-group">
                               <div class="col-sm-3 text-sm-right">
                                   <label class="col-form-label" :for="$id('surname')">Фамилия</label>
                               </div>
                               <div class="col-sm-9">
                                   <input class="form-control" :id="$id('surname')" type="text" placeholder="" v-model="datauser.SECOND_NAME">
                               </div>
                           </div>
                           <div class="row form-group">
                               <div class="col-sm-3 text-sm-right">
                                   <label class="col-form-label" :for="$id('phone')">Телефон</label>
                               </div>
                               <div class="col-sm-9">
                                   <input class="form-control" :id="$id('phone')" type="text" placeholder="" v-model="datauser.PERSONAL_PHONE">
                               </div>
                           </div>
                           <div class="row form-group">
                               <div class="col-sm-3 text-sm-right">
                                   <label class="col-form-label" :for="$id('email')">Email</label>
                               </div>
                               <div class="col-sm-9">
                                   <input class="form-control" :id="$id('email')" type="text" placeholder="" v-model="datauser.EMAIL">
                               </div>
                           </div>

                           <div class="form-group text-center">
                               <button type="button" class="btn btn-primary" @click="savefields">Сохранить</button>
                           </div>
                       </div>


                       <div class="mt-5">
                           <div class="h3">Изменить пароль</div>
                           <div class="row form-group">
                               <div class="col-sm-3 text-sm-right">
                                   <label class="col-form-label" :for="$id('newpass')">Новый пароль</label>
                               </div>
                               <div class="col-sm-9">
                                   <input class="form-control" :id="$id('newpass')" type="password" placeholder="" v-model="password.one">
                               </div>
                           </div>
                           <div class="row form-group">
                               <div class="col-sm-3 text-sm-right">
                                   <label class="col-form-label" :for="$id('newpass')">Повторите новый пароль</label>
                               </div>
                               <div class="col-sm-9">
                                   <input class="form-control" :id="$id('newpass')" type="password" placeholder="" v-model="password.two">
                               </div>
                           </div>

                           <div class="form-group text-center">
                               <button type="button" class="btn btn-primary" @click="savepassword">Сохранить</button>
                           </div>
                       </div>

                   </div>
                </div>

            </div>
        </div>


        <div class="alert alert-danger" role="alert" v-if="!datauser">
            Нет данных о пользователе!
        </div>

</script>


<?ob_start();?>

<script type="text/javascript" src="<?=$templateFolder?>/user.data.php"></script>
<script type="text/javascript" src="<?=$templateFolder?>/profile_user.js"></script>

<script>
    window.addEventListener("components:ready", function(event) {
    profile_user.start(<?=CUtil::PhpToJSObject([

    ], false, true)?>);
    });
</script>


<?
$addScriptinPage = trim(ob_get_contents());
ob_end_clean();
$addscript = (\KContainer::getInstance())->get('addscript');
if (!$addscript) $addscript = [];
$addscript[] = $addScriptinPage;
(\KContainer::getInstance())->maked($addscript,'addscript');
?>
