<form name="form_auth" method="post" target="_top" action="<?=$arResult["AUTH_URL"]?>">
    <input type="hidden" name="AUTH_FORM" value="Y" />
    <input type="hidden" name="TYPE" value="AUTH" />
    <?if ($arResult["BACKURL"] <> ''):?>
        <input type="hidden" name="backurl" value="<?=$arResult["BACKURL"]?>" />
    <?endif?>
    <?foreach ($arResult["POST"] as $key => $value):?>
        <input type="hidden" name="<?=$key?>" value="<?=$value?>" />
    <?endforeach?>

    <div class="panel-body">
        <div class="row row-30">
            <!--div class="col-lg-5 order-lg-2">
              <h4>Для входа вам нужен:</h4>
              <ul>
                <li>Ваш Email</li>
                <li>Ваш Email</li>
                <li>Ваш Email</li>
                <li>Ваш Email</li>
              </ul>
            </div-->
            <div class="col-lg-7 order-lg-1">
                <div class="form-group">
                    <label for="user"><?=GetMessage("AUTH_LOGIN")?></label>
                    <div class="input-group">
                        <div class="input-group-prepend"><span class="input-group-text fa-user"></span></div>
                        <input class="form-control" id="user" type="text" name="USER_LOGIN" placeholder="Введите ваш <?=GetMessage("AUTH_LOGIN")?>" value="<?=$arResult["LAST_LOGIN"]?>" >
                    </div>
                </div>
                <div class="form-group">
                    <label for="pass"><?=GetMessage("AUTH_PASSWORD")?></label>
                    <div class="input-group">
                        <div class="input-group-prepend"><span class="input-group-text fa-lock"></span></div>
                        <input class="form-control" id="pass" type="password" name="USER_PASSWORD" placeholder="Enter password" autocomplete="off" value="">
                    </div>
                </div>

                <div class="form-group">
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

                <?// Включение блока CAPTCHA из отдельного файла?>
                <?include_once(__DIR__.'/catcha_auth.inc.php');?>

            </div>
        </div>
    </div>
    <div class="panel-footer">
        <div class="row row-10 align-items-center">
            <div class="col-sm-6">

                <?if ($arResult["STORE_PASSWORD"] == "Y"):?>
                    <div class="custom-control custom-switch custom-switch-primary">
                        <input class="custom-control-input" type="checkbox" name="USER_REMEMBER" id="novfjclw"/>
                        <label class="custom-control-label" for="novfjclw"><?=GetMessage("AUTH_REMEMBER_ME")?>
                        </label>
                    </div>
                <?endif;?>
            </div>
            <div class="col-sm-6 text-sm-right">
                <input name="Login" class="btn btn-primary" type="submit" value="<?=GetMessage("AUTH_AUTHORIZE")?>"/>
            </div>
        </div>
    </div>
</form>