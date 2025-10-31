<?
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/header.php");
$APPLICATION->SetTitle("Пополнение баланса кабинета «Купи-Отзыв»");
?>
<section class="section-xs">
    <div class="container-fluid">
        <div class="d-flex justify-content-between">
            <h1>Пополнение баланса</h1>
            <div class="pagehelp-button text-primary" data-component="pagehelp" data-code="BALANCE" style="margin-right: 15px;"><i class="fa fa-info-circle text-warning" aria-hidden="true"></i> Помощь</div>
        </div>
    </div>
</section>


<?$APPLICATION->IncludeComponent("exi:page.help", "", Array(
        'CODE' => 'BALANCE',
    )
);?>


<section class="">
    <div class="container-fluid">
        <div class="row row-30">
            <div class="col-md-12">
            <div class="panel deposit-block-1">
                <div class="panel-body">
                    <?$APPLICATION->IncludeComponent("exi:balance.deposit", "", Array(
                        )
                    );?>
                </div>
            </div>
            </div>
        </div>
    </div>
</section>

<?require($_SERVER["DOCUMENT_ROOT"]."/bitrix/footer.php");?>