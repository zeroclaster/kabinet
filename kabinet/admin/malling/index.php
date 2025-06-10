<?
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/header.php");
$APPLICATION->SetTitle("Рассылка");
?>


<?
$siteuser = \Bitrix\Main\DI\ServiceLocator::getInstance()->get('siteuser');

?>
<section class="section-xs">
    <div class="container-fluid">
        <div class="row row-30">
            <div class="col-md-12 d-xxl-flex">
                <h1>Рассылка</h1>
            </div>
        </div>
    </div>
</section>
<section class="section-xs">
    <div class="container-fluid">
        <div class="row row-30">
            <div class="col-md-12">
                <?
                $GLOBALS["findallmaillingusers"] = ['>UF_EMAIL_NOTIFI'=>0];
                ?>
                <?$APPLICATION->IncludeComponent("exi:admin.maillingusers", "", Array(
                        "FILTER_NAME" => "findallmaillingusers",
                        'COUNT' => 10,
                    )
                );?>
            </div>
        </div>
    </div>
</section>

<?require($_SERVER["DOCUMENT_ROOT"]."/bitrix/footer.php");?>


