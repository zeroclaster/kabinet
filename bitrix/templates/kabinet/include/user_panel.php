<?
use Bitrix\Main\Page\Asset;

$siteuser = (\KContainer::getInstance())->get('siteuser');
(\KContainer::getInstance())->get('billingStore');
Asset::getInstance()->addJs(SITE_TEMPLATE_PATH."/assets/js/kabinet/applications/billing.js");
?>
<?/*
id = headderapp
bitrix/templates/kabinet/assets/js/kabinet/applications/billing.js
*/?>
<div class="rd-navbar-panel-cell"><a href="/kabinet/finance/deposit/" title="пополнить баланс"><img src="<?=SITE_TEMPLATE_PATH?>/assets/images/popolnenie_64b.png" alt="" style="width: 50px;"></a></div>

<!-- тут выводится балланс пользователя -->
<div id="headderapp" class="rd-navbar-panel-cell"></div>

<div class="rd-navbar-panel-cell"><a href="/kabinet/notifications/" title="Уведомления"><span class="fa-bell site-red" style="font-size: 22px;"></span></a></div>
<div class="rd-navbar-panel-cell">
<div id="usergamburgermenu" class="navbar-toggle d-flex" data-multi-switch='{"targets":"#subpanel-user-menu","scope":"#subpanel-user-menu","isolate":"[data-multi-switch]"}' title="Меню пользователя">
    
    <?/*
    <div class="butt-blk1"><button class="btn btn-link mdi-menu menu-hamburger"></button></div>
    */?>
    <img src="<?=$siteuser->getAvatar60x60()?>" alt=""/>
</div>

<div class="rd-navbar-subpanel" id="subpanel-user-menu">
  <div class="panel">
    <div class="panel-header">
      <h4 class="panel-title"><?=$siteuser->printName()?> (ID<?=$siteuser->get('ID')?>)</h4>
	  <div class="small"><?=$siteuser->get('EMAIL')?></div>
    </div>
    <div class="panel-body p-0 scroller scroller-vertical">
      <div class="list-group list-group-flush">
	  
	  
		<a class="list-group-item rounded-0" href="/kabinet/profile/">
          <div class="media align-items-center">
            <div class="pr-2"><span class="fa-user"></span></div>
            <div class="media-body">
              <div class="user-menu">Профиль</div>
            </div>
          </div></a>
		 
		  <a class="list-group-item rounded-0" href="/kabinet/notifications/">
          <div class="media align-items-center">
            <div class="pr-2"><span class="fa-bell"></span></div>
            <div class="media-body">
             <div class="user-menu">Все комментарии</div>
            </div>
          </div></a>
		  
		  <a class="list-group-item rounded-0" href="https://t.me/kupiotziv_bot" target="_blank">
          <div class="media align-items-center">
            <div class="pr-2"><span class="fa-question-circle"></span></div>
            <div class="media-body">
              <div class="user-menu">Поддержка</div>
            </div>
          </div></a>
		  
		  <a class="list-group-item rounded-0" href="/kabinet/closing-documents/">
          <div class="media align-items-center">
            <div class="pr-2"><span class="fa-book"></span></div>
            <div class="media-body">
             <div class="user-menu">Договор и документы</div>
            </div>
          </div></a>		  
		  		  
	</div>
    </div>
    <div class="panel-footer p-2">
      <div class="d-flex align-items-center justify-content-between"><a class="btn btn-sm btn-danger fa-sign-out icon-button" href="<?echo $APPLICATION->GetCurPageParam("logout=yes&".bitrix_sessid_get(), ["login","logout","register","forgot_password","change_password"]);?>">Выход</a></div>
    </div>
  </div>
</div>
</div>