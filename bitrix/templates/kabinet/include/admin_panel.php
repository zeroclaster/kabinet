<?
$siteuser = \Bitrix\Main\DI\ServiceLocator::getInstance()->get('siteuser');
$user = \Bitrix\Main\DI\ServiceLocator::getInstance()->get('user');
?>
<div class="rd-navbar-panel-cell" id="admincontent">
    <div class="h4">Профиль администратора</div>
    <?if($user->get('ID')):?>
    <div>Вы находитесь в кабинете пользователя: <?=$user->printName()?> (#id: <?=$user->get('ID')?>)</div>
    <?endif;?>
</div>
<div class="rd-navbar-panel-cell">
    <div class="navbar-toggle navbar-user" data-multi-switch='{"targets":"#subpanel-user-menu","scope":"#subpanel-user-menu","isolate":"[data-multi-switch]"}' title="User Menu"><img src="<?=$siteuser->getAvatar60x60()?>" alt=""/></div>
    <div class="rd-navbar-subpanel" id="subpanel-user-menu">
        <div class="panel">
            <div class="panel-header">
                <h4 class="panel-title"><?=$siteuser->printName()?> (#<?=$siteuser->get('ID')?>)</h4>
                <div class="small"><?=$siteuser->get('EMAIL')?></div>
            </div>
            <div class="panel-body p-0 scroller scroller-vertical">
                <div class="list-group list-group-flush">

                    <a class="list-group-item rounded-0" href="/kabinet/admin/profile/">
                        <div class="media align-items-center">
                            <div class="pr-2"><span class="fa-user"></span></div>
                            <div class="media-body">
                                <h5>Профиль</h5>
                            </div>
                        </div></a>

                </div>
            </div>
            <div class="panel-footer p-2">
                <div class="d-flex align-items-center justify-content-between"><a class="btn btn-sm btn-danger" href="<?echo $APPLICATION->GetCurPageParam("logout=yes&".bitrix_sessid_get(), ["login","logout","register","forgot_password","change_password"]);?>">Выход</a></div>
            </div>
        </div>
    </div>
</div>


