<?if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();?><?
?>

<section class="section-lg section-one-screen">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-lg-8 col-xl-6">
                <div class="text-center"><a href="/"><img class="logo-default" src="/bitrix/templates/main/images/logo_w.svg" alt="Купи отзыв" style="width: 300px;"></a></div>

                    <?if (isMobileDevice()):?>
                <div class="row align-items-end">
                    <div class="col-12 text-right">
                        <a href="/" rel="nofollow"><b>На главную</b></a><span class="px-2">|</span>
                        <a href="<?=$arResult["AUTH_AUTH_URL"]?>">Вход</a>
                    </div>
                </div>
                    <?else:?>
                        <div class="row align-items-end">
                            <div class="col-12 text-right">
                                <a href="/" rel="nofollow"><b>На главную</b></a><span class="px-2">|</span>
                                <a href="<?=$arResult["AUTH_AUTH_URL"]?>">Вход</a>
                            </div>
                        </div>
                    <?endif;?>


                <div class="panel">
                    <div class="panel-header">

                        <h2>Восстановление пароля</h2>

                        <?
                        if (!empty($arParams["~AUTH_RESULT"]))
                        {
                            ShowMessage($arParams["~AUTH_RESULT"]);
                        }
                        ?>

                        <div class="alert alert-primary alert-border-left" role="alert"><span class="alert-icon fa-info"></span><span><?echo GetMessage("sys_forgot_pass_note_email")?></span>
                        </div>
                    </div>
                    <div class="panel-body">
                        <form name="bform" method="post" target="_top" action="<?=$arResult["AUTH_URL"]?>">
                            <? if ($arResult["BACKURL"] <> '') echo "<input type=\"hidden\" name=\"backurl\" value=\"{$arResult["BACKURL"]}\" />"; ?>
                            <input type="hidden" name="AUTH_FORM" value="Y">
                            <input type="hidden" name="TYPE" value="SEND_PWD">

                            <div class="form-group">
                                <label for="USER_LOGIN"><?=GetMessage("sys_forgot_pass_login1")?></label>
                                <input type="text" name="USER_LOGIN" value="<?=$arResult["USER_LOGIN"]?>" class="form-control" id="USER_LOGIN" aria-describedby="emailHelp">
                                <input type="hidden" name="USER_EMAIL" />
                                <small id="emailHelp" class="form-text text-muted"></small>
                            </div>

                            <?if($arResult["PHONE_REGISTRATION"]):?>
                                <div class="form-group">
                                    <label for="USER_PHONE_NUMBER"><?=GetMessage("sys_forgot_pass_phone")?></label>
                                    <input type="text" name="USER_PHONE_NUMBER" value="<?=$arResult["USER_PHONE_NUMBER"]?>" class="form-control" id="USER_PHONE_NUMBER" aria-describedby="phoneHelp">
                                    <small id="phoneHelp" class="form-text text-muted"></small>
                                </div>
                            <?endif;?>

                            <?if($arResult["USE_CAPTCHA"]):?>
                                <div class="form-group">
                                    <input type="hidden" name="captcha_sid" value="<?=$arResult["CAPTCHA_CODE"]?>" />
                                    <img src="/bitrix/tools/captcha.php?captcha_sid=<?=$arResult["CAPTCHA_CODE"]?>" width="180" height="40" alt="CAPTCHA" />
                                </div>
                                <div class="form-group">
                                    <label for="captcha_word"><?echo GetMessage("system_auth_captcha")?></label>
                                    <input type="text" name="captcha_word" maxlength="50" value="" class="form-control" id="captcha_word" aria-describedby="capchaHelp">
                                    <small id="capchaHelp" class="form-text text-muted"></small>
                                </div>
                            <?endif?>

                            <div class="form-group">
                                <input class="btn btn-danger" type="submit" name="send_account_info" value="<?=GetMessage("AUTH_SEND")?>" />
                            </div>
                        </form>

                    </div>
                </div>
            </div>
        </div>
    </div>
</section>


<script type="text/javascript">
document.bform.onsubmit = function(){document.bform.USER_EMAIL.value = document.bform.USER_LOGIN.value;};
document.bform.USER_LOGIN.focus();
</script>
