<?
use Bitrix\Main\Localization\Loc as Loc;
use Bitrix\Main\Page\Asset;

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
            <div class="panel-body">
                <div class="d-flex justify-content-center">
                    <div class="personal-photo" style="width: 300px;">
                        <div v-if="datauser.PERSONAL_PHOTO_ORIGINAL_300x300">
                            <img :src="datauser.PERSONAL_PHOTO_ORIGINAL_300x300.src" :alt="datauser.PRINT_NAME" class="img-thumbnail">
                        </div>
                        <div v-else>
                            <img :src="config.USER.photo_default" :alt="datauser.PRINT_NAME" class="img-thumbnail">
                        </div>
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
                                   <label class="col-form-label" :for="$id('last_name')">Фамилия</label>
                               </div>
                               <div class="col-sm-9">
                                   <input class="form-control" :id="$id('last_name')" type="text" placeholder="" v-model="datauser.LAST_NAME">
                               </div>
                           </div>
                           <div class="row form-group">
                               <div class="col-sm-3 text-sm-right">
                                   <label class="col-form-label" :for="$id('surname')">Отчество</label>
                               </div>
                               <div class="col-sm-9">
                                   <input class="form-control" :id="$id('surname')" type="text" placeholder="" v-model="datauser.SECOND_NAME">
                               </div>
                           </div>
                           <div class="row form-group">
                               <div class="col-sm-3 text-sm-right">
                                   <label class="col-form-label" :for="$id('jobtitle')">Должность</label>
                               </div>
                               <div class="col-sm-9">
                                   <input class="form-control" :id="$id('jobtitle')" type="text" placeholder="" v-model="datauser.PERSONAL_PROFESSION">
                               </div>
                           </div>
                           <div class="row form-group">
                               <div class="col-sm-3 text-sm-right">
                                   <label class="col-form-label" :for="$id('sitelink')">Адрес сайта</label>
                               </div>
                               <div class="col-sm-9">
                                   <input class="form-control" :id="$id('sitelink')" type="text" placeholder="" v-model="datauser.PERSONAL_WWW">
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
                           <div class="h3">Уведомления</div>
                           <div class="row form-group">
                               <div class="col-sm-3 text-sm-right">
                                   <label class="col-form-label" :for="$id('emailnotifi')">Получать уведомления по email</label>
                               </div>
                               <div class="col-sm-9">
                                   <select class="form-control" :id="$id('emailnotifi')" v-model="datauser.UF_EMAIL_NOTIFI">
                                       <option v-for="option in datauser.UF_EMAIL_NOTIFI_ORIGINAL" :value="option.ID">
                                           {{ option.VALUE }}
                                       </option>
                                   </select>
                               </div>
                           </div>

                           <div class="row form-group" v-if="datauser.UF_TELEGRAM_ID>0">
                               <div class="col-sm-3 text-sm-right">
                                   <label class="col-form-label" :for="$id('telegramnotifi')">Получать уведомления в Telegram</label>
                               </div>
                               <div class="col-sm-1">
                                   <input class="form-control" style="width: 50%;" type="checkbox" :id="$id('telegramnotifi')" v-model="datauser.UF_TELEGRAM_NOTFI"/>
                               </div>
                               <div class="col-sm-3">
                                   <button class="btn btn-primary" type="button" @click="UnlinkTelegram">Отвязать Telegram</button>
                               </div>
                           </div>
                           <div class="row form-group" v-else>
                               <div class="col-sm-3 text-sm-right">
                                   <label class="col-form-label" :for="$id('telegramnotifi')">Добавить аккаунт в Telegram</label>
                               </div>
                               <div class="col-sm-9" id="telegram-login-btn"></div>
                           </div>

                           <div class="form-group text-center">
                               <button type="button" class="btn btn-primary" @click="savefields">Сохранить</button>
                           </div>
                       </div>

<?//-------------------------------------------------------------------------------------------------------?>
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


                       <?
                       // TODO AKULA Настройка уведомлений
                       ?>
                       <?/*

                       <div class="mt-5">
                           <div class="h3">Настройка уведомлений</div>

                           <table>
                               <thead>
                                   <tr>
                                       <th></th>
                                       <th>Email</th>
                                       <th>Telegram</th>
                                   </tr>
                               </thead>
                               <tbody>
                               <tr>
                                   <td>Информация о расходах и пополнении баланса</td>
                                   <td></td>
                                   <td></td>
                               </tr>
                               <tr>
                                   <td>Уведомления о согласовании</td>
                                   <td></td>
                                   <td></td>
                               </tr>
                               <tr>
                                   <td>Уведомления о ходе выполнения проектов и сообщений администратора</td>
                                   <td></td>
                                   <td></td>
                               </tr>
                               </tbody>

                           </table>

                           <div class="form-group text-center">
                               <button type="button" class="btn btn-primary" @click="saveevent">Сохранить</button>
                           </div>


                       </div>
*/?>

                   </div>
                </div>




            </div>
        </div>


        <div class="alert alert-danger" role="alert" v-if="!datauser">
            Нет данных о пользователе!
        </div>

</script>


<?
(\KContainer::getInstance())->get('userStore');

Asset::getInstance()->addJs(SITE_TEMPLATE_PATH."/assets/js/kabinet/vue-componets/extension/addnewmethods.js");
Asset::getInstance()->addJs($templateFolder."/profile_user.js");
?>

<script>
    window.addEventListener("components:ready", function(event) {
    profile_user.start(<?=CUtil::PhpToJSObject([], false, true)?>);
    });
</script>

