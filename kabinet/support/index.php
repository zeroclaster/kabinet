<?
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/header.php");
$APPLICATION->SetTitle("Поддержка");
?>
<section class="section-xs">
    <div class="container-fluid">
        <div class="d-flex justify-content-between">
            <h1><i class="fa fa-question-circle" aria-hidden="true"></i> Поддержка</h1>
            <div class="pagehelp-button text-primary" data-component="pagehelp" data-code="SUPPORT" style="margin-right: 15px;"><i class="fa fa-info-circle text-warning" aria-hidden="true"></i> Помощь</div>
        </div>

    </div>
</section>
<section class="">
    <div class="container-fluid">
        <?$APPLICATION->IncludeComponent("exi:page.help", "", Array(
                'CODE' => 'SUPPORT',
            )
        );?>
    </div>
</section>
<?
// если нужно показывать только прочитанные
//$GLOBALS['message_filter'] = ['UF_STATUS'=>\Bitrix\Kabinet\messanger\Messanger::NEW_MASSAGE];
$GLOBALS['message_filter'] = ['UF_TYPE'=>\Bitrix\Kabinet\messanger\Messanger::USER_MESSAGE];
?>
<?$APPLICATION->IncludeComponent("exi:messanger.view", "support-page", Array(
        'FILTER_NAME' => 'message_filter',      // фильтр по id пользователя добавляется всегда
        'COUNT' => 100,                           // количество сообщений в чате
        //'NEW_RESET' => 'N',
    )
);?>

<script>
    BX.ready(function () {
        setTimeout(function () {
            const el = document.querySelector("footer");
            if (el) el.scrollIntoView({behavior: 'smooth'});
        },1000)

    });
</script>

<?require($_SERVER["DOCUMENT_ROOT"]."/bitrix/footer.php");?>