<?
/**
 * Bitrix Framework
 * @package bitrix
 * @subpackage main
 * @copyright 2001-2014 Bitrix
 */

/**
 * Bitrix vars
 * @global CMain $APPLICATION
 * @var array $arParams
 * @var array $arResult
 * @var CBitrixComponentTemplate $this
 */

if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();

if($arResult["SHOW_SMS_FIELD"] == true)
{
	CJSCore::Init('phone_auth');
}

$arResult["USER_LOGIN"] = 'user_'.uniqid();

// В начале скрипта регистрации
if ($_GET['from'] == 'telegram' && !empty($_SESSION['TELEGRAM_REGISTER_DATA'])) {
    $telegramData = $_SESSION['TELEGRAM_REGISTER_DATA'];

    // Автозаполнение полей формы
    $arResult["USER_NAME"] = htmlspecialcharsbx($telegramData['first_name']);
    $arResult["USER_LAST_NAME"] = htmlspecialcharsbx($telegramData['last_name']);
    $arResult["USER_LOGIN"] = htmlspecialcharsbx($telegramData['username'] ?: 'tg_' . $telegramData['telegram_id']);

    // Можно добавить скрытое поле с telegram_id
    $telegramIdField = '<input type="hidden" name="UF_TELEGRAM_ID" value="' . $telegramData['telegram_id'] . '">';
}



?>
<div class="bx-auth">

<section class="section-lg section-one-screen">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-lg-10">

                <div class="text-center"><a href="/"><img class="logo-default" src="/bitrix/templates/main/images/logo_w.svg" alt="Купи отзыв" style="width: 300px;"></a></div>


                <?if (isMobileDevice()):?>
                <div class="row row-10 align-items-end">
                    <div class="col-sm-7"><a href="#"><img src="" alt=""></a></div>
                    <div class="col-sm-12 text-right">
                        <a href="/" rel="nofollow"><b>На главную</b></a><span class="px-2">|</span>
                        <a href="<?=$arResult["AUTH_AUTH_URL"]?>" rel="nofollow"><b>Уже регистрировались — войдите</b></a>
                    </div>
                </div>
                <?else:?>
                    <div class="row row-10 align-items-end">
                        <div class="col-6 col-sm-7"><a href="#"><img src="" alt=""></a></div>
                        <div class="col-6 col-sm-12 text-right">
                            <a href="/" rel="nofollow"><b>На главную</b></a><span class="px-2">|</span>
                            <a href="<?=$arResult["AUTH_AUTH_URL"]?>" rel="nofollow"><b>Уже регистрировались — войдите</b></a>
                        </div>
                    </div>
                <?endif;?>

                <div class="panel">
                    <div class="panel-header">
                        <h2>Регистрация</h2>
                    </div>
                    <div class="panel-body">

                        <?if(!isset($telegramData)):?>
                        <div class="row">
                            <div class="col-sm-3"></div>
                            <div class="col-sm-9">
                                            <div id="telegram-login-btn" style="margin: 0px;margin-bottom: 0px;"></div>
                                            <script>
                                                BX.ready(function() {
                                                    var script = document.createElement('script');
                                                    script.async = true;
                                                    script.src = "https://telegram.org/js/telegram-widget.js?22";
                                                    script.setAttribute('data-telegram-login', 'kupiotziv_bot');
                                                    script.setAttribute('data-size', 'large');
                                                    script.setAttribute('data-auth-url', '/auth/telegram.php');
                                                    script.setAttribute('data-request-access', 'write');
                                                    script.setAttribute('data-userpic', 'true'); // Отключаем аватарку
                                                    document.getElementById('telegram-login-btn').appendChild(script);
                                                });
                                            </script>
                            </div>
                        </div>
                        <?endif;?>

                        <?
                        if (!empty($arParams["~AUTH_RESULT"]))
                        {
                            ShowMessage($arParams["~AUTH_RESULT"]);
                        }
                        ?>

<?
if($arResult["SHOW_EMAIL_SENT_CONFIRMATION"])  echo "<p>".GetMessage("AUTH_EMAIL_SENT")."</p>";
if(
   !$arResult["SHOW_EMAIL_SENT_CONFIRMATION"] &&
   $arResult["USE_EMAIL_CONFIRMATION"] === "Y"
) echo "<p>".GetMessage("AUTH_EMAIL_WILL_BE_SENT")."</p>";
?>


<noindex>

<?/*
Регистрация через телефон
*/?>
<?if($arResult["SHOW_SMS_FIELD"] == true):?>

<form method="post" action="<?=$arResult["AUTH_URL"]?>" name="regform">
<input type="hidden" name="SIGNED_DATA" value="<?=htmlspecialcharsbx($arResult["SIGNED_DATA"])?>" />

<table class="data-table bx-registration-table">
	<tbody>
		<tr>
			<td><span class="starrequired">*</span><?echo GetMessage("main_register_sms_code")?></td>
			<td><input size="30" type="text" name="SMS_CODE" value="<?=htmlspecialcharsbx($arResult["SMS_CODE"])?>" autocomplete="off" /></td>
		</tr>
	</tbody>
	<tfoot>
		<tr>
			<td></td>
			<td><input type="submit" name="code_submit_button" value="<?echo GetMessage("main_register_sms_send")?>" /></td>
		</tr>
	</tfoot>
</table>
</form>

<script>
new BX.PhoneAuth({
	containerId: 'bx_register_resend',
	errorContainerId: 'bx_register_error',
	interval: <?=$arResult["PHONE_CODE_RESEND_INTERVAL"]?>,
	data:
		<?=CUtil::PhpToJSObject([
			'signedData' => $arResult["SIGNED_DATA"],
		])?>,
	onError:
		function(response)
		{
			var errorDiv = BX('bx_register_error');
			var errorNode = BX.findChildByClassName(errorDiv, 'errortext');
			errorNode.innerHTML = '';
			for(var i = 0; i < response.errors.length; i++)
			{
				errorNode.innerHTML = errorNode.innerHTML + BX.util.htmlspecialchars(response.errors[i].message) + '<br>';
			}
			errorDiv.style.display = '';
		}
});
</script>

<div id="bx_register_error" style="display:none"><?ShowError("error")?></div>

<div id="bx_register_resend"></div>
<?/*----------------------------------------------------------------------------------------------------------------*/?>

<?elseif(!$arResult["SHOW_EMAIL_SENT_CONFIRMATION"]):?>

<form method="post" action="<?=$arResult["AUTH_URL"]?>" name="bform" enctype="multipart/form-data">
	<input type="hidden" name="AUTH_FORM" value="Y" />
	<input type="hidden" name="TYPE" value="REGISTRATION" />
    <?if(isset($telegramData)) echo $telegramIdField;?>
    <?if(isset($telegramData)) echo '<input type="hidden" name="FROM_TELEGRAM" value="1">';?>

    <?if($arResult["EMAIL_REGISTRATION"]):?>
    <div class="row form-group">
        <div class="col-sm-3 text-sm-right">
            <label class="col-form-label starrequired" for="USER_EMAIL"><?=GetMessage("AUTH_EMAIL")?></label>
        </div>
        <div class="col-sm-9" style="position: relative;">
            <div class="input-group">
                <div class="input-group-prepend"><span class="input-group-text fa fa-envelope"></span></div>
                <input id="USER_EMAIL" class="form-control" type="text" name="USER_EMAIL" maxlength="255" value="<?=$arResult["USER_EMAIL"]?>" placeholder="Используется как логин для входа в кабинет">
            </div>
        </div>
    </div>
    <?endif?>

    <?if($arResult["PHONE_REGISTRATION"]):?><?endif?>
    <div class="row form-group">
        <div class="col-sm-3 text-sm-right">
            <label class="col-form-label starrequired" for="USER_PHONE_NUMBER"><?echo GetMessage("main_register_phone_number")?></label>
        </div>
        <div class="col-sm-9" style="position: relative;">
            <div class="input-group">
                <div class="input-group-prepend"><span class="input-group-text fa fa-phone"></span></div>
                <input id="USER_PHONE_NUMBER" class="form-control" type="text" name="USER_PHONE_NUMBER" maxlength="255" value="<?=$arResult["USER_PHONE_NUMBER"]?>" placeholder="Можно использовать как логин для входа в кабинет">
            </div>
        </div>
    </div>


    <div class="row form-group">
        <div class="col-sm-3 text-sm-right">
            <label class="col-form-label starrequired" for="USER_NAME"><?=GetMessage("AUTH_NAME")?></label>
        </div>
        <div class="col-sm-9" style="position: relative;">
            <div class="input-group">
                <div class="input-group-prepend"><span class="input-group-text fa fa-user"></span></div>
                <input id="USER_NAME" class="form-control" type="text" name="USER_NAME" maxlength="50" value="<?=$arResult["USER_NAME"]?>" placeholder="<?=GetMessage("AUTH_NAME")?>">
            </div>
        </div>
    </div>

    <div class="row form-group">
        <div class="col-sm-3 text-sm-right">
            <label class="col-form-label starrequired" for="USER_LAST_NAME"><?=GetMessage("AUTH_LAST_NAME")?></label>
        </div>
        <div class="col-sm-9" style="position: relative;">
        <div class="input-group">
            <div class="input-group-prepend"><span class="input-group-text fa fa-user"></span></div>
            <input id="USER_LAST_NAME" class="form-control" type="text" name="USER_LAST_NAME" maxlength="50" value="<?=$arResult["USER_LAST_NAME"]?>" placeholder="<?=GetMessage("AUTH_LAST_NAME")?>">
        </div>
        </div>
    </div>

    <input id="USER_LOGIN" type="hidden" name="USER_LOGIN" value="<?=$arResult["USER_LOGIN"]?>">
    <?/*
    <div class="row form-group">
        <div class="col-sm-3 text-sm-right">
            <label class="col-form-label" for="USER_LOGIN"><?=GetMessage("AUTH_LOGIN_MIN")?></label>
        </div>
        <div class="col-sm-9" style="position: relative;">
        <div class="input-group">
            <div class="input-group-prepend"><span class="input-group-text fa fa-user"></span></div>
            <input id="USER_LOGIN" class="form-control" type="text" name="USER_LOGIN" maxlength="50" value="<?=$arResult["USER_LOGIN"]?>" placeholder="<?=GetMessage("AUTH_LOGIN_MIN")?>">
        </div>
        </div>
    </div>
    */?>

    <div class="row form-group">
        <div class="col-sm-3 text-sm-right">
            <label class="col-form-label starrequired" for="USER_PASSWORD"><?=GetMessage("AUTH_PASSWORD_REQ")?></label>
        </div>
        <div class="col-sm-9" style="position: relative;">
        <div class="input-group">
            <div class="input-group-prepend"><span class="input-group-text fa fa-unlock"></span></div>
            <input id="USER_PASSWORD" class="form-control" type="password" name="USER_PASSWORD" maxlength="255" value="<?=$arResult["USER_PASSWORD"]?>" placeholder="Пароль должен содержать не менее 6 символов" autocomplete="off">
            <?if($arResult["SECURE_AUTH"]):?>
                <span class="bx-auth-secure" id="bx_auth_secure" title="<?echo GetMessage("AUTH_SECURE_NOTE")?>" style="display:none">
					<div class="bx-auth-secure-icon"></div>
				</span>
                <noscript>
				<span class="bx-auth-secure" title="<?echo GetMessage("AUTH_NONSECURE_NOTE")?>">
					<div class="bx-auth-secure-icon bx-auth-secure-unlock"></div>
				</span>
                </noscript>
                <script type="text/javascript">
                    document.getElementById('bx_auth_secure').style.display = 'inline-block';
                </script>
            <?endif?>

        </div>
    </div>
    </div>

    <div class="row form-group">
        <div class="col-sm-3 text-sm-right">
            <label class="col-form-label starrequired" for="USER_CONFIRM_PASSWORD"><?=GetMessage("AUTH_CONFIRM")?></label>
        </div>
        <div class="col-sm-9" style="position: relative;">
        <div class="input-group">
            <div class="input-group-prepend"><span class="input-group-text fa fa-lock"></span></div>
            <input id="USER_CONFIRM_PASSWORD" class="form-control" type="password" name="USER_CONFIRM_PASSWORD" maxlength="255" value="<?=$arResult["USER_CONFIRM_PASSWORD"]?>" placeholder="<?=GetMessage("AUTH_CONFIRM")?>" autocomplete="off" >
        </div>
        </div>
    </div>

<?// ********************* User properties ***************************************************?>
<?if($arResult["USER_PROPERTIES"]["SHOW"] == "Y"):?>
    <div class="row form-group">
        <div class="col-sm-12">
            <div class="col-form-label"><?=trim($arParams["USER_PROPERTY_NAME"]) <> '' ? $arParams["USER_PROPERTY_NAME"] : GetMessage("USER_TYPE_EDIT_TAB")?></div>
        </div>
    </div>

	<?foreach ($arResult["USER_PROPERTIES"]["DATA"] as $FIELD_NAME => $arUserField):?>
        <div class="row form-group">
            <div class="col-sm-3 text-sm-right">
                <label class="col-form-label" for="USER_CONFIRM_PASSWORD"><?if ($arUserField["MANDATORY"]=="Y"):?><span class="starrequired">*</span><?endif;
                    ?><?=$arUserField["EDIT_FORM_LABEL"]?></label>
            </div>
            <div class="col-sm-9" style="position: relative;">
                <?$APPLICATION->IncludeComponent(
                    "bitrix:system.field.edit",
                    $arUserField["USER_TYPE"]["USER_TYPE_ID"],
                    array("bVarsFromForm" => $arResult["bVarsFromForm"], "arUserField" => $arUserField, "form_name" => "bform"), null, array("HIDE_ICONS"=>"Y"));?>
            </div>
        </div>
	<?endforeach;?>
<?endif;?>
<?// ******************** /User properties ***************************************************

	/* CAPTCHA */
	if ($arResult["USE_CAPTCHA"] == "Y")
	{
		?>
        <div class="row form-group">
            <div class="col-sm-12">
                <div class="col-form-label"><b><?=GetMessage("CAPTCHA_REGF_TITLE")?></b></div>
            </div>
        </div>

        <div class="row form-group">
            <div class="col-sm-12">
                <input type="hidden" name="captcha_sid" value="<?=$arResult["CAPTCHA_CODE"]?>" />
                <img src="/bitrix/tools/captcha.php?captcha_sid=<?=$arResult["CAPTCHA_CODE"]?>" width="180" height="40" alt="CAPTCHA" />
            </div>
        </div>

        <div class="row form-group">
            <div class="col-sm-3 text-sm-right">
                <label class="col-form-label" for="CAPCHA_REG"><span class="starrequired">*</span><?=GetMessage("CAPTCHA_REGF_PROMT")?></label>
            </div>
            <div class="col-sm-9" style="position: relative;">
                <div class="input-group">
                    <div class="input-group-prepend"><span class="input-group-text fa fa-lock"></span></div>
                    <input id="CAPCHA_REG" class="form-control" type="text" name="captcha_word" maxlength="50" value="" autocomplete="off" >
                </div>
            </div>
        </div>

		<?
	}
	/* CAPTCHA */
	?>
    <div class="row form-group">
        <div class="col-sm-12">
				<?$APPLICATION->IncludeComponent("bitrix:main.userconsent.request", "",
					array(
						"ID" => COption::getOptionString("main", "new_user_agreement", ""),
						"IS_CHECKED" => "Y",
						"AUTO_SAVE" => "N",
						"IS_LOADED" => "Y",
						"ORIGINATOR_ID" => $arResult["AGREEMENT_ORIGINATOR_ID"],
						"ORIGIN_ID" => $arResult["AGREEMENT_ORIGIN_ID"],
						"INPUT_NAME" => $arResult["AGREEMENT_INPUT_NAME"],
						"REPLACE" => array(
							"button_caption" => GetMessage("AUTH_REGISTER"),
							"fields" => array(
								rtrim(GetMessage("AUTH_NAME"), ":"),
								rtrim(GetMessage("AUTH_LAST_NAME"), ":"),
								rtrim(GetMessage("AUTH_LOGIN_MIN"), ":"),
								rtrim(GetMessage("AUTH_PASSWORD_REQ"), ":"),
								rtrim(GetMessage("AUTH_EMAIL"), ":"),
							)
						),
					)
				);?>
        </div>
    </div>
    <div class="text-sm-right">
	<input class="btn btn-primary" type="submit" name="Register" value="<?=GetMessage("AUTH_REGISTER")?>" />
    </div>

</form>
<script type="text/javascript">
document.bform.USER_NAME.focus();
</script>

<?endif?>

</noindex>
</div>

            <div class="panel-footer">
                <div class="text-sm-right">
                    <p><span style="color: red;">*</span><?=GetMessage("AUTH_REQ")?></p>
                </div>
            </div>
                </div>
            </div>
        </div>
    </div>
</div>
</section>

<script>
    BX.ready(function(){
        // Находим форму и обязательные поля
        var form = document.forms['bform'];
        var phoneField = document.getElementById('USER_PHONE_NUMBER');
        var nameField = document.getElementById('USER_NAME');
        var lastNameField = document.getElementById('USER_LAST_NAME');

        if (form && phoneField && nameField && lastNameField) {
            // Добавляем обработчик отправки формы
            BX.bind(form, 'submit', function(e) {
                var hasError = false;
                var errorMessage = '';

                // Проверяем заполненность поля телефона
                if (!BX.util.trim(phoneField.value)) {
                    hasError = true;
                    errorMessage += '<span class="fa fa-exclamation-circle"></span> Поле "Телефон" обязательно для заполнения<br>';
                    BX.addClass(phoneField.parentNode, 'has-error');
                }

                // Проверяем заполненность поля имени
                if (!BX.util.trim(nameField.value)) {
                    hasError = true;
                    errorMessage += '<span class="fa fa-exclamation-circle"></span> Поле "Имя" обязательно для заполнения<br>';
                    BX.addClass(nameField.parentNode, 'has-error');
                }

                // Проверяем заполненность поля фамилии
                if (!BX.util.trim(lastNameField.value)) {
                    hasError = true;
                    errorMessage += '<span class="fa fa-exclamation-circle"></span> Поле "Фамилия" обязательно для заполнения<br>';
                    BX.addClass(lastNameField.parentNode, 'has-error');
                }

                if (hasError) {
                    // Есть ошибки, показываем их и отменяем отправку
                    e.preventDefault();

                    // Создаем или находим контейнер для ошибки
                    var errorContainer = document.getElementById('form_error_message');
                    if (!errorContainer) {
                        errorContainer = document.createElement('div');
                        errorContainer.id = 'form_error_message';
                        errorContainer.className = 'alert alert-danger';
                        errorContainer.style.marginTop = '10px';

                        // Вставляем перед кнопкой отправки или в удобное место
                        var submitButton = form.querySelector('input[type="submit"]');
                        if (submitButton) {
                            BX.insertBefore(errorContainer, submitButton.parentNode);
                        } else {
                            form.appendChild(errorContainer);
                        }
                    }

                    // Устанавливаем текст ошибки
                    errorContainer.innerHTML = errorMessage;
                    errorContainer.style.display = 'block';

                    // Фокусируемся на первом поле с ошибкой
                    if (!BX.util.trim(nameField.value)) {
                        nameField.focus();
                    } else if (!BX.util.trim(lastNameField.value)) {
                        lastNameField.focus();
                    } else if (!BX.util.trim(phoneField.value)) {
                        phoneField.focus();
                    }
                }
            });

            // Убираем ошибку при изменении полей
            var fields = [phoneField, nameField, lastNameField];
            fields.forEach(function(field) {
                BX.bind(field, 'input', function() {
                    var errorContainer = document.getElementById('form_error_message');
                    if (errorContainer && BX.util.trim(field.value)) {
                        BX.removeClass(field.parentNode, 'has-error');
                        // Скрываем ошибку только если все поля заполнены
                        var allFieldsFilled = true;
                        fields.forEach(function(f) {
                            if (!BX.util.trim(f.value)) {
                                allFieldsFilled = false;
                            }
                        });
                        if (allFieldsFilled) {
                            errorContainer.style.display = 'none';
                        }
                    }
                });
            });
        }
    });
</script>