<?
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/header.php");
$APPLICATION->SetTitle("Пополнение баланса");
?>
<?
// for test
//https://kupi-otziv.ru/kabinet/finance/deposit/result.php?OutSum=100&InvId=5&SignatureValue=501436CECB77C04FD251BBA5D50EE716

$sL = \Bitrix\Main\DI\ServiceLocator::getInstance();
$billing = $sL->get('Kabinet.Billing');

?>
<section class="section-xs">
    <div class="container-fluid">
        <div class="row row-30">
            <div class="col-md-12">
                <h1>Пополнение баланса</h1>
           </div>


            <div class="panel">
                <div class="panel-body">
                    <?
                    (new \Bitrix\Kabinet\billing\paysystem\robokassa\Result())->failpay();
                    ?>

                    <div class="alert alert-danger" role="alert">Вы отказались от пополнения баланса!</div>
                </div>
            </div>

            </div>
        </div>
    </div>
</section>

<?require($_SERVER["DOCUMENT_ROOT"]."/bitrix/footer.php");?>