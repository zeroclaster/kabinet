const filter1 = {
    seach_result: [],

    init(phpparams) {
        const this_ = this;
        this_.seach_result = phpparams.SEARCH_RESULT;

        $(function () {
            const $fromDatepickerPub = $("#search-publicdatefrom");
            const $toDatepickerPub = $("#search-publicdateto");

            $fromDatepickerPub.datetimepicker({
                locale: moment.locale('ru'),
                format: 'DD.MM.YYYY',
            });

            $toDatepickerPub.datetimepicker({
                locale: moment.locale('ru'),
                format: 'DD.MM.YYYY',
            });

            const datepicker1Pub = $toDatepickerPub.data('DateTimePicker');
            const datepicker2Pub = $fromDatepickerPub.data('DateTimePicker');

            $fromDatepickerPub.on('dp.change', (event) => {
                if (event.date) {
                    const newDate = moment(event.date, "DD.MM.YYYY");
                    let d = $toDatepickerPub.val();
                    datepicker1Pub.minDate(newDate);
                    if (!d) datepicker1Pub.date(null);
                } else {
                    let d = $toDatepickerPub.val();
                    datepicker1Pub.minDate(false);
                    if (!d) datepicker1Pub.date(null);
                }
            });

            $toDatepickerPub.on('dp.change', (event) => {
                if (event.date) {
                    const newDate = moment(event.date, "DD.MM.YYYY");
                    let d = $fromDatepickerPub.val();
                    datepicker2Pub.maxDate(newDate);
                    if (!d) datepicker2Pub.date(null);
                } else {
                    let d = $fromDatepickerPub.val();
                    datepicker2Pub.maxDate(false);
                    if (!d) datepicker2Pub.date(null);
                }
            });
        });

        window.addEventListener("components:ready", function (event) {
            // Обработчики формы
            const form = BX.findChild(document.body, { attribute: { name: 'filterform1' } }, true, false);

            BX.bind(form, 'submit', function (event) {
                const form = event.target;
                let sum = 0;

                for (node of form.elements) {
                    if (node.type == 'text' && node.value) sum = sum + node.value.length;
                }

                const kabinetStore = usekabinetStore();
                if (!sum) {
                    kabinetStore.Notify = '';
                    kabinetStore.Notify = 'Вы не выбрали не одного поля!';

                    event.preventDefault();
                    event.stopPropagation()
                    return false;
                }
            });

            // Очистка фильтра
            BX.bind(BX("clearfilter"), 'click', function (e) {
                for (input of form.elements) {
                    if (input.type === 'text') {
                        input.value = '';
                    }
                }

                e.preventDefault();
                e.stopPropagation();
                return false;
            });
        });
    }
};