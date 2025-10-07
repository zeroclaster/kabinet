<?
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/header.php");
$APPLICATION->SetTitle("Корректировка баланса");
?>
<?
$siteuser = \Bitrix\Main\DI\ServiceLocator::getInstance()->get('siteuser');
$user = \Bitrix\Main\DI\ServiceLocator::getInstance()->get('user');
?>
<section class="section-xs">
    <div class="container-fluid">
        <div class="row row-30">
            <div class="col-md-12 d-xxl-flex">
                <h1>Корректировка баланса</h1>
            </div>
        </div>
    </div>
</section>
<section class="section-xs">
    <div class="container-fluid">
        <div class="row row-30">
            <div class="col-md-12">
                <div class="panel">
                    <div class="panel-header">
                        <div class="d-flex justify-content-between align-items-center flex-wrap group-10">
                        </div>
                    </div>
                    <div class="panel-body">
                        <?$APPLICATION->IncludeComponent("exi:adminbalancecorrection", "", Array(
                            )
                        );?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<?require($_SERVER["DOCUMENT_ROOT"]."/bitrix/footer.php");?>


