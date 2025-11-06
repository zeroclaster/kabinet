var client_filter_report = document.client_filter_report || {};
client_filter_report = (function () {
    return {
        start(PHPPARAMS) {
            $(function(){
                const $filterContainer = $("#filter-form-container");
                const $filterToggleBtn = $("#filter-toggle-btn");
                const $filterToggleText = $filterToggleBtn.find(".filter-toggle-text");
                const $filterToggleIcon = $filterToggleBtn.find(".filter-toggle-icon i");

                const $fromDatepicker = $("#fromdate1");
                const $toDatepicker = $("#todate1");
                const findform = BX.findChild(document.body,{attribute:{name:'clientfindreportform'}},true,false);

                // Находим чекбокс "Требует вашего внимания" вне формы
                const alertCheckbox = document.getElementById('alertfind');

                // Функция для получения куки
                function getCookie(name) {
                    const matches = document.cookie.match(new RegExp(
                        "(?:^|; )" + name.replace(/([\.$?*|{}\(\)\[\]\\\/\+^])/g, '\\$1') + "=([^;]*)"
                    ));
                    return matches ? decodeURIComponent(matches[1]) : undefined;
                }

                // Функция для установки куки
                function setCookie(name, value, days = 365) {
                    const date = new Date();
                    date.setTime(date.getTime() + days * 24 * 60 * 60 * 1000);
                    const expires = "expires=" + date.toUTCString();
                    document.cookie = name + "=" + encodeURIComponent(value) + ";" + expires + ";path=/";
                }

                // Уникальное имя куки для этого фильтра
                const cookieName = 'filter_state_' + (PHPPARAMS.COMPONENT_NAME || 'default');

                // Проверяем состояние фильтра из куки
                const filterState = getCookie(cookieName);
                const isFilterOpen = filterState === 'open';

                // Устанавливаем начальное состояние
                if (isFilterOpen) {
                    $filterContainer.show();
                    $filterToggleText.text('Скрыть фильтр');
                    $filterToggleIcon.removeClass('fa-chevron-down').addClass('fa-chevron-up');
                } else {
                    $filterContainer.hide();
                    $filterToggleText.text('Фильтр');
                    $filterToggleIcon.removeClass('fa-chevron-up').addClass('fa-chevron-down');
                }

                // Обработчик переключения фильтра
                BX.bind($filterToggleBtn[0], 'click', function(e) {
                    if ($filterContainer.is(":visible")) {
                        $filterContainer.slideUp(300);
                        $filterToggleText.text('Фильтр');
                        $filterToggleIcon.removeClass('fa-chevron-up').addClass('fa-chevron-down');
                        setCookie(cookieName, 'closed');
                    } else {
                        $filterContainer.slideDown(300);
                        $filterToggleText.text('Скрыть фильтр');
                        $filterToggleIcon.removeClass('fa-chevron-down').addClass('fa-chevron-up');
                        setCookie(cookieName, 'open');
                    }
                });

                // Обработчик для чекбокса "Требует вашего внимания"
                BX.bind(alertCheckbox, 'click', (e) => {
                    // Создаем скрытое поле в форме и отправляем
                    const hiddenInput = document.createElement('input');
                    hiddenInput.type = 'hidden';
                    hiddenInput.name = 'alert';
                    hiddenInput.value = alertCheckbox.checked ? 'y' : '';
                    findform.appendChild(hiddenInput);
                    findform.submit();
                });

                // Добавляем обработчик нажатия Enter для поля "ID исполнения"
                const queueInput = findform.querySelector('input[name="queue"]');
                BX.bind(queueInput, 'keypress', function(event) {
                    if (event.keyCode === 13) { // 13 - код клавиши Enter
                        event.preventDefault(); // Предотвращаем стандартное поведение
                        findform.submit(); // Отправляем форму
                    }
                });

                $fromDatepicker.datetimepicker({
                    locale: moment.locale('ru'),
                    format: 'DD.MM.YYYY',
                    //minDate: newDate.toDate()
                });

                $toDatepicker.datetimepicker({
                    locale: moment.locale('ru'),
                    format: 'DD.MM.YYYY',
                    //minDate: newDate.toDate()
                });

                const datepicker1 = $toDatepicker.data('DateTimePicker');
                const datepicker2 = $fromDatepicker.data('DateTimePicker');

                $fromDatepicker.on('dp.change', (event) => {
                    if (event.date) {
                        const newDate = moment(event.date, "DD.MM.YYYY");
                        let d = $toDatepicker.val();
                        datepicker1.minDate(newDate);
                        if (!d) datepicker1.date(null);
                    }else{
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
                    }else{
                        let d = $fromDatepicker.val();
                        datepicker2.maxDate(false);
                        if (!d) datepicker2.date(null);
                    }
                });

                BX.bind(BX('clearform'),'click',function (e) {
                    for(index in findform.elements)
                        findform.elements[index].value = '';

                    // Также сбрасываем внешний чекбокс
                    alertCheckbox.checked = false;

                    findform.clearflag.value = 'y';
                    findform.submit();
                });
            });
        }
    }
}());