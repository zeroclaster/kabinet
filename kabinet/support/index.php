<?
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/header.php");
$APPLICATION->SetTitle("Поддержка");
?>

<div class="d-flex justify-content-between">
    <?$APPLICATION->IncludeComponent("bitrix:breadcrumb","",Array(
            "START_FROM" => "0",
            "PATH" => "",
            "SITE_ID" => "s1"
        )
    );?>
    <div class="pagehelp-button text-primary" data-component="pagehelp" data-code="SUPPORT" style="margin-right: 15px;"><i class="fa fa-question-circle text-warning" aria-hidden="true"></i> Помощь</div>
</div>

<section class="section-xs">
    <div class="container-fluid">
        <?$APPLICATION->IncludeComponent("exi:page.help", "", Array(
                'CODE' => 'SUPPORT',
            )
        );?>
    </div>
</section>

<section class="section-xs"><div class="container-fluid"><h1>Поддержка</h1></div></section>

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