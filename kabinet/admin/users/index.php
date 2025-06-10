<?
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/header.php");
$APPLICATION->SetTitle("Пользователи");
?>


<?
$siteuser = \Bitrix\Main\DI\ServiceLocator::getInstance()->get('siteuser');

?>
<section class="section-xs">
    <div class="container-fluid">
        <div class="row row-30">
            <div class="col-md-12 d-xxl-flex">
                <h1>Пользователи</h1>
            </div>
        </div>
    </div>
</section>
<section class="section-xs">
    <div class="container-fluid">
        <div class="row row-30">
            <div class="col-md-12">
                        <?
                        $GLOBALS["findalltelegramusers"] = ['>UF_TELEGRAM_ID'=>0];
                        ?>
                        <?$APPLICATION->IncludeComponent("exi:admin.telegramusers", "", Array(
                                "FILTER_NAME" => "findalltelegramusers",
                                'COUNT' => 10,
                            )
                        );?>
            </div>
        </div>
    </div>
</section>

<?require($_SERVER["DOCUMENT_ROOT"]."/bitrix/footer.php");?>


