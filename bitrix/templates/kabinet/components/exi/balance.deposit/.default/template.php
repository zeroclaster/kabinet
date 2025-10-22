<?/* МЕСТО ВСТАВКИ */?>
<div id="depositcontent"></div>

<?/* ШАБЛОН */?>
<script type="text/html" id="deposit-template">
    <form name="depositform1" action="get" @submit="onSubmit">
        <input type="hidden" name="totalsum" v-model="totalsum">
        Выберите способ пополнения баланса:
        <div class="radio-list">
            <?if(!\PHelp::isAdmin()):?>
                <div class="radio-item">

                    <input id="typepay-1" type="radio" name="typepay" value="1" v-model="fields.typepay" @change="onChange">
                    <label for="typepay-1" class="radio-label no-d-flex-to-block">
                        <i class="fa fa-credit-card-alt" aria-hidden="true"></i> <span style="font-size: 22px;">Карта</span> <span style="font-weight: normal;color: #8a8a8a;">Мгновенное пополнение. Мир, СБП, Сбер Pay, Т-pay, Я-Пэй. Комиссия 7%</span>
                    </label>
                </div>
            <?endif;?>

            <?if(!\PHelp::isAdmin()):?>
                <?/*2025-03-03 Скрыть оплату qr-кодом*/?>
                <?if(0):?>
                    <div class="radio-item">
                        <input id="typepay-2" type="radio" name="typepay" value="2" v-model="fields.typepay" @change="onChange">
                        <label for="typepay-2" class="radio-label no-d-flex-to-block">
                            <i class="fa fa-qrcode" aria-hidden="true"></i> QR-код
                        </label>
                    </div>
                <?endif;?>
            <?endif;?>

            <?if(!\PHelp::isAdmin()):?>
                <div class="radio-item">
                    <input id="typepay-3" type="radio" name="typepay" value="3" v-model="fields.typepay" @change="onChange">
                    <label for="typepay-3" class="radio-label no-d-flex-to-block">
                        <i class="fa fa-university" aria-hidden="true"></i> <span style="font-size: 22px;">Банковский перевод</span> <span style="font-weight: normal;color: #8a8a8a;">Оплата по счету, для юрлиц и ИП. Комиссия 3%</span>
                    </label>
                </div>
            <?endif;?>

            <?if(\PHelp::isAdmin()):?>
                <div class="radio-item">
                    <input id="typepay-4" type="radio" name="typepay" value="4" v-model="fields.typepay" @change="onChange">
                    <label for="typepay-4" class="radio-label no-d-flex-to-block">
                        <i class="fa fa-university" aria-hidden="true"></i> Прямое пополнение
                    </label>
                </div>
            <?endif;?>
        </div>

        <?/* $typepay = $request->getPost('typepay'); */?>
        <!-- Онлайн-платеж -->
        <?if(!\PHelp::isAdmin()):?>
            <div class="row" v-if="fields.typepay==1">
                <div class="col-md-12">
                    <div class="to-pay-block">

                        <div>Текущий баланс</div>
                        <div class="usr-balanse">{{databilling.UF_VALUE_ORIGINAL}} руб.</div>
                        <div class="form-block mt-5">
                            <div class="form-group row">
                                <div class="col-md-2">
                                    <label for="summa-popolneniya">Сумма пополнения, руб.</label>
                                    <div v-if="showError('summapopolneniya')" class="error-field">Вы не ввели сумму пополнения</div>
                                    <div v-if="showError('summapopolneniya2')" class="error-field">Сумма платежа не должна быть меньше 1000</div>
                                    <input
                                            id="summa-popolneniya"
                                            name="summapopolneniya"
                                            class="form-control"
                                            style="text-align: right" type="text"
                                            v-model="fields.summapopolneniya"
                                            @input="formatCurrency($event, 'summapopolneniya')"
                                            placeholder="0.00"
                                    >
                                    <div class="info-help">Не менее 1000 рублей.</div>
                                </div>
                            </div>
                            <?/*
											пока не используется
											*/?>
                            <?/*
                                            <div class="form-group row">
                                                <div class="col-md-6">
                                                <label for="promocode">Промокод</label>
                                                <input id="promocode" name="promocode" class="form-control" type="text"  v-model="fields.promocode" placeholder="Добавить промокод" @input="onInput">
                                                </div>
                                            </div>
											*/?>

                            <div class="total-sum mt-3 mb-5">Сумма платежа: <span>{{totalsum}} руб.</span></div>

                            <div class="d-flex justify-content-center"><div v-if="isError" class="error-field">Ошибка при заполнении полей</div></div>
                            <div class="gotopay"><button class="btn btn-primary" type="button" @click="onSubmit">Перейти к оплате</button></div>
                        </div>

                    </div>
                </div>
            </div>
        <?endif;?>

        <?/* $typepay = $request->getPost('typepay'); */?>
        <?/*2025-03-03 Скрыть оплату qr-кодом*/?>
        <?if(0):?>
            <?if(!\PHelp::isAdmin()):?>
                <!-- QR-код -->
                <div class="row" v-if="fields.typepay==2">
                    <div class="col-md-12">
                        <div class="to-pay-block">

                            <div>Текущий баланс</div>
                            <div class="usr-balanse">{{databilling.UF_VALUE_ORIGINAL}} руб.</div>
                            <div class="form-block mt-5">
                                <div class="form-group row">
                                    <div class="col-md-2">
                                        <label for="qrsumm">Сумма пополнения, руб.</label>
                                        <div v-if="showError('summapopolneniya')" class="error-field">Вы не ввели сумму пополнения</div>

                                        <select class="form-control" name="qrsumm" id="qrsumm" v-model="fields.qrsumm" style="width: 170px;text-align: right;" @change="onChange">
                                            <option value="1000">1000</option>
                                            <option value="3000">3000</option>
                                            <option value="5000">5000</option>
                                            <option value="10000">10000</option>
                                            <option value="20000">20000</option>
                                        </select>
                                        <div class="info-help"></div>
                                    </div>
                                    <div class="col-md-6">
                                        <img v-if="fields.qrsumm==1000" src="/kabinet/finance/deposit/images/kupi-otziv.ru_QR1075_qrcode.png">
                                        <img v-if="fields.qrsumm==3000" src="/kabinet/finance/deposit/images/kupi-otziv.ru_QR3225.81_qrcode.png">
                                        <img v-if="fields.qrsumm==5000" src="/kabinet/finance/deposit/images/kupi-otziv.ru_QR5376_qrcode.png">
                                        <img v-if="fields.qrsumm==10000" src="/kabinet/finance/deposit/images/kupi-otziv.ru_QR10752_qrcode.png">
                                        <img v-if="fields.qrsumm==20000" src="/kabinet/finance/deposit/images/kupi-otziv.ru_QR21505_qrcode.png">
                                    </div>
                                </div>
                                <?/*
											пока не используется
											*/?>
                                <?/*
                                            <div class="form-group row">
                                                <div class="col-md-6">
                                                <label for="promocode">Промокод</label>
                                                <input id="promocode" name="promocode" class="form-control" type="text"  v-model="fields.promocode" placeholder="Добавить промокод" @input="onInput">
                                                </div>
                                            </div>
											*/?>

                                <div class="total-sum mt-3 mb-5">Сумма платежа: <span>{{totalsum}} руб.</span></div>

                                <div class="d-flex justify-content-end"><div v-if="isError" class="error-field">Ошибка при заполнении полей</div></div>
                                <div class="gotopay">
                                    <form action="" method="post">
                                        <input type="hidden" name="typepay" v-model="fields.typepay">
                                        <input type="hidden" name="qrsumm" v-model="fields.qrsumm">
                                        <button class="btn btn-primary" type="submit">Обновить баланс</button>
                                    </form>
                                </div>
                            </div>

                        </div>
                    </div>
                </div>
            <?endif;?>
        <?endif;?>

        <?/* $typepay = $request->getPost('typepay'); */?>
        <!-- Банковский перевод -->
        <?if(!\PHelp::isAdmin()):?>
            <div class="row" v-if="fields.typepay==3">
                <div class="col-md-12">
                    <div class="to-pay-block">

                        <div>Текущий баланс</div>
                        <div class="usr-balanse">{{databilling.UF_VALUE_ORIGINAL}} руб.</div>
                        <div class="form-block mt-5">
                            <div class="form-group row">
                                <div class="col-md-2">
                                    <label for="summa-popolneniya2">Сумма пополнения, руб.</label>
                                    <div v-if="showError('summapopolneniya')" class="error-field">Вы не ввели сумму пополнения</div>
                                    <div v-if="showError('summapopolneniya2')" class="error-field">Сумма платежа не должна быть меньше 1000</div>
                                    <input
                                            id="summa-popolneniya2"
                                            name="summapopolneniya"
                                            class="form-control"
                                            style="text-align: right"
                                            type="text"
                                            v-model="fields.summapopolneniya"
                                            @input="formatCurrency($event, 'summapopolneniya')"
                                            placeholder="0.00"
                                    >
                                    <div class="info-help">Не менее 1000 рублей.</div>
                                </div>
                            </div>
                            <?/*
											пока не используется
											*/?>
                            <?/*
                                            <div class="form-group row">
                                                <div class="col-md-6">
                                                <label for="promocode">Промокод</label>
                                                <input id="promocode" name="promocode" class="form-control" type="text"  v-model="fields.promocode" placeholder="Добавить промокод" @input="onInput">
                                                </div>
                                            </div>
											*/?>

                            <div class="total-sum mt-3 mb-5">Сумма платежа: <span>{{totalsum}} руб.</span></div>
                            <div>Для формирования счета, заполните данные в разделе <a href="/kabinet/closing-documents/">"Договор и документы"</a>.</div>

                            <div class="d-flex justify-content-center"><div v-if="isError" class="error-field">Ошибка при заполнении полей</div></div>
                            <div class="d-flex justify-content-center" v-if="errorField.contractFieldEmpty"><div class="error-field">Вы не заолнили обязательные поля <a href="/kabinet/closing-documents/">"Договор и документы"</a></div></div>
                            <div class="gotopay">
                                <form action="/ajax/pdfschot/invoice.pdf" method="post" @submit="download">
                                    <input type="hidden" name="summ" v-model="totalsum">
                                    <input type="hidden" name="billing_id" v-model="databilling.ID">
                                    <input type="hidden" name="usertype" v-model="contracttype.value">
                                    <input type="hidden" name="nazvanie_organizacii" v-model="contract.UF_NAME">
                                    <input type="hidden" name="ur_addres" v-model="contract.UF_UR_ADDRESS">
                                    <input type="hidden" name="inn" v-model="contract.UF_INN">
                                    <input type="hidden" name="kpp" v-model="contract.UF_KPP">
                                    <input type="hidden" name="ogrn" v-model="contract.UF_OGRN">
                                    <input type="hidden" name="mail_addres" v-model="contract.UF_MAILN_ADDRESS">
                                    <input type="hidden" name="fio" v-model="contract.UF_FIO">
                                    <input type="hidden" name="act" v-model="contract.UF_ACTS">
                                    <input type="hidden" name="nazvanie_banka" v-model="bank.UF_NAME">
                                    <input type="hidden" name="bik" v-model="bank.UF_BIK">
                                    <input type="hidden" name="raschetnyj_schet" v-model="bank.UF_CH_ACCOUNT">
                                    <input type="hidden" name="korr_schet" v-model="bank.UF_CORR_CHECK">
                                    <input type="hidden" name="idclient" v-model="datauser.ID">
                                    <input type="hidden" name="emailclient" v-model="datauser.EMAIL">
                                    <input type="hidden" name="phoneclient" v-model="datauser.PERSONAL_PHONE">
                                    <button class="btn btn-primary" type="submit" formtarget="_blank">Скачать счет PDF</button>
                                    <button class="btn btn-link" type="button" @click="toemail">Отправить счет PDF на почту</button>
                                </form>
                            </div>
                        </div>

                    </div>
                </div>
            </div>
        <?endif;?>

        <?/* $typepay = $request->getPost('typepay'); */?>
        <?if(\PHelp::isAdmin()):?>
            <div class="row typepay-4" v-if="fields.typepay==4">
                <div class="col-md-12">
                    <div class="to-pay-block">

                        <div>Текущий баланс</div>
                        <div class="usr-balanse">{{databilling.UF_VALUE_ORIGINAL}} руб.</div>
                        <div class="form-block mt-5">
                            <div class="form-group row">
                                <div class="col-md-6">
                                    <label for="summa-popolneniya">Сумма пополнения, руб.</label>
                                    <div v-if="showError('summapopolneniya')" class="error-field">Вы не ввели сумму пополнения</div>
                                    <div class="d-flex align-items-center mb-3">
                                        <div>
                                            <input id="summa-popolneniya" name="summapopolneniya" class="form-control" style="text-align: right" type="text" v-model="fields.summapopolneniya" @input="onInput2">
                                        </div>
                                        <div class="ml-3">
                                            сумма удержания: {{sumpopolnenia}} руб.
                                        </div>
                                    </div>
                                    <label for="percent-popolneniya">Процент удержания</label>
                                    <input id="percent-popolneniya" name="percentpopolneniya" class="form-control" style="text-align: right;width: 200px;" type="text" v-model="fields.percentpopolneniya" @input="onInput2">
                                </div>
                            </div>
                            <div class="d-flex justify-content-center"><div v-if="isError" class="error-field">Ошибка при заполнении полей</div></div>
                            <div class="gotopay"><button class="btn btn-primary" type="button" @click="ondepositMoney">Пополнить</button></div>
                        </div>

                    </div>
                </div>
            </div>
        <?endif;?>

        <div id="toscroll"></div>

    </form>
</script>
<?/* КОНЕЦ ШАБЛОН */?>


<?
$context = \Bitrix\Main\Application::getInstance()->getContext();
$request = $context->getRequest();

$user = \Bitrix\Main\DI\ServiceLocator::getInstance()->get('user');
$usertype = \CUserOptions::GetOption('kabinet','usertype',false,$user->get('ID'));

$sL = \Bitrix\Main\DI\ServiceLocator::getInstance();
$contractDATA = $sL->get('Kabinet.Contract')->getData();
$bankDATA = $sL->get('Kabinet.Bankdata')->getData();

$typepay = $request->getPost('typepay');
$qrsumm = $request->getPost('qrsumm');

if ($typepay == NULL) $typepay = 0;
if ($qrsumm == NULL) $qrsumm = 0;

(\KContainer::getInstance())->get('userStore');
(\KContainer::getInstance())->get('billingStore');
\Bitrix\Main\Page\Asset::getInstance()->addJs(SITE_TEMPLATE_PATH."/assets/js/kabinet/applications/deposit.js");
?>
<script>
    const  AgreementStore = BX.Vue3.Pinia.defineStore('agreement-store', {
        state: () => ({
            contract:<?=CUtil::PhpToJSObject($contractDATA, false, true)?>,
            bank:<?=CUtil::PhpToJSObject($bankDATA, false, true)?>,
            contracttype:{value:<?=CUtil::PhpToJSObject($usertype, false, true)?>},
        })
    });

    window.addEventListener("components:ready", function(event) {
        deposit_form.start(<?=CUtil::PhpToJSObject([
            'CONTAINER' => '#depositcontent',
            'TEMPLATE' => '#deposit-template',
            'TYPEPAY'=> $typepay,
            'QRSUMM'=> $qrsumm,
        ], false, true)?>);
    });
</script>
