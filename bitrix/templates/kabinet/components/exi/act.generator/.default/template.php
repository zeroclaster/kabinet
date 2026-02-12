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

<div id="actcreator-container"></div>
<script type="text/html" id="act-template">
    <table class="table">
        <tbody>
        <tr>
            <td></td><td></td>
        </tr>
        <tr v-for="(work1,workdate) in dataworkslist">
            <td>
                <template v-if="work1 && work1.length > 0">
                    <a href="#" @click="downloadAct(workdate)">kupi-otziv.ru Акт №{{ formatActFilename(workdate) }} от {{ formatWorkDate(workdate) }}.docx</a>
                </template>
                <template v-else>
                    Нет акта
                </template>
            </td>
        </tr>
        <tr v-if="Object.keys(dataworkslist).length === 0">
            <td colspan="2">Нет данных за выбранный период</td>
        </tr>
        </tbody>
    </table>
</script>

<script>
    const  workslistStore = BX.Vue3.Pinia.defineStore('workslist', {
        state: () => ({dataworkslist:<?=CUtil::PhpToJSObject($arResult['groupedFulfillments'], false, true)?>}),
    });

    window.addEventListener("components:ready", function(event) {

        const actcreatorApplication = BX.Vue3.BitrixVue.createApp({
            data() {
                return {
                    err_message:''
                }
            },
            computed: {
                ...BX.Vue3.Pinia.mapState(AgreementFormStore, ['fields','contractsettings','fields2','banksettings','contracttype']),
                ...BX.Vue3.Pinia.mapState(userStore, ['datauser']),
                ...BX.Vue3.Pinia.mapState(workslistStore, ['dataworkslist']),
            },
            methods: {
                // Форматирование даты для отображения (день.месяц.год)
                formatWorkDate(date) {
                    // Ищем другие акты в этом же месяце
                    const currentMonth = moment(date).format('YYYY-MM');
                    let endActDate = null;

                    // Перебираем все ключи в dataworkslist
                    for (const key in this.dataworkslist) {
                        if (this.dataworkslist.hasOwnProperty(key)) {
                            // Проверяем, что ключ относится к тому же месяцу и больше текущего month
                            if (moment(key).format('YYYY-MM') === currentMonth && moment(key).isAfter(date)) {
                                // Если нашли более позднюю дату в том же месяце, сохраняем её
                                if (!endActDate || moment(key).isAfter(endActDate)) {
                                    endActDate = key;
                                }
                            }
                        }
                    }

                    if (endActDate){
                        return moment(endActDate).format('DD.MM.YYYY');
                    }else{
                        return moment(date).add(1, 'month').startOf('month').format('DD.MM.YYYY');
                    }
                },
                formatWorkDate2(date) {
                    return moment(date)
                        .format('DD.MM.YYYY');
                },

                // Форматирование имени файла акта (ddmm-yy + ID пользователя)
                formatActFilename(date) {
                    const formattedDate = moment(date).format('DDMM-YY');
                    return `${formattedDate}${this.datauser.ID}`;
                },
                downloadAct(month) {
                    const filename = this.formatActFilename(month);
                    if (this.isDownloading) return;
                    this.isDownloading = true;

                    // Получаем работы за выбранный месяц
                    const worksForMonth = this.dataworkslist[month] || [];

                    const formData = new FormData();
                    formData.append('usertype', this.contracttype.value);
                    formData.append('fio', this.fields.UF_FIO);
                    formData.append('inn', this.fields.UF_INN);
                    formData.append('mail_addres', this.fields.UF_MAILN_ADDRESS);
                    formData.append('idclient', this.datauser.ID);
                    formData.append('regdateclient', this.datauser.DATE_REGISTER);
                    formData.append('act', this.fields.UF_ACTS);
                    formData.append('nazvanie_organizacii', this.fields.UF_NAME);

                    if (this.datauser.UF_DOGOVOR_DATE) {
                        formData.append('dogovordate', moment(this.datauser.UF_DOGOVOR_DATE, 'DD.MM.YYYY HH:mm:ss').format('DD.MM.YYYY'));
                        formData.append('dogovorid', moment(this.datauser.UF_DOGOVOR_DATE, 'DD.MM.YYYY HH:mm:ss').format('DDMM-YY') + this.datauser.ID);
                    }else {
                        formData.append('dogovordate', "");
                        formData.append('dogovorid', "");
                    }


                    formData.append('month', month);
                    formData.append('sessid', BX.bitrix_sessid());

                    // Добавляем данные о работах в формате JSON
                    formData.append('works', JSON.stringify(worksForMonth));

                    // Ищем другие акты в этом же месяце
                    const currentMonth = moment(month).format('YYYY-MM');
                    let endActDate = null;

                    // Перебираем все ключи в dataworkslist
                    for (const key in this.dataworkslist) {
                        if (this.dataworkslist.hasOwnProperty(key)) {
                            // Проверяем, что ключ относится к тому же месяцу и больше текущего month
                            if (moment(key).format('YYYY-MM') === currentMonth && moment(key).isAfter(month)) {
                                // Если нашли более позднюю дату в том же месяце, сохраняем её
                                if (!endActDate || moment(key).isAfter(endActDate)) {
                                    endActDate = key;
                                }
                            }
                        }
                    }

                    // Если нашли более поздний акт в месяце - добавляем его дату
                    if (endActDate) {
                        formData.append('endactdate', endActDate);
                    } else {
                        // Иначе добавляем последний день месяца
                        const lastDayOfMonth = moment(month).endOf('month').format('YYYY-MM-DD');
                        formData.append('endactdate', lastDayOfMonth);
                    }

                    fetch('/ajax/dowload/act.php', {
                        method: 'POST',
                        body: formData
                    })
                        .then(response => response.blob())
                        .then(blob => {
                            const url = window.URL.createObjectURL(blob);
                            const a = document.createElement('a');
                            a.href = url;
                            a.download = `kupi-otziv.ru Акт №${filename}.docx`;
                            document.body.appendChild(a);
                            a.click();
                            window.URL.revokeObjectURL(url);
                            document.body.removeChild(a);
                            this.isDownloading = false;
                        })
                        .catch(error => {
                            console.error('Error:', error);
                            this.isDownloading = false;
                        });
                }
            },
            mounted() {
            },
            template: '#act-template'
        });

        configureVueApp(actcreatorApplication,'#actcreator-container');
    });
</script>
