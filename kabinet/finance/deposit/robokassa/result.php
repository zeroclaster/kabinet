<?
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/header.php");
$APPLICATION->SetTitle("Пополнение баланса");
?>
<?
// for test
//https://kupi-otziv.ru/kabinet/finance/deposit/robokassa/result.php?OutSum=100&InvId=5&SignatureValue=501436CECB77C04FD251BBA5D50EE716


$sL = \Bitrix\Main\DI\ServiceLocator::getInstance();
$billing = $sL->get('Kabinet.Billing');

if (
        empty($_REQUEST["OutSum"]) &&
        empty($_REQUEST["InvId"]) &&
        empty($_REQUEST["SignatureValue"])
) LocalRedirect("/kabinet/finance/deposit/");
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
                    $result = new \Bitrix\Kabinet\billing\paysystem\robokassa\Result();

                    // For Test!!!
                    //print_r($result->makeCRC());
                    if ($result->isSuccess()) {
                        echo "<div class=\"alert alert-success\" role=\"alert\">Ваш баланс успешно пополнен!</div>";
                        ?>
                        <script>
                            BX.ready(function() {
                                setTimeout(function() {
                                    window.location.href = '/kabinet/';
                                }, 3000); // 3000 мс = 3 секунды
                            });
                        </script>
                        <?
                    }else{
                        $err = $result->getErrors();
                        echo "<div class=\"alert alert-danger\" role=\"alert\">{$err}</div>";
                    }
                    ?>
                </div>
            </div>

            </div>
        </div>
    </div>
</section>
<?require($_SERVER["DOCUMENT_ROOT"]."/bitrix/footer.php");?>