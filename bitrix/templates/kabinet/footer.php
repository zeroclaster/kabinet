<?use Bitrix\Main\Page\Asset;?>

<footer class="footer footer-small">
<div class="d-flex justify-content-between align-items-center group-10">
  <div>
  <!--
  Тут размещается футер
  -->
  </div>
  <div>
    <button class="to-top btn btn-sm btn-primary"><span class="fa-chevron-up"></span></button>
  </div>
</div>
</footer>
<div class="sidebar scroller">
<div class="panel">
  <div class="panel-header">
    <h4 class="panel-title"><span class="panel-icon fa-trophy"></span><span>Right Sidebar Content</span></h4>
  </div>
  <div class="panel-body">
    <p>Lorem ipsum dolor sit amet, consectetur adipiscing elit. Fusce volutpat ac tortor eu viverra. Etiam ipsum neque, fermentum quis sagittis nec, hendrerit id diam. Mauris a tincidunt odio. Sed porttitor ex pulvinar, tristique sapien sed, malesuada nunc.</p>
  </div>
</div>
</div>

<script>
    const usr_id_const = <?echo ($_REQUEST['usr'])? $_REQUEST['usr'] : 0?>
</script>
<?
Dbg::showDebug();
?>
</div>

    <?
    Asset::getInstance()->addJs(SITE_TEMPLATE_PATH."/assets/components/base/jquery-3.4.1.min.js");
    Asset::getInstance()->addJs(SITE_TEMPLATE_PATH."/assets/components/base/script.js");
    Asset::getInstance()->addJs(SITE_TEMPLATE_PATH."/assets/components/base/moment.min.js");
    Asset::getInstance()->addJs(SITE_TEMPLATE_PATH."/assets/js/datetimepicker/ru.js");
    Asset::getInstance()->addJs(SITE_TEMPLATE_PATH."/assets/js/kabinet/custom.component.js");
    Asset::getInstance()->addJs(SITE_TEMPLATE_PATH."/assets/components/datetimepicker/bootstrap-datetimepicker.min.js");

    Asset::getInstance()->addJs(SITE_TEMPLATE_PATH."/assets/js/kabinet/userfields/custome.js");
    Asset::getInstance()->addJs(SITE_TEMPLATE_PATH."/assets/js/kabinet/utilites.js");
	Asset::getInstance()->addJs(SITE_TEMPLATE_PATH."/assets/js/kabinet/core.js");

    Asset::getInstance()->addJs(SITE_TEMPLATE_PATH."/assets/js/kabinet/vue-componets/extension/addnewmethods.js");
    Asset::getInstance()->addJs(SITE_TEMPLATE_PATH."/assets/js/kabinet/vue-componets/extension/helper.js");

    if (\PHelp::isAdmin()) Asset::getInstance()->addJs(SITE_TEMPLATE_PATH."/assets/js/kabinet/admin.application.js");
    //Asset::getInstance()->addJs(SITE_TEMPLATE_PATH."/assets/js/kabinet/test.js");
    ?>

<?php
$config = (\KContainer::getInstance())->get('config');
echo  "<script>const kabinetCongig = ".CUtil::PhpToJSObject($config, false, true).'</script>';
$addscript = (\KContainer::getInstance())->get('addscript');
foreach ($addscript as $item) {
    echo $item;
}
?>

<?if(\PHelp::isAdmin()):?>
<script>
    BX.ready(function () {
        try {
            //BX.ajax.get("/cron/cron1.php", ()=> {});
        }catch (e){

        }
    });
</script>
<?endif;?>

<div id="loading" class="loading-block"><img src="<?=SITE_TEMPLATE_PATH?>/assets/images/loading.gif" alt=""></div>
  </body>
</html>