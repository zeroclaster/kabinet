<?
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();
?>
<section class="section-lg section-one-screen">
        <div class="container">
            <?/*
           <div style="position: absolute;background-color: #FFF;padding: 10px;">
               <div>Временный блок для тестирования</div>
               <div>логин <input type="text" value="manager1@manager1.ru"></div>
               <div>пароль <input type="text" value="123456"></div>
           </div>
		   */?>

          <div class="row justify-content-center">
            <div class="col-lg-8">

             <div class="text-center"><img class="logo-default" src="/bitrix/templates/main/images/logo_w.svg" alt="Купи отзыв" style="width: 300px;"></div>

              <div class="row row-10 align-items-end">
                <div class="col-3 col-sm-3"><a href="/"><img src="images/logo-default-178x66.png" alt=""></a></div>
                <div class="col-9 col-sm-9 text-right">
                	<?if ($arParams["NOT_SHOW_LINKS"] != "Y"):?>
                	<noindex>
                	<a class="font-weight-bold" href="<?=$arResult["AUTH_FORGOT_PASSWORD_URL"]?>" rel="nofollow"><?=GetMessage("AUTH_FORGOT_PASSWORD_2")?></a><span class="px-2">|</span>
                	</noindex>
                	<?endif;?>
                	<?if($arParams["NOT_SHOW_LINKS"] != "Y" && $arResult["NEW_USER_REGISTRATION"] == "Y" && $arParams["AUTHORIZE_REGISTRATION"] != "Y"):?>
                	<noindex>
                	<a href="<?=$arResult["AUTH_REGISTER_URL"]?>" rel="nofollow"><?=GetMessage("AUTH_REGISTER")?></a>
                	</noindex>
                	<?endif;?>
                	
                </div>
              </div>
              <div class="panel">
                
<?/*
				<?if($arResult["AUTH_SERVICES"]):?>
				<?
				$APPLICATION->IncludeComponent("bitrix:socserv.auth.form", "",
					array(
						"AUTH_SERVICES" => $arResult["AUTH_SERVICES"],
						"CURRENT_SERVICE" => $arResult["CURRENT_SERVICE"],
						"AUTH_URL" => $arResult["AUTH_URL"],
						"POST" => $arResult["POST"],
						"SHOW_TITLES" => $arResult["FOR_INTRANET"]?'N':'Y',
						"FOR_SPLIT" => $arResult["FOR_INTRANET"]?'Y':'N',
						"AUTH_LINE" => $arResult["FOR_INTRANET"]?'N':'Y',
					),
					$component,
					array("HIDE_ICONS"=>"Y")
				);
				?>
				<?endif?>
                <div class="panel-header">
                  <div class="row row-10">
                    <div class="col-md-4">
                      <button class="btn btn-primary btn-block"> <span class="fa-facebook-f"></span> Facebook</button>
                    </div>
                    <div class="col-md-4">
                      <button class="btn btn-info btn-block"><span class="fa-twitter"></span> Twitter</button>
                    </div>
                    <div class="col-md-4">
                      <button class="btn btn-danger btn-block"><span class="fa-google-plus"></span> Google +</button>
                    </div>
                  </div>
                </div>
*/?>

				<?if($arResult["AUTH_SERVICES"]):?>
					<div class="bx-auth-title"><?echo GetMessage("AUTH_TITLE")?></div>
				<?endif?>
				

                  <div class="alert alert-dismissible alert-secondary alert123456" role="alert"><span class="alert-icon fa-trophy"></span><span><?=GetMessage("AUTH_PLEASE_AUTH")?></span>
                    <button class="close" type="button" data-dismiss="alert" aria-label="Close"><span class="fa-close" aria-hidden="true"></span></button>
                  </div>


                  <? if (!empty($arParams["~AUTH_RESULT"])): ?>
                  <?//ShowMessage($arParams["~AUTH_RESULT"]);?>
                      <script>
                          var err_auth_mess = '<?=strip_tags($arParams["~AUTH_RESULT"]['MESSAGE'])?>';
                      </script>
                  <?endif;?>
                  <?if (!empty($arResult['ERROR_MESSAGE'])):?>
                      <script>
                          var err_auth_mess = '<?=strip_tags($arResult['ERROR_MESSAGE']['MESSAGE'])?>';
                      </script>
                      <?//ShowMessage($arResult['ERROR_MESSAGE']); ?>
                  <?endif;?>

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


			<?if($arResult["CAPTCHA_CODE"]):?>
			
					
					<div class="form-group">
						<input type="hidden" name="captcha_sid" value="<?echo $arResult["CAPTCHA_CODE"]?>" />
						<img src="/bitrix/tools/captcha.php?captcha_sid=<?echo $arResult["CAPTCHA_CODE"]?>" width="180" height="40" alt="CAPTCHA" />
					</div>
				
				
					<div class="form-group">
						<label for="capchaword"><?echo GetMessage("AUTH_CAPTCHA_PROMT")?>:</label>
					<input id="capchaword" class="form-control" type="text" name="captcha_word" maxlength="50" value="" size="15" autocomplete="off" />

					</div>
			<?endif;?>


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

              </div>
            </div>
          </div>
        </div>
      </section>


<script type="text/javascript">
<?if ($arResult["LAST_LOGIN"] <> ''):?>
try{document.form_auth.USER_PASSWORD.focus();}catch(e){}
<?else:?>
try{document.form_auth.USER_LOGIN.focus();}catch(e){}
<?endif?>

window.addEventListener("components:ready", function(event) {
    if (typeof err_auth_mess != "undefined") {
        PNotify.alert({
            type: 'danger',
            title: 'Ошибка!',
            text: err_auth_mess,
            animation: 'fade',
            width: '300px',
            shadow: false,
            styling: 'bootstrap4',
            icons: 'fontawesome4'
        });
    }
});
</script>