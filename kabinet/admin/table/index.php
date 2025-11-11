<?
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/header.php");
$APPLICATION->SetTitle("Таблица исполнений");
?>

<?
$siteuser = \Bitrix\Main\DI\ServiceLocator::getInstance()->get('siteuser');
$user = \Bitrix\Main\DI\ServiceLocator::getInstance()->get('user');
?>

                    <div class="panel-body" style="display: none;">
                        <?$APPLICATION->IncludeComponent("exi:admin.filterexecution", "", Array(
                                'FILTER_NAME' => 'clientfilter1',
                            )
                        );?>
                        <?
                        global $clientfilter1;
                        //\Dbg::var_dump($clientfilter1);
                        ?>
                    </div>

                <?if($clientfilter1):?>
                <?$APPLICATION->IncludeComponent("exi:adminexecution.list", "table", Array(
                        'FILTER_NAME' => 'clientfilter1',
                        'COUNT' => 10000,
                        'MESSAGE_COUNT' => 5,
                    )
                );?>
                <?endif;?>

<div id="admincontent">1234</div>
<?require($_SERVER["DOCUMENT_ROOT"]."/bitrix/footer.php");?>


