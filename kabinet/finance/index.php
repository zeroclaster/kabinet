<?
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/header.php");
$APPLICATION->SetTitle("Финансы");
?>

<div class="d-flex justify-content-between">
    <?$APPLICATION->IncludeComponent("bitrix:breadcrumb","",Array(
            "START_FROM" => "0",
            "PATH" => "",
            "SITE_ID" => "s1"
        )
    );?>
    <div class="pagehelp-button text-primary" data-component="pagehelp" data-code="FINANCE" style="margin-right: 15px;"><i class="fa fa-info-circle text-warning" aria-hidden="true"></i> Помощь</div>
</div>

<section class="section-xs">
    <div class="container-fluid">
        <?$APPLICATION->IncludeComponent("exi:page.help", "", Array(
                'CODE' => 'FINANCE',
            )
        );?>
    </div>
</section>

<section class="section-xs">
    <div class="container-fluid">
        <div class="row row-30">
            <div class="col-md-12">
                <h1><i class="fa fa-credit-card-alt" aria-hidden="true"></i> Финансы</h1>
            </div>

                <?$APPLICATION->IncludeComponent("exi:billing.view", "", Array(
                        'COUNT' => 20,                           // количество
                        "FILTER_NAME"=>'',
                    )
                );?>
            </div>
        </div>
</section>
<?require($_SERVER["DOCUMENT_ROOT"]."/bitrix/footer.php");?>