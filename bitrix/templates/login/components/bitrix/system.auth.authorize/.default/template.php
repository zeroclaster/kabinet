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

                <div class="text-center"><a href="/"><img class="logo-default" src="/bitrix/templates/main/images/logo_w.svg" alt="Купи отзыв" style="width: 300px;"></a></div>

                <?if (isMobileDevice()):?>
              <div class="row row-10 align-items-end">
                             <div class="col-sm-12 text-right">
                    <a href="/" rel="nofollow"><b>На главную</b></a><span class="px-2">|</span>
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
                <?else:?>
                    <div class="row row-10 align-items-end">
                        <div class="col-3 col-sm-3"><a href="/"><img src="images/logo-default-178x66.png" alt=""></a></div>
                        <div class="col-9 col-sm-9 text-right">
                            <a href="/" rel="nofollow"><b>На главную</b></a><span class="px-2">|</span>
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
                <?endif;?>

              <div class="panel">

                  <?include_once(__DIR__.'/socserv_auth.inc.php');?>

				<?if($arResult["AUTH_SERVICES"]):?>
					<div class="bx-auth-title"><?echo GetMessage("AUTH_TITLE")?></div>
				<?endif?>
				

                  <?/*
                  <div class="alert alert-dismissible alert-secondary alert123456" role="alert"><span class="alert-icon fa-trophy"></span><span><?=GetMessage("AUTH_PLEASE_AUTH")?></span>
                    <button class="close" type="button" data-dismiss="alert" aria-label="Close"><span class="fa-close" aria-hidden="true"></span></button>
                  </div>
                    */?>

                  <div class="panel-header"><h2><i class="fa fa-sign-in" aria-hidden="true" style="font-style: normal;"></i> Вход в личный кабинет</h2></div>

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



                  <div id="telegram-login-btn" style="margin: 20px;margin-bottom: 0px;"></div>

                  <script>
                      BX.ready(function() {
                          var script = document.createElement('script');
                          script.async = true;
                          script.src = "https://telegram.org/js/telegram-widget.js?22";
                          script.setAttribute('data-telegram-login', 'kupiotziv_bot');
                          script.setAttribute('data-size', 'large');
                          script.setAttribute('data-auth-url', '/auth/telegramtoregistrate.php');
                          script.setAttribute('data-request-access', 'write');
                          script.setAttribute('data-userpic', 'true'); // Отключаем аватарку
                          document.getElementById('telegram-login-btn').appendChild(script);
                      });
                  </script>

                  <?include_once(__DIR__.'/form_auth.inc.php');?>

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


// При загрузке страницы подставляем сохраненный логин из куки
BX.ready(function() {
    var savedLogin = BX.getCookie('saved_login');
    if (savedLogin && document.form_auth && document.form_auth.USER_LOGIN) {
        document.form_auth.USER_LOGIN.value = savedLogin;
    }


// При отправке формы сохраняем логин в куки
if (document.form_auth) {
    document.form_auth.addEventListener('submit', function() {
        var loginValue = document.form_auth.USER_LOGIN.value;
        if (loginValue) {
            // Сохраняем на 30 дней
            BX.setCookie('saved_login', loginValue, {expires: 30});
        }
    });
}

});

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