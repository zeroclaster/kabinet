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

                  <?include_once(__DIR__.'/socserv_auth.inc.php');?>

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



                  <div id="telegram-login-btn" style="margin: 20px;margin-bottom: 0px;"></div>

                  <script>
                      BX.ready(function() {
                          var script = document.createElement('script');
                          script.async = true;
                          script.src = "https://telegram.org/js/telegram-widget.js?22";
                          script.setAttribute('data-telegram-login', 'kupiotziv_bot');
                          script.setAttribute('data-size', 'large');
                          script.setAttribute('data-auth-url', '/auth/telegram.php');
                          script.setAttribute('data-request-access', 'write');
                          script.setAttribute('data-userpic', 'false'); // Отключаем аватарку
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