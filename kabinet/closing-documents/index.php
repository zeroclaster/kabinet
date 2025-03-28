<?
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/header.php");
$APPLICATION->SetTitle("Договор и закрывающие документы");
?>
<section class="section-xs">
    <div class="container-fluid">
        <div class="d-flex justify-content-between">
            <h1><i class="fa fa-book" aria-hidden="true"></i> Договор и закрывающие документы</h1>
            <div class="pagehelp-button text-primary" data-component="pagehelp" data-code="DOGOVOR" style="margin-right: 15px;"><i class="fa fa-info-circle text-warning" aria-hidden="true"></i> Помощь</div>
        </div>
    </div>
</section>
<section class="">
    <div class="container-fluid">
        <?$APPLICATION->IncludeComponent("exi:page.help", "", Array(
                'CODE' => 'DOGOVOR',
            )
        );?>
    </div>
</section>
<?$APPLICATION->IncludeComponent("exi:form.contract", "", Array(
        "GROUPS" =>[
            0=>"Договор",
            1=>"Платежные реквизиты",
        ],
        "GROUP0"=>[
            0=>"HLBLOCK_16_UF_NAME",
            1=>"HLBLOCK_16_UF_UR_ADDRESS",
            2=>"HLBLOCK_16_UF_INN",
            3=>"HLBLOCK_16_UF_KPP",
            4=>"HLBLOCK_16_UF_OGRN",
            5=>"HLBLOCK_16_UF_MAILN_ADDRESS",
            6=>"HLBLOCK_16_UF_FIO",
            7=>"HLBLOCK_16_UF_ACTS",
        ],
        "GROUP1"=>[
            0=>"HLBLOCK_17_UF_NAME",
            1=>"HLBLOCK_17_UF_BIK",
            2=>"HLBLOCK_17_UF_CH_ACCOUNT",
            3=>"HLBLOCK_17_UF_CORR_CHECK",
        ],
        "HB_ID" => CONTRACT,
    )
);?>

<section class="section-md">
    <div class="container-fluid">

                    <div class="panel">
                        <div class="panel-body">
                            <div class="row">
                                <div class="col-4">После заполнения вы можете скачать договор оферты</div><div class="col"> <a class="btn btn-primary" href="/upload/kupi-otziv_publichnaya_oferta_dlya_skachivaniya.pdf" target="_blank">Скачать договор оферты</a></div>
                            </div>
                            <div class="row">
                                <div class="col-4">либо скачать договор, подписать и выслать в наш адрес.</div><div class="col" id="dogovorcreator-container">
                                </div>
                                <script type="text/html" id="dogovordowload-template">
                                    <form action="/ajax/dowload/" @submit="dowload" method="post">
                                        <input type="hidden" name="usertype" v-model="contracttype.value">
                                        <input type="hidden" name="nazvanie_organizacii" v-model="fields.UF_NAME">
                                        <input type="hidden" name="ur_addres" v-model="fields.UF_UR_ADDRESS">
                                        <input type="hidden" name="inn" v-model="fields.UF_INN">
                                        <input type="hidden" name="kpp" v-model="fields.UF_KPP">
                                        <input type="hidden" name="ogrn" v-model="fields.UF_OGRN">
                                        <input type="hidden" name="mail_addres" v-model="fields.UF_MAILN_ADDRESS">
                                        <input type="hidden" name="fio" v-model="fields.UF_FIO">
                                        <input type="hidden" name="act" v-model="fields.UF_ACTS">
                                        <input type="hidden" name="nazvanie_banka" v-model="fields2.UF_NAME">
                                        <input type="hidden" name="bik" v-model="fields2.UF_BIK">
                                        <input type="hidden" name="raschetnyj_schet" v-model="fields2.UF_CH_ACCOUNT">
                                        <input type="hidden" name="korr_schet" v-model="fields2.UF_CORR_CHECK">
                                        <input type="hidden" name="idclient" v-model="datauser.ID">
                                        <input type="hidden" name="emailclient" v-model="datauser.EMAIL">
                                        <input type="hidden" name="phoneclient" v-model="datauser.PERSONAL_PHONE">
                                        <div if="err_message" style="color: red;">{{err_message}}</div>
                                    <button class="btn btn-primary" type="submit" formtarget="_blank">Скачать договор на подпись</button>
                                    </form>
                                </script>
                            </div>

                            <div class="mt-5">
                            <div class="h4">Обмен документами через ЭДО</div>
                            Если вы не можете работать черед договор-оферту, то скачайте договор, подпишите и направьте нам через ЭДО. Для этого найдите нашу организацию организацию ИП Оберман М.С. в системе «Сбис» или пришлите приглашение через вашу систему ЭДО.
                            </div>

                            <div class="mt-5">
                                <div class="h4">Обмен «бумажными» экземплярами договора</div>
                            Если вам необходимы бумажный экземпляр договора, пожалуйста, скачайте договор и распечатайте в двух экземплярах. Подпишите оба экземпляра и отправьте по адресу: 300002, Россия, г. Тула, ул. Литейная, д.4, оф.188. Ваш экземпляр договора мы подпишем и отправим по указанному в форме почтовому адресу.
                            </div>

                            <div class="mt-5">
                                <div class="h4">Закрывающие документы</div>
                            Обмен закрывающими документами осуществляется только через ЭДО. Осуществите приглашение в «Сбис» для связи с нашей организацией. Закрывающие документы будут направляться вам в системе ЭДО ежемесячно.
                            </div>
                        </div>
                    </div>
  </div>
    </section>

<?
(\KContainer::getInstance())->get('userStore');
\Bitrix\Main\Page\Asset::getInstance()->addJs(SITE_TEMPLATE_PATH."/assets/js/kabinet/applications/dogovor.creator.js");
?>
    <script>
        window.addEventListener("components:ready", function(event) {
            dogovor_creator.start(<?=CUtil::PhpToJSObject([
                'CONTAINER' => '#dogovorcreator-container',
                'TEMPLATE' => '#dogovordowload-template',
            ], false, true)?>);
        });
    </script>
	 
<?require($_SERVER["DOCUMENT_ROOT"]."/bitrix/footer.php");?>