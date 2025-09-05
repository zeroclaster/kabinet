<?if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();

if($arResult["PHONE_REGISTRATION"])
{
	CJSCore::Init('phone_auth');
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
                    <div class="col-6 col-sm-7"><a href="#"><img src="" alt=""></a></div>
                    <div class="col-6 col-sm-5 text-right">
                        <a href="/" rel="nofollow"><b>На главную</b></a><span class="px-2">|</span>
                        <a href="<?=$arResult["AUTH_AUTH_URL"]?>" rel="nofollow"><b><?=GetMessage("AUTH_AUTH")?></b></a><span class="px-2">|</span><a class="font-weight-bold" href="/login/?register=yes">Регистрация</a>
                    </div>
                </div>
                <?else:?>
                    <div class="row row-10 align-items-end">
                        <div class="col-6 col-sm-7"><a href="#"><img src="" alt=""></a></div>
                        <div class="col-6 col-sm-5 text-right">
                            <a href="/" rel="nofollow"><b>На главную</b></a><span class="px-2">|</span>
                            <a href="<?=$arResult["AUTH_AUTH_URL"]?>" rel="nofollow"><b><?=GetMessage("AUTH_AUTH")?></b></a><span class="px-2">|</span><a class="font-weight-bold" href="/login/?register=yes">Регистрация</a>
                        </div>
                    </div>
                <?endif;?>

                <div class="panel">
                    <div class="panel-header">
                        <h2><?=GetMessage("AUTH_CHANGE_PASSWORD")?></h2>
                    </div>
                    <div class="panel-body">

<?
if (!empty($arParams["~AUTH_RESULT"]))
{
	ShowMessage($arParams["~AUTH_RESULT"]);
}
?>

<?if($arResult["SHOW_FORM"]):?>

<form method="post" action="<?=$arResult["AUTH_URL"]?>" name="bform">
	<?if ($arResult["BACKURL"] <> ''): ?>
	<input type="hidden" name="backurl" value="<?=$arResult["BACKURL"]?>" />
	<? endif ?>
	<input type="hidden" name="AUTH_FORM" value="Y">
	<input type="hidden" name="TYPE" value="CHANGE_PWD">



<?if($arResult["PHONE_REGISTRATION"]):?>
			<?echo GetMessage("sys_auth_chpass_phone_number")?>

					<input type="text" value="<?=htmlspecialcharsbx($arResult["USER_PHONE_NUMBER"])?>" class="bx-auth-input" disabled="disabled" />
					<input type="hidden" name="USER_PHONE_NUMBER" value="<?=htmlspecialcharsbx($arResult["USER_PHONE_NUMBER"])?>" />
				<span class="starrequired">*</span><?echo GetMessage("sys_auth_chpass_code")?>
				<input type="text" name="USER_CHECKWORD" maxlength="50" value="<?=$arResult["USER_CHECKWORD"]?>" class="bx-auth-input" autocomplete="off" />

<?else:?>
    <div class="row form-group">
        <div class="col-sm-3 text-sm-right">
            <label class="col-form-label" for="USER_LOGIN"><span class="starrequired">*</span><?=GetMessage("AUTH_LOGIN")?></label>
        </div>
        <div class="col-sm-9" style="position: relative;">
            <div class="input-group">
                <div class="input-group-prepend"><span class="input-group-text fa fa-user"></span></div>
                <input id="USER_LOGIN" class="form-control" type="text" name="USER_LOGIN" maxlength="50" value="<?=$arResult["LAST_LOGIN"]?>" placeholder="">
            </div>
        </div>
    </div>

<?
	if($arResult["USE_PASSWORD"]):
?>
        <div class="row form-group">
            <div class="col-sm-3 text-sm-right">
                <label class="col-form-label starrequired" for="USER_CURRENT_PASSWORD"><?echo GetMessage("sys_auth_changr_pass_current_pass")?></label>
            </div>
            <div class="col-sm-9" style="position: relative;">
                <div class="input-group">
                    <div class="input-group-prepend"><span class="input-group-text fa fa-lock"></span></div>
                    <input id="USER_CURRENT_PASSWORD" class="form-control" type="password" name="USER_CURRENT_PASSWORD" maxlength="255" value="<?=$arResult["USER_CURRENT_PASSWORD"]?>" autocomplete="new-password" >
                </div>
            </div>
        </div>
<?
	else:
?>
        <div class="row form-group">
            <div class="col-sm-3 text-sm-right">
                <label class="col-form-label starrequired" for="USER_CHECKWORD"><?=GetMessage("AUTH_CHECKWORD")?></label>
            </div>
            <div class="col-sm-9" style="position: relative;">
                <div class="input-group">
                    <div class="input-group-prepend"><span class="input-group-text fa fa-lock"></span></div>
                    <input id="USER_CHECKWORD" class="form-control" type="password" name="USER_CHECKWORD" maxlength="50" value="<?=$arResult["USER_CHECKWORD"]?>" autocomplete="off" >
                </div>
            </div>
        </div>

<?
	endif
?>
<?endif?>
    <div class="row form-group">
        <div class="col-sm-3 text-sm-right">
            <label class="col-form-label starrequired" for="USER_PASSWORD"><?=GetMessage("AUTH_NEW_PASSWORD_REQ")?></label>
        </div>
        <div class="col-sm-9" style="position: relative;">
            <div class="input-group">
                <div class="input-group-prepend"><span class="input-group-text fa fa-lock"></span></div>
                <input id="USER_PASSWORD" class="form-control" type="password" name="USER_PASSWORD" maxlength="255" value="<?=$arResult["USER_PASSWORD"]?>" autocomplete="new-password" >
            </div>
        </div>
    </div>

<?if($arResult["SECURE_AUTH"]):?>
    <div class="row form-group">
        <div class="col-sm-12">
				<span class="bx-auth-secure" id="bx_auth_secure" title="<?echo GetMessage("AUTH_SECURE_NOTE")?>" style="display:none">
					<div class="bx-auth-secure-icon"></div>
				</span>
				<noscript>
				<span class="bx-auth-secure" title="<?echo GetMessage("AUTH_NONSECURE_NOTE")?>">
					<div class="bx-auth-secure-icon bx-auth-secure-unlock"></div>
				</span>
				</noscript>
        </div>
    </div>
<script type="text/javascript">
document.getElementById('bx_auth_secure').style.display = 'inline-block';
</script>
<?endif?>

    <div class="row form-group">
        <div class="col-sm-3 text-sm-right">
            <label class="col-form-label starrequired" for="USER_CONFIRM_PASSWORD"><?=GetMessage("AUTH_NEW_PASSWORD_CONFIRM")?></label>
        </div>
        <div class="col-sm-9" style="position: relative;">
            <div class="input-group">
                <div class="input-group-prepend"><span class="input-group-text fa fa-lock"></span></div>
                <input id="USER_CONFIRM_PASSWORD" class="form-control" type="password" name="USER_CONFIRM_PASSWORD" maxlength="255" value="<?=$arResult["USER_CONFIRM_PASSWORD"]?>" autocomplete="new-password" >
            </div>
        </div>
    </div>

		<?if($arResult["USE_CAPTCHA"]):?>
            <div class="row form-group">
                <div class="col-sm-12">
                            <input type="hidden" name="captcha_sid" value="<?=$arResult["CAPTCHA_CODE"]?>" />
                            <img src="/bitrix/tools/captcha.php?captcha_sid=<?=$arResult["CAPTCHA_CODE"]?>" width="180" height="40" alt="CAPTCHA" />
                </div>
            </div>
            <div class="row form-group">
                <div class="col-sm-3 text-sm-right">
                    <label class="col-form-label starrequired" for="captcha_word"><?echo GetMessage("system_auth_captcha")?></label>
                </div>
                <div class="col-sm-9" style="position: relative;">
                    <div class="input-group">
                        <div class="input-group-prepend"><span class="input-group-text fa fa-user"></span></div>
                        <input id="captcha_word" class="form-control" type="text" name="captcha_word" maxlength="50" value="" autocomplete="off">
                    </div>
                </div>
            </div>
		<?endif?>

    <div class="text-sm-right">
        <input class="btn btn-primary" type="submit" name="change_pwd" value="<?=GetMessage("AUTH_CHANGE")?>" />
    </div>

</form>

<p><?echo $arResult["GROUP_POLICY"]["PASSWORD_REQUIREMENTS"];?></p>

<?if($arResult["PHONE_REGISTRATION"]):?>

<script type="text/javascript">
new BX.PhoneAuth({
	containerId: 'bx_chpass_resend',
	errorContainerId: 'bx_chpass_error',
	interval: <?=$arResult["PHONE_CODE_RESEND_INTERVAL"]?>,
	data:
		<?=CUtil::PhpToJSObject([
			'signedData' => $arResult["SIGNED_DATA"]
		])?>,
	onError:
		function(response)
		{
			var errorDiv = BX('bx_chpass_error');
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

<div id="bx_chpass_error" style="display:none"><?ShowError("error")?></div>

<div id="bx_chpass_resend"></div>

<?endif?>

<?endif?>


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