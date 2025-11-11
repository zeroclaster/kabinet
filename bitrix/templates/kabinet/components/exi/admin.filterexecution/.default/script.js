const filter1 = {
    seach_result: [],
    allData: {
        clients: [],
        projects: [],
        tasks: [],
        executions: [],
        responsibles: []
    },

    // Функция для экранирования спецсимволов в регулярных выражениях
    escapeRegExp(string) {
        return string.replace(/[.*+?^${}()|[\]\\]/g, '\\$&');
    },

    // Загружаем все данные сразу
    loadAllData_() {
        const this_ = this;
        let formData = new FormData;
        const kabinetStore = usekabinetStore();

        var data = BX.ajax.runComponentAction("exi:admin.filterexecution", "getalldata", {
            mode: 'class',
            data: formData,
            timeout: 300
        }).then(function (response) {
            this_.allData = response.data;
            this_.initializeTypeaheads();
            this_.setInitialValues();

        }, function (response) {
            kabinet.loading(false);
            if (response.errors[0].code != 0) {
                kabinetStore.Notify = '';
                kabinetStore.Notify = response.errors[0].message;
            } else {
                kabinetStore.Notify = '';
                kabinetStore.Notify = "Возникла системная ошибка! Пожалуйста обратитесь к администратору сайта.";
            }
        });
    },

    // Обновляем метод loadAllData
    loadAllData() {
        const this_ = this;
        let formData = new FormData;
        const kabinetStore = usekabinetStore();

        var data = BX.ajax.runComponentAction("exi:admin.filterexecution", "getalldata", {
            mode: 'class',
            data: formData,
            timeout: 300
        }).then(function (response) {
            this_.allData = response.data;
            this_.initializeTypeaheads();
            this_.setInitialValues();

        }, function (response) {
            kabinet.loading(false);
            if (response.errors[0].code != 0) {
                kabinetStore.Notify = '';
                kabinetStore.Notify = response.errors[0].message;
            } else {
                kabinetStore.Notify = '';
                kabinetStore.Notify = "Возникла системная ошибка! Пожалуйста обратитесь к администратору сайта.";
            }
        });
    },

    // Инициализация всех typeahead полей
    initializeTypeaheads() {
        this.initializeClientTypeahead();
        this.initializeProjectTypeahead();
        this.initializeTaskTypeahead();
        this.initializeExecutionTypeahead();
        this.initializeResponsibleTypeahead();
    },

    // Добавляем инициализацию typeahead для ответственного
    initializeResponsibleTypeahead() {
        const this_ = this;
        var input = $('#search-responsible');

        if (typeof input.typeahead !== "undefined") {
            input.typeahead('destroy');
        }

        input.typeahead(
            {
                hint: true,
                highlight: true,
                minLength: 0
            },
            {
                limit: 1000000,
                name: input.attr('placeholder'),
                displayKey: 'value',
                source: function findMatches(q, cb) {
                    let matches = [];
                    // Экранируем спецсимволы в поисковом запросе
                    const escapedQuery = this_.escapeRegExp(q);
                    this_.allData.responsibles.forEach(function (element) {
                        if ((new RegExp(escapedQuery, 'i')).test(element.value)) matches.push(element);
                    });
                    cb(matches);
                }
            }
        );

        input.bind('typeahead:select', function (ev, suggestion) {
            $('#responsibleidsearch').val(suggestion.id);
        });

        input.bind('typeahead:change', function (ev) {
            if (!ev.target.value) {
                $('#responsibleidsearch').val('0');
            }
        });
    },

    // Инициализация поля клиента
    initializeClientTypeahead() {
        const this_ = this;
        var input = $('#search-client');

        if (typeof input.typeahead !== "undefined") {
            input.typeahead('destroy');
        }

        input.typeahead(
            {
                hint: true,
                highlight: true,
                minLength: 0
            },
            {
                limit: 1000000,
                name: input.attr('placeholder'),
                displayKey: 'value',
                source: function findMatches(q, cb) {
                    let matches = [];
                    // Экранируем спецсимволы в поисковом запросе
                    const escapedQuery = this_.escapeRegExp(q);
                    this_.allData.clients.forEach(function (element) {
                        if ((new RegExp(escapedQuery, 'i')).test(element.value)) matches.push(element);
                    });
                    cb(matches);
                }
            }
        );

        input.bind('typeahead:select', function (ev, suggestion) {
            $('#clientidsearch').val(suggestion.id);
        });

        input.bind('typeahead:change', function (ev) {
            if (!ev.target.value) {
                $('#clientidsearch').val('0');
            }
        });
    },

    // Инициализация поля проекта
    initializeProjectTypeahead() {
        const this_ = this;
        var input = $('#search-project');

        if (typeof input.typeahead !== "undefined") {
            input.typeahead('destroy');
        }

        input.typeahead(
            {
                hint: true,
                highlight: true,
                minLength: 0
            },
            {
                limit: 1000000,
                name: input.attr('placeholder'),
                displayKey: 'value',
                source: function findMatches(q, cb) {
                    let matches = [];
                    // Экранируем спецсимволы в поисковом запросе
                    const escapedQuery = this_.escapeRegExp(q);
                    this_.allData.projects.forEach(function (element) {
                        if ((new RegExp(escapedQuery, 'i')).test(element.value)) matches.push(element);
                    });
                    cb(matches);
                }
            }
        );

        input.bind('typeahead:select', function (ev, suggestion) {
            $('#projectidsearch').val(suggestion.id);
        });

        input.bind('typeahead:change', function (ev) {
            if (!ev.target.value) {
                $('#projectidsearch').val('0');
            }
        });
    },

    // Инициализация поля задачи
    initializeTaskTypeahead() {
        const this_ = this;
        var input = $('#search-task');

        if (typeof input.typeahead !== "undefined") {
            input.typeahead('destroy');
        }

        input.typeahead(
            {
                hint: true,
                highlight: true,
                minLength: 0
            },
            {
                limit: 1000000,
                name: input.attr('placeholder'),
                displayKey: 'value',
                source: function findMatches(q, cb) {
                    let matches = [];
                    // Экранируем спецсимволы в поисковом запросе
                    const escapedQuery = this_.escapeRegExp(q);
                    this_.allData.tasks.forEach(function (element) {
                        if ((new RegExp(escapedQuery, 'i')).test(element.value)) matches.push(element);
                    });
                    cb(matches);
                }
            }
        );

        input.bind('typeahead:select', function (ev, suggestion) {
            $('#taskidsearch').val(suggestion.id);
        });

        input.bind('typeahead:change', function (ev) {
            if (!ev.target.value) {
                $('#taskidsearch').val('0');
            }
        });
    },

    // Инициализация поля execution ID
    initializeExecutionTypeahead() {
        const this_ = this;
        var input = $('#search-executionid');

        if (typeof input.typeahead !== "undefined") {
            input.typeahead('destroy');
        }

        input.typeahead(
            {
                hint: true,
                highlight: true,
                minLength: 0
            },
            {
                limit: 1000000,
                name: input.attr('placeholder'),
                displayKey: 'value',
                source: function findMatches(q, cb) {
                    let matches = [];

                    // Убираем символ # если пользователь его ввел
                    let searchQuery = q.replace(/^#/, '');
                    // Экранируем спецсимволы в поисковом запросе
                    const escapedQuery = this_.escapeRegExp(searchQuery);

                    this_.allData.executions.forEach(function (element) {
                        // Ищем по полному значению (с #) и по числовому значению extKey
                        const escapedFullQuery = this_.escapeRegExp(q);
                        if ((new RegExp(escapedFullQuery, 'i')).test(element.value) ||
                            (new RegExp(escapedQuery, 'i')).test(element.extKey)) {
                            matches.push(element);
                        }
                    });

                    // Сортируем по extKey в порядке убывания (новые сначала)
                    matches.sort(function(a, b) {
                        return b.extKey - a.extKey;
                    });

                    cb(matches);
                }
            }
        );

        input.bind('typeahead:select', function (ev, suggestion) {
            $('#executionidsearch').val(suggestion.id);
        });

        input.bind('typeahead:change', function (ev) {
            if (!ev.target.value) {
                $('#executionidsearch').val('0');
            } else {
                // Если пользователь ввел значение вручную, пытаемся найти соответствие
                let manualValue = ev.target.value.replace(/^#/, '');
                if (manualValue && !$('#executionidsearch').val()) {
                    // Ищем точное соответствие по extKey
                    let found = false;
                    for (let element of this_.allData.executions) {
                        if (element.extKey == manualValue) {
                            $('#executionidsearch').val(element.id);
                            found = true;
                            break;
                        }
                    }
                    if (!found) {
                        $('#executionidsearch').val('0');
                    }
                }
            }
        });
    },

    // Установка начальных значений из поискового результата
    setInitialValues() {

        // Устанавливаем значение ответственного
        if (typeof this.seach_result.responsibleidsearch !== 'undefined' && this.seach_result.responsibleidsearch != '0') {
            for (let element of this.allData.responsibles) {
                if (element.id == this.seach_result.responsibleidsearch) {
                    $('#search-responsible').typeahead('val', element.value);
                    $('#responsibleidsearch').val(element.id);
                    break;
                }
            }
        }

        // Устанавливаем текстовое значение если есть
        if (this.seach_result.responsibletextsearch && !this.seach_result.responsibleidsearch) {
            $('#search-responsible').val(this.seach_result.responsibletextsearch);
        }

        // Устанавливаем значение клиента
        if (typeof this.seach_result.clientidsearch !== 'undefined' && this.seach_result.clientidsearch != '0') {
            for (let element of this.allData.clients) {
                if (element.id == this.seach_result.clientidsearch) {
                    $('#search-client').typeahead('val', element.value);
                    $('#clientidsearch').val(element.id);
                    break;
                }
            }
        }

        // Устанавливаем значение проекта
        if (typeof this.seach_result.projectidsearch !== 'undefined' && this.seach_result.projectidsearch != '0') {
            for (let element of this.allData.projects) {
                if (element.id == this.seach_result.projectidsearch) {
                    $('#search-project').typeahead('val', element.value);
                    $('#projectidsearch').val(element.id);
                    break;
                }
            }
        }

        // Устанавливаем значение задачи
        if (typeof this.seach_result.taskidsearch !== 'undefined' && this.seach_result.taskidsearch != '0') {
            for (let element of this.allData.tasks) {
                if (element.id == this.seach_result.taskidsearch) {
                    $('#search-task').typeahead('val', element.value);
                    $('#taskidsearch').val(element.id);
                    break;
                }
            }
        }

        // Устанавливаем значение execution
        if (typeof this.seach_result.executionidsearch !== 'undefined' && this.seach_result.executionidsearch != '0') {
            for (let element of this.allData.executions) {
                if (element.id == this.seach_result.executionidsearch) {
                    $('#search-executionid').typeahead('val', element.value);
                    $('#executionidsearch').val(element.id);
                    break;
                }
            }
        }

        // Устанавливаем текстовые значения если есть
        if (this.seach_result.clienttextsearch && !this.seach_result.clientidsearch) {
            $('#search-client').val(this.seach_result.clienttextsearch);
        }
        if (this.seach_result.projecttextsearch && !this.seach_result.projectidsearch) {
            $('#search-project').val(this.seach_result.projecttextsearch);
        }
        if (this.seach_result.tasktextsearch && !this.seach_result.taskidsearch) {
            $('#search-task').val(this.seach_result.tasktextsearch);
        }
        if (this.seach_result.executiontextsearch && !this.seach_result.executionidsearch) {
            $('#search-executionid').val(this.seach_result.executiontextsearch);
        }
    },

    showTable() {
        const form = document.forms.filterform1;

        // Сохраняем оригинальные значения action и target
        const originalAction = form.action;
        const originalTarget = form.target;

        // Автоматически сбрасываем hidden поля если текстовые поля пустые
        if (form.elements.clienttextsearch.value == '') form.elements.clientidsearch.value = '0';
        if (form.elements.projecttextsearch.value == '') form.elements.projectidsearch.value = '0';
        if (form.elements.tasktextsearch.value == '') form.elements.taskidsearch.value = '0';
        if (form.elements.executiontextsearch.value == '') form.elements.executionidsearch.value = '0';

        // Временно меняем action формы и отправляем
        form.action = '/kabinet/admin/table/';
        form.target = '_blank';
        form.submit();

        // НЕМЕДЛЕННО возвращаем оригинальные значения
        setTimeout(() => {
            form.action = originalAction;
            form.target = originalTarget;
        }, 0);
    },

    init(phpparams) {
        const this_ = this;
        this_.seach_result = phpparams.SEARCH_RESULT;

        $(function () {
            // Инициализация datepicker'ов (оставляем без изменений)
            const $fromDatepicker = $("#search-planedaterangefrom");
            const $toDatepicker = $("#search-planedaterangeto");

            $fromDatepicker.datetimepicker({
                locale: moment.locale('ru'),
                format: 'DD.MM.YYYY',
            });

            $toDatepicker.datetimepicker({
                locale: moment.locale('ru'),
                format: 'DD.MM.YYYY',
            });

            const datepicker1 = $toDatepicker.data('DateTimePicker');
            const datepicker2 = $fromDatepicker.data('DateTimePicker');

            $fromDatepicker.on('dp.change', (event) => {
                if (event.date) {
                    const newDate = moment(event.date, "DD.MM.YYYY");
                    let d = $toDatepicker.val();
                    datepicker1.minDate(newDate);
                    if (!d) datepicker1.date(null);
                } else {
                    let d = $toDatepicker.val();
                    datepicker1.minDate(false);
                    if (!d) datepicker1.date(null);
                }
            });

            $toDatepicker.on('dp.change', (event) => {
                if (event.date) {
                    const newDate = moment(event.date, "DD.MM.YYYY");
                    let d = $fromDatepicker.val();
                    datepicker2.maxDate(newDate);
                    if (!d) datepicker2.date(null);
                } else {
                    let d = $fromDatepicker.val();
                    datepicker2.maxDate(false);
                    if (!d) datepicker2.date(null);
                }
            });

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
            // Загружаем все данные сразу
            this_.loadAllData();

            // Обработчики формы
            const form = BX.findChild(document.body, { attribute: { name: 'filterform1' } }, true, false);
            BX.bind(form, 'submit', function (event) {
                const form = event.target;

                // Автоматически сбрасываем hidden поля если текстовые поля пустые
                if (form.elements.clienttextsearch.value == '') form.elements.clientidsearch.value = '0';
                if (form.elements.projecttextsearch.value == '') form.elements.projectidsearch.value = '0';
                if (form.elements.tasktextsearch.value == '') form.elements.taskidsearch.value = '0';
                if (form.elements.executiontextsearch.value == '') form.elements.executionidsearch.value = '0';

                let sum = 0;
                for (node of form.elements) {
                    if (node.type == 'hidden' && node.value && node.value != '0') sum = sum + parseInt(node.value);
                    if (node.type == 'text' && node.value) sum = sum + node.value.length;
                    if (node.type == 'select-one' && node.value) sum = sum + parseInt(node.value);
                    if (node.type == 'radio' && node.checked) sum = sum + 1;
                    if (node.type == 'checkbox' && node.checked) sum = sum + 1;
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


            // Обработчик для кнопки "Показать таблицу"
            BX.bind(BX("showtablemode"), 'click', function (e) {
                this_.showTable();
                e.preventDefault();
                e.stopPropagation();
                return false;
            });

            // Обработчики изменения текстовых полей
            BX.bind(form.elements.clienttextsearch, 'change', function () {
                if (this.value && !$('#clientidsearch').val()) {
                    $('#clientidsearch').val('0');
                }
            });

            BX.bind(form.elements.projecttextsearch, 'change', function () {
                if (this.value && !$('#projectidsearch').val()) {
                    $('#projectidsearch').val('0');
                }
            });

            BX.bind(form.elements.tasktextsearch, 'change', function () {
                if (this.value && !$('#taskidsearch').val()) {
                    $('#taskidsearch').val('0');
                }
            });

            BX.bind(form.elements.executiontextsearch, 'change', function () {
                if (this.value && !$('#executionidsearch').val()) {
                    $('#executionidsearch').val('0');
                }
            });

            // Очистка фильтра
            BX.bind(BX("clearfilter"), 'click', function (e) {
                for (input of form.elements) {
                    if (input.type === 'text') {
                        input.value = '';
                    } else if (input.type === 'hidden') {
                        input.value = '0';
                    } else if (input.type === 'checkbox') {
                        input.checked = false;
                    } else if (input.type === 'radio') {
                        input.checked = false;
                    }
                }

                if (typeof $('#search-client').typeahead !== "undefined") {
                    $('#search-client').typeahead('val', '');
                }
                if (typeof $('#search-project').typeahead !== "undefined") {
                    $('#search-project').typeahead('val', '');
                }
                if (typeof $('#search-task').typeahead !== "undefined") {
                    $('#search-task').typeahead('val', '');
                }
                if (typeof $('#search-executionid').typeahead !== "undefined") {
                    $('#search-executionid').typeahead('val', '');
                }

                e.preventDefault();
                e.stopPropagation();
                return false;
            });

            // Авто-сабмит для радио кнопок "Требует внимания"
            const b = BX.findChild(form, { class: 'alert-filter-block' }, true, false);
            const inp = BX.findChild(b, { tag: 'input' }, true, true);
            inp.forEach(function (node) {
                BX.bind(node, 'change', function () { form.submit(); });
            })
        });
    }
};
