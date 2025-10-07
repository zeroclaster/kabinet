/**
 * Класс для управления фильтром поиска клиентов в операциях с балансом
 * Обеспечивает поиск клиентов через typeahead и взаимодействие с Vue компонентом
 */
class BalanceFilter {
    constructor() {
        // Результаты поиска из PHP
        this.searchResult = [];
        // Список клиентов для поиска
        this.clients = [];
        // DOM элемент формы фильтра
        this.form = null;
    }

    /**
     * Загружает данные клиентов с сервера через AJAX
     * @async
     */
    async loadDataClient() {
        try {
            const formData = new FormData();
            const kabinetStore = usekabinetStore();

            // AJAX запрос к компоненту Bitrix для получения списка клиентов
            const response = await BX.ajax.runComponentAction(
                "exi:adminbalancecorrection",
                "searchClients",
                {
                    mode: 'class',
                    data: formData,
                    timeout: 300
                }
            );

            // Сохраняем полученных клиентов
            this.clients = response.data.clients || [];
            // Настраиваем поиск после загрузки данных
            this.setupClientSearch();

        } catch (error) {
            // Обработка ошибок при загрузке данных
            kabinet.loading(false);
            const message = error.errors?.[0]?.code !== 0
                ? error.errors[0].message
                : "Возникла системная ошибка! Пожалуйста обратитесь к администратору сайта.";

            kabinetStore.Notify = message;
        }
    }

    /**
     * Настраивает поиск клиентов после загрузки данных
     */
    setupClientSearch() {
        // Сбрасываем скрытое поле с ID клиента
        $('#clientidsearch').val(0);
        // Устанавливаем выбранного клиента из предыдущего поиска (если есть)
        this.setSelectedClientFromSearch();
        // Инициализируем typeahead для автодополнения
        this.initializeTypeahead();
    }

    /**
     * Восстанавливает выбранного клиента из результатов предыдущего поиска
     */
    setSelectedClientFromSearch() {
        // Если нет сохраненного ID клиента - выходим
        if (!this.searchResult.clientidsearch) return;

        // Ищем клиента в загруженном списке по ID
        const selectedClient = this.clients.find(
            client => client.id == this.searchResult.clientidsearch
        );

        // Если клиент найден - устанавливаем его в поле поиска
        if (selectedClient) {
            $('#search-client-balance').typeahead('val', selectedClient.value);
            $('#clientidsearch').val(this.searchResult.clientidsearch);
        }
    }

    /**
     * Инициализирует typeahead для поля поиска клиентов
     */
    initializeTypeahead() {
        const $input = $('#search-client-balance');

        // Очищаем предыдущий typeahead если он был инициализирован
        if (typeof $input.typeahead !== "undefined") {
            $input.typeahead('destroy');
        }

        // Настраиваем новый typeahead с автодополнением
        $input.typeahead(
            {
                hint: true,      // Показывать подсказку
                highlight: true, // Подсвечивать совпадения
                minLength: 0     // Минимальная длина для поиска (0 - поиск при любом вводе)
            },
            {
                limit: 200000,   // Максимальное количество результатов
                name: $input.attr('placeholder'),
                displayKey: 'value', // Поле для отображения в результатах
                source: (query, callback) => {
                    // Фильтруем клиентов по введенному запросу (регистронезависимо)
                    const matches = this.clients.filter(client =>
                        new RegExp(query, 'i').test(client.value)
                    );
                    callback(matches);
                }
            }
        );

        // Привязываем обработчики событий typeahead
        this.bindTypeaheadEvents($input);
    }

    /**
     * Привязывает обработчики событий к полю поиска
     * @param {jQuery} $input - jQuery объект поля ввода
     */
    bindTypeaheadEvents($input) {
        // Обработка выбора клиента из выпадающего списка
        $input.bind('typeahead:select', (event, suggestion) => {
            this.handleClientSelection(suggestion);
        });

        // Обработка изменения значения в поле ввода
        $input.bind('typeahead:change', (event) => {
            // Если поле очищено - сбрасываем выбранного клиента
            if (!$(event.target).val()) {
                this.handleClientClear();
            }
        });
    }

    /**
     * Обрабатывает выбор клиента из списка
     * @param {Object} client - Выбранный клиент
     */
    handleClientSelection(client) {
        // Устанавливаем ID выбранного клиента в скрытое поле
        $('#clientidsearch').val(client.id);
        // Обновляем Vue компонент с выбранным клиентом
        this.updateVueComponent(client.id);
        // Отправляем форму для применения фильтра
        this.submitForm();
    }

    /**
     * Обрабатывает очистку поля поиска
     */
    handleClientClear() {
        // Сбрасываем ID клиента
        $('#clientidsearch').val(0);
        // Обновляем Vue компонент (убираем выбранного клиента)
        this.updateVueComponent(null);
        // Отправляем форму для сброса фильтра
        this.submitForm();
    }

    /**
     * Обновляет Vue компонент операций с балансом при изменении выбранного клиента
     * @param {number|null} clientId - ID клиента или null для сброса
     */
    updateVueComponent(clientId) {
        // Получаем ссылку на Vue приложение операций с балансом
        const balanceApp = window.balanceOperationsApp;
        if (!balanceApp?._instance) return;

        // При выборе клиента форма автоматически отправится и сервер обновит dataclient
        // Нам не нужно вручную устанавливать selectedClient
    }

    /**
     * Отправляет форму фильтра
     */
    submitForm() {
        // Если форма уже найдена - отправляем её
        if (this.form) {
            this.form.submit();
            return;
        }

        // Ищем форму если еще не нашли
        const form = document.querySelector('form[name="balancefilter"]');
        if (form) {
            this.form = form;
            form.submit();
        }
    }

    /**
     * Привязывает обработчик к кнопке очистки фильтра
     */
    bindClearFilter() {
        const clearButton = document.getElementById('clearfilter');
        if (!clearButton) return;

        // Обработчик клика по кнопке очистки
        BX.bind(clearButton, 'click', (event) => {
            event.preventDefault();
            event.stopPropagation();

            // Очищаем все поля формы
            Array.from(this.form.elements).forEach(input => {
                input.value = '';
            });

            // Отправляем очищенную форму
            this.submitForm();
            return false;
        });
    }

    /**
     * Инициализирует фильтр
     * @param {Object} phpParams - Параметры из PHP
     */
    init(phpParams) {
        // Сохраняем результаты предыдущего поиска
        this.searchResult = phpParams.SEARCH_RESULT;

        // Инициализируем после готовности компонентов
        window.addEventListener("components:ready", () => {
            // Загружаем данные клиентов
            this.loadDataClient();

            // Находим форму фильтра
            this.form = document.querySelector('form[name="balancefilter"]');
            // Привязываем обработчик очистки фильтра
            this.bindClearFilter();
        });
    }
}

// Создаем глобальный экземпляр фильтра
const balanceFilter = new BalanceFilter();