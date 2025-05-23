<?
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/header.php");
$APPLICATION->SetTitle("Профиль пользователя «Купи-Отзыв»");
?>

<?

$user = \Bitrix\Main\DI\ServiceLocator::getInstance()->get('user');
$user_id = $user->get('ID');
?>


    <section class="section-xs">
    <div class="container-fluid">
        <div class="d-flex justify-content-between">
            <h1><i class="fa fa-user" aria-hidden="true"></i> Профиль пользователя</h1>
            <div class="pagehelp-button text-primary" data-component="pagehelp" data-code="PROFILE" style="margin-right: 15px;"><i class="fa fa-info-circle text-warning" aria-hidden="true"></i> Помощь</div>
        </div>
        <div class="h3">ID<?=$user_id?></div>
    </div>
</section>
<section class="">
    <div class="container-fluid">
        <?$APPLICATION->IncludeComponent("exi:page.help", "", Array(
                'CODE' => 'PROFILE',
            )
        );?>
    </div>
</section>

<section class="section-md">
    <div class="container-fluid">
        <div class="row row-30">
            <div class="col-md-12">
                <?$APPLICATION->IncludeComponent("exi:profile.user", "", Array(
                    )
                );?>
            </div>
        </div>
    </div>
</section>

	  
<?require($_SERVER["DOCUMENT_ROOT"]."/bitrix/footer.php");?>