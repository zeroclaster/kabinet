<?
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/header.php");
$APPLICATION->SetTitle("Финансы кабинета «Купи-Отзыв»");
?>


<section class="section-xs">
    <div class="container-fluid">
        <div class="d-flex justify-content-between">
            <h1><i class="fa fa-credit-card-alt" aria-hidden="true"></i> Финансы</h1>
            <div class="pagehelp-button text-primary" data-component="pagehelp" data-code="FINANCE" style="margin-right: 15px;"><i class="fa fa-info-circle text-warning" aria-hidden="true"></i> Помощь</div>
        </div>
    </div>
</section>


<?$APPLICATION->IncludeComponent("exi:page.help", "", Array(
        'CODE' => 'FINANCE',
    )
);?>


<section class="section-xs">
    <div class="container-fluid">
        <div class="row row-30">
                <?$APPLICATION->IncludeComponent("exi:billing.view", "", Array(
                        'COUNT' => 20,                           // количество
                        "FILTER_NAME"=>'',
                    )
                );?>
            </div>
        </div>
</section>
<?require($_SERVER["DOCUMENT_ROOT"]."/bitrix/footer.php");?>