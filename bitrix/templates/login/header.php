<?use Bitrix\Main\Page\Asset;?>
<!DOCTYPE html>
<html lang="en">
  <head>
    <title>Вход</title>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, height=device-height, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta property="og:title" content="Купи отзыв страница входа">
    <meta property="og:description" content="Купи отзыв страница входа">
    <meta property="og:image" content="">
    <meta property="og:url" content="">
    <link rel="icon" href="/bitrix/templates/kabinet/assets/images/favicon.svg" type="image/x-icon">

    <? 
    $APPLICATION->ShowHead();
    Asset::getInstance()->addCss(SITE_TEMPLATE_PATH . "/assets/components/base/base.css");
	Asset::getInstance()->addCss(SITE_TEMPLATE_PATH . "/assets/css/style.css");
    Asset::getInstance()->addCss(SITE_TEMPLATE_PATH . "/assets/css/adaptive.css");
    if (isMobileDevice()) Asset::getInstance()->addCss(SITE_TEMPLATE_PATH . "/assets/css/mobilse.css");
    ?>

  </head>
  <body>
    <div class="page page-image-bg">
