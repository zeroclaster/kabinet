<?use Bitrix\Main\Page\Asset;
define("INCLUDE_TAMPLATE", $_SERVER["DOCUMENT_ROOT"].SITE_TEMPLATE_PATH."/include/");
\Bitrix\Main\UI\Extension::load("ui.vue3.pinia");
\Bitrix\Main\UI\Extension::load("ui.vue3");
?>
<!DOCTYPE html>
<html class="rd-navbar-sidebar-active page-small-footer" lang="en">
  <head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, height=device-height, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <title><? $APPLICATION->ShowTitle() ?> Кабинет пользователя</title>
    <meta property="og:title" content="<? $APPLICATION->ShowTitle() ?> Кабинет пользователя">
    <meta property="og:description" content="Кабинет пользователя">
    <meta property="og:image" content="">
    <meta property="og:url" content="">
    <link rel="icon" href="<?=SITE_TEMPLATE_PATH?>/assets/images/favicon.svg" type="image/x-icon">

    <? 
    $APPLICATION->ShowHead();

    Asset::getInstance()->addCss(SITE_TEMPLATE_PATH . "/assets/components/button/button.css");
    Asset::getInstance()->addCss(SITE_TEMPLATE_PATH . "/assets/components/dropdown/dropdown.css");
    Asset::getInstance()->addCss(SITE_TEMPLATE_PATH . "/assets/components/datetimepicker/bootstrap-datetimepicker.css");
    Asset::getInstance()->addCss(SITE_TEMPLATE_PATH . "/assets/components/base/base.css");

    Asset::getInstance()->addCss(SITE_TEMPLATE_PATH . "/assets/css/style.css");
    if (\PHelp::isAdmin()) {
        Asset::getInstance()->addCss(SITE_TEMPLATE_PATH . "/assets/css/admin_style.css");
    }
    ?>
  </head>
  <body>
  <div class="page kabinet-project header-static">

      <header class="section page-header">
        <!--RD Navbar-->
        <div class="rd-navbar-wrap">
          <nav class="rd-navbar">
            <div class="rd-navbar-panel">
			
              <div class="rd-navbar-panel-inner">
                <div class="rd-navbar-panel-cell rd-navbar-panel-spacer"></div>
                <?
                  if(\PHelp::isAdmin()) include(INCLUDE_TAMPLATE."admin_panel.php");
                  else include(INCLUDE_TAMPLATE."user_panel.php");
				?>
              </div>
            </div>
            <div class="rd-navbar-sidebar-wrap">
              <div class="rd-navbar-sidebar-panel">
                  <?if(\PHelp::isAdmin()):?>
                    <div class="rd-navbar-logo"><a class="logo-link" href="/kabinet/admin/"><img class="logo-default" src="/bitrix/templates/main/images/logo_w.svg" alt="Купи отзыв"/><img class="logo-compact" src="/bitrix/templates/main/images/logo_w.svg" alt="Купи отзыв"/></a></div>
                <?else:?>
                      <div class="rd-navbar-logo"><a class="logo-link" href="/kabinet/"><img class="logo-default" src="/bitrix/templates/main/images/logo_w.svg" alt="Купи отзыв"/><img class="logo-compact" src="/bitrix/templates/main/images/logo_w.svg" alt="Купи отзыв"/></a></div>
                  <?endif;?>
                  <button class="navbar-toggle-sidebar mdi-pin" data-navigation-switch="data-navigation-switch"></button>
              </div>
              <div class="rd-navbar-sidebar scroller scroller-vertical">


              <?$APPLICATION->IncludeComponent("bitrix:menu", "top", Array(
                        "ALLOW_MULTI_SELECT" => "N",    // Разрешить несколько активных пунктов одновременно
                        "CHILD_MENU_TYPE" => "left",    // Тип меню для остальных уровней
                        "DELAY" => "N", // Откладывать выполнение шаблона меню
                        "MAX_LEVEL" => "2", // Уровень вложенности меню
                        "MENU_CACHE_GET_VARS" => array( // Значимые переменные запроса
                            0 => "",
                        ),
                        "MENU_CACHE_TIME" => "3600",    // Время кеширования (сек.)
                        "MENU_CACHE_TYPE" => "N",   // Тип кеширования
                        "MENU_CACHE_USE_GROUPS" => "Y", // Учитывать права доступа
                        "ROOT_MENU_TYPE" => "top",  // Тип меню для первого уровня
                        "USE_EXT" => "Y",   // Подключать файлы с именами вида .тип_меню.menu_ext.php
                    ),
                    false
                );?>


              
              </div>

                <div class="catalog-service-menulink"><a href="/zakaz/" target="_blank"><span class="rd-navbar-icon mdi-cart-outline"></span> <span class="footer-item-menu">Каталог услуг</span></a></div>

            </div>
          </nav>
        </div>
      </header>