<?
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/header.php");
$APPLICATION->SetTitle("Таблица исполнений");
?>

<?
$siteuser = \Bitrix\Main\DI\ServiceLocator::getInstance()->get('siteuser');
$user = \Bitrix\Main\DI\ServiceLocator::getInstance()->get('user');
?>
<section class="section-xs">
    <div class="container-fluid">
        <div class="row row-30">
            <div class="col-md-12 d-xxl-flex">
                <h1>Таблица исполнений</h1>
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
                        <?$APPLICATION->IncludeComponent("exi:admin.filterexecution", "", Array(
                                'FILTER_NAME' => 'clientfilter1',
                            )
                        );?>
                        <?
                        global $clientfilter1;
                        //\Dbg::var_dump($clientfilter1);
                        ?>
                    </div>
                </div>
                <?if($clientfilter1):?>
                <?$APPLICATION->IncludeComponent("exi:adminexecution.list", "table", Array(
                        'FILTER_NAME' => 'clientfilter1',
                        'COUNT' => 10000,
                        'MESSAGE_COUNT' => 5,
                    )
                );?>
                <?endif;?>
            </div>
        </div>
    </div>
</section>

<?require($_SERVER["DOCUMENT_ROOT"]."/bitrix/footer.php");?>


