<?
use Bitrix\Main\Localization\Loc as Loc;

if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();
/** @var array $arParams */
/** @var array $arResult */
/** @global CMain $APPLICATION */
/** @global CUser $USER */
/** @global CDatabase $DB */
/** @var CBitrixComponentTemplate $this */
/** @var string $templateName */
/** @var string $templateFile */
/** @var string $templateFolder */
/** @var string $componentPath */
/** @var CBitrixComponent $component */

Loc::loadMessages(__FILE__);
$this->setFrameMode(true);
?>

<!-- Форма фильтра как в вашем примере -->
<form action="" name="balancefilter" enctype="multipart/form-data" method="post">
    <div class="row justify-content-md-center">
        <div class="col-md-8">
            <div class="row form-group">
                <div class="col-sm-3 text-sm-right">
                    <label class="col-form-label" for="search-client-balance">Клиент</label>
                </div>
                <div class="col-sm-9">
                    <input id="clientidsearch" name="clientidsearch" type="hidden" value="<?=$arResult['SEARCH_RESULT']['clientidsearch']?>">
                    <input value="<?=$arResult['SEARCH_RESULT']['clienttextsearch']?>"
                           name="clienttextsearch"
                           id="search-client-balance"
                           class="form-control"
                           type="text"
                           placeholder="начните вводить или выберите из списка"
                           data-typehead=''>
                </div>
            </div>
        </div>
    </div>
</form>

<div id="kabinetcontent" data-modalload=""></div>

<script type="text/html" id="balance-operations-content">
    <div class="panel">
        <div class="panel-body">
            <!-- Выбранный клиент -->
            <div v-if="currentClient" class="selected-client mt-4 p-3 border rounded bg-light">
                <h4>Выбран клиент: {{currentClient.NAME}} {{currentClient.LAST_NAME}}</h4>
                <p>Email: {{currentClient.EMAIL}} | ID: {{currentClient.ID}}</p>
            </div>

            <!-- Формы операций (показываются только когда выбран клиент) -->
            <div v-if="currentClient" class="operations-forms mt-4">

                <!-- Форма 1: Пополнение банковским переводом -->
                <div class="card mb-4 mt-5">
                    <div class="card-header">
                        <h4>Пополнение баланса банковским переводом</h4>
                    </div>
                    <div class="card-body">
                        <form @submit.prevent="submitBankTransfer">
                            <div class="form-group">
                                <label>Сумма поступления (руб.)</label>
                                <input type="text"
                                       class="form-control"
                                       :value="bankTransfer.amount"
                                       @input="handleAmountInput($event, 'bankTransfer')"
                                       placeholder="0.00"
                                       required
                                       pattern="[0-9]*\.?[0-9]*">
                                <small class="form-text text-muted">Максимальная сумма: 50 000 руб.</small>
                            </div>

                            <div class="form-group">
                                <label>Удержание 3%</label>
                                <input type="text"
                                       class="form-control"
                                       :value="calculateCommission"
                                       readonly
                                       style="background-color: #f8f9fa;">
                            </div>

                            <div class="form-group">
                                <label>Сумма пополнения баланса</label>
                                <input type="text"
                                       class="form-control"
                                       :value="calculateFinalAmount"
                                       readonly
                                       style="background-color: #f8f9fa; font-weight: bold;">
                            </div>

                            <div class="">
                                <div v-if="bankTransfer.message"
                                                :class="['alert', 'alert-sm', 'mb-0', bankTransfer.message.success ? 'alert-success' : 'alert-danger']">
                                    {{bankTransfer.message.text}}
                                </div>
                                <button type="submit"
                                        class="btn btn-primary"
                                        :disabled="!bankTransfer.amount || bankTransfer.amount <= 0 || bankTransfer.amount > 50000">
                                    Выполнить пополнение
                                </button>
                            </div>
                        </form>
                    </div>
                </div>

                <!-- Форма 2: Свободное пополнение -->
                <div class="card mb-4 mt-5">
                    <div class="card-header">
                        <h4>Свободное пополнение баланса</h4>
                    </div>
                    <div class="card-body">
                        <form @submit.prevent="submitFreeReplenishment">
                            <div class="form-group">
                                <label>Сумма пополнения баланса (руб.)</label>
                                <input type="text"
                                       class="form-control"
                                       :value="freeReplenishment.amount"
                                       @input="handleAmountInput($event, 'freeReplenishment')"
                                       placeholder="0.00"
                                       required
                                       pattern="[0-9]*\.?[0-9]*">
                                <small class="form-text text-muted">Максимальная сумма: 50 000 руб.</small>
                            </div>

                            <div class="form-group">
                                <label>Комментарий об операции</label>
                                <textarea class="form-control"
                                          v-model="freeReplenishment.comment"
                                          rows="3"
                                          placeholder="Введите комментарий к операции"></textarea>
                            </div>

                            <div class="d-flex align-items-center">
                                <button type="submit"
                                        class="btn btn-primary"
                                        :disabled="!freeReplenishment.amount || freeReplenishment.amount <= 0 || freeReplenishment.amount > 50000">
                                    Выполнить пополнение
                                </button>
                                <div class="ml-3">
                                    <div v-if="freeReplenishment.message"
                                         :class="['alert', 'alert-sm', 'mb-0', freeReplenishment.message.success ? 'alert-success' : 'alert-danger']">
                                        {{freeReplenishment.message.text}}
                                    </div>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>

                <!-- Форма 3: Списание с баланса -->
                <div class="card mb-4 mt-5">
                    <div class="card-header">
                        <h4>Списание с баланса</h4>
                    </div>
                    <div class="card-body">
                        <form @submit.prevent="submitWithdraw">
                            <div class="form-group">
                                <label>Сумма списания с баланса (руб.)</label>
                                <input type="text"
                                       class="form-control"
                                       :value="withdraw.amount"
                                       @input="handleAmountInput($event, 'withdraw')"
                                       placeholder="0.00"
                                       required
                                       pattern="[0-9]*\.?[0-9]*">
                                <small class="form-text text-muted">Максимальная сумма: 50 000 руб.</small>
                            </div>

                            <div class="form-group">
                                <label>Комментарий об операции</label>
                                <textarea class="form-control"
                                          v-model="withdraw.comment"
                                          rows="3"
                                          placeholder="Введите комментарий к операции"></textarea>
                            </div>

                            <div class="d-flex align-items-center">
                                <button type="submit"
                                        class="btn btn-danger"
                                        :disabled="!withdraw.amount || withdraw.amount <= 0 || withdraw.amount > 50000">
                                    Выполнить списание
                                </button>
                                <div class="ml-3">
                                    <div v-if="withdraw.message"
                                         :class="['alert', 'alert-sm', 'mb-0', withdraw.message.success ? 'alert-success' : 'alert-danger']">
                                        {{withdraw.message.text}}
                                    </div>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

            <!-- Сообщение если клиент не выбран -->
            <div v-if="!currentClient" class="alert alert-info mt-4">
                <h5>Для работы с балансом выберите клиента</h5>
                <p class="mb-0">Введите имя, email или логин клиента в поле поиска выше и выберите из списка</p>
            </div>

            <!-- Общие сообщения об операциях -->
            <div v-if="operationMessage"
                 :class="['alert', operationMessage.success ? 'alert-success' : 'alert-danger']"
                 class="mt-3">
                {{operationMessage.text}}
            </div>
        </div>
    </div>
</script>

<?
\Bitrix\Main\Page\Asset::getInstance()->addJs($templateFolder."/balance_filter.js");
\Bitrix\Main\Page\Asset::getInstance()->addJs($templateFolder."/balance_operations.js");

$jsParams = [
    'SEARCH_RESULT' => $arResult['SEARCH_RESULT']
];

?>
<script>
    // Инициализация фильтра
    balanceFilter.init(<?=CUtil::PhpToJSObject($jsParams, false, true)?>);
</script>

<script>
    const PHPPARAMS = <?=CUtil::PhpToJSObject([
        "componentName" => "exi:adminbalancecorrection",
        "signedParameters" => $this->getComponent()->getSignedParameters(),
    ], false, true)?>;

    window.addEventListener("components:ready", function(event) {
        const balanceOperationsApp = BX.Vue3.BitrixVue.createApp(balance_operations);

        balanceOperationsApp._component.data = () => ({
            dataclient: <?=CUtil::PhpToJSObject($arResult["CLIENT_DATA"], false, true)?>,
            total: Number(<?=$arResult["TOTAL"]?>),
            bankTransfer: {
                amount: 0,
                message: null
            },
            freeReplenishment: {
                amount: 0,
                comment: '',
                message: null
            },
            withdraw: {
                amount: 0,
                comment: '',
                message: null
            },
            operationMessage: null
        });

        configureVueApp(balanceOperationsApp);
    });
</script>
