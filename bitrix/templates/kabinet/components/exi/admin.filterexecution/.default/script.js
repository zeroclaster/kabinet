class FilterComponent {
    constructor() {
        this.searchResult = {};
        this.clients = [];
        this.projects = [];
        this.tasks = [];
        this.kabinetStore = usekabinetStore();
    }

    async loadDataClient() {
        try {
            const formData = new FormData();
            const response = await BX.ajax.runComponentAction(
                "exi:admin.filterclient",
                "getclients",
                {
                    mode: 'class',
                    data: formData,
                    timeout: 300
                }
            );

            this.clients = response.data;
            $('#clientidsearch').val(0);

            // Set user-selected value if exists
            if (this.searchResult.clientidsearch) {
                this.projects = [];
                this.tasks = [];

                $('#projectidsearch').val(0);
                $('#taskidsearch').val(0);

                const selectedClient = this.clients.find(
                    client => client.id == this.searchResult.clientidsearch
                );

                if (selectedClient) {
                    $('#search-client').typeahead('val', selectedClient.value);
                }

                // Load projects for selected client
                await this.loadDataProjects(this.searchResult.clientidsearch);
                $('#clientidsearch').val(this.searchResult.clientidsearch);
            }
        } catch (error) {
            this.handleError(error);
        }
    }

    async loadDataProjects(clientId = 0) {
        try {
            const formData = new FormData();
            formData.append("ID", clientId);

            const response = await BX.ajax.runComponentAction(
                "exi:admin.filterclient",
                "getproject",
                {
                    mode: 'class',
                    data: formData,
                    timeout: 300
                }
            );

            $('#projectidsearch').val(0);
            $('#taskidsearch').val(0);
            this.projects = response.data;

            // Reset task search if exists
            if (typeof $('#search-task').typeahead !== "undefined") {
                $('#search-task').typeahead('val', "");
                $('#search-task').typeahead('destroy');
            }

            this.initTypeahead(
                '#search-project',
                this.projects,
                'projectidsearch',
                (suggestion) => {
                    $('#projectidsearch').val(suggestion.id);
                    this.loadDataTasks(suggestion.id);
                }
            );

            // Set user-selected value if exists
            if (this.searchResult.projectidsearch) {
                this.tasks = [];
                $('#taskidsearch').val(0);

                const selectedProject = this.projects.find(
                    project => project.id == this.searchResult.projectidsearch
                );

                if (selectedProject) {
                    $('#search-project').typeahead('val', selectedProject.value);
                }

                await this.loadDataTasks(this.searchResult.projectidsearch);
                $('#projectidsearch').val(this.searchResult.projectidsearch);
            }
        } catch (error) {
            this.handleError(error);
        }
    }

    async loadDataTasks(projectId = 0) {
        try {
            const formData = new FormData();
            formData.append("ID", projectId);

            const response = await BX.ajax.runComponentAction(
                "exi:admin.filterclient",
                "gettask",
                {
                    mode: 'class',
                    data: formData,
                    timeout: 300
                }
            );

            this.tasks = response.data;
            this.initTypeahead(
                '#search-task',
                this.tasks,
                'taskidsearch',
                (suggestion) => {
                    $('#taskidsearch').val(suggestion.id);
                }
            );

            // Set user-selected value if exists
            if (this.searchResult.taskidsearch) {
                const selectedTask = this.tasks.find(
                    task => task.id == this.searchResult.taskidsearch
                );

                if (selectedTask) {
                    $('#search-task').typeahead('val', selectedTask.value);
                }

                $('#taskidsearch').val(this.searchResult.taskidsearch);
            }
        } catch (error) {
            this.handleError(error);
        }
    }

    initTypeahead(selector, data, hiddenFieldId, onSelectCallback) {
        const input = $(selector);

        // Destroy existing typeahead if exists
        if (typeof input.typeahead !== "undefined") {
            input.typeahead('val', "");
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
                source: (q, cb) => {
                    const matches = data.filter(element =>
                        new RegExp(q, 'i').test(element.value)
                    );
                    cb(matches);
                }
            }
        );

        input.bind('typeahead:select', (ev, suggestion) => {
            onSelectCallback(suggestion);
        });
    }

    initClientTypeahead() {
        this.initTypeahead(
            '#search-client',
            this.clients,
            'clientidsearch',
            (suggestion) => {
                $('#clientidsearch').val(suggestion.id);
                this.loadDataProjects(suggestion.id);
            }
        );
    }

    initDatePickers() {
        this.initDateRangePicker(
            '#search-planedaterangefrom',
            '#search-planedaterangeto'
        );

        this.initDateRangePicker(
            '#search-publicdatefrom',
            '#search-publicdateto'
        );
    }

    initDateRangePicker(fromSelector, toSelector) {
        const $from = $(fromSelector);
        const $to = $(toSelector);

        $from.datetimepicker({
            locale: moment.locale('ru'),
            format: 'DD.MM.YYYY'
        });

        $to.datetimepicker({
            locale: moment.locale('ru'),
            format: 'DD.MM.YYYY'
        });

        const toPicker = $to.data('DateTimePicker');
        const fromPicker = $from.data('DateTimePicker');

        $from.on('dp.change', (event) => {
            if (event.date) {
                const newDate = moment(event.date, "DD.MM.YYYY");
                toPicker.minDate(newDate);
                if (!$to.val()) toPicker.date(null);
            } else {
                toPicker.minDate(false);
                if (!$to.val()) toPicker.date(null);
            }
        });

        $to.on('dp.change', (event) => {
            if (event.date) {
                const newDate = moment(event.date, "DD.MM.YYYY");
                fromPicker.maxDate(newDate);
                if (!$from.val()) fromPicker.date(null);
            } else {
                fromPicker.maxDate(false);
                if (!$from.val()) fromPicker.date(null);
            }
        });
    }

    handleError(error) {
        error.errors.forEach(err => {
            this.kabinetStore.Notify = '';
            this.kabinetStore.Notify = err.message;
        });
    }

    initFormHandlers() {
        const form = BX.findChild(document.body, { attribute: { name: 'filterform1' } }, true, false);

        BX.bind(form, 'submit', (event) => {
            const form = event.target;

            // Reset IDs if text fields are empty
            if (!form.elements.clienttextsearch.value) form.elements.clientidsearch.value = '0';
            if (!form.elements.projecttextsearch.value) form.elements.projectidsearch.value = '0';
            if (!form.elements.tasktextsearch.value) form.elements.taskidsearch.value = '0';

            // Check if any filter is selected
            const hasFilters = Array.from(form.elements).some(node => {
                if (node.type === 'hidden' && node.value) return true;
                if (node.type === 'text' && node.value) return true;
                if (node.type === 'select-one' && node.value) return true;
                if (node.type === 'radio' && node.checked) return true;
                return false;
            });

            if (!hasFilters) {
                this.kabinetStore.Notify = 'Вы не выбрали ни одного поля!';
                event.preventDefault();
                event.stopPropagation();
                return false;
            }
        });

        // Clear filter handler
        BX.bind(BX("clearfilter"), 'click', (e) => {
            Array.from(form.elements).forEach(input => {
                input.value = '';
            });
            form.submit();
            e.preventDefault();
            e.stopPropagation();
            return false;
        });

        // "Requires attention" checkboxes
        const alertBlock = BX.findChild(form, { class: 'alert-filter-block' }, true, false);
        const checkboxes = BX.findChild(alertBlock, { tag: 'input' }, true, true);
        checkboxes.forEach(checkbox => {
            BX.bind(checkbox, 'change', () => form.submit());
        });
    }

    async init(phpParams) {
        this.searchResult = phpParams.SEARCH_RESULT || {};

        $(() => {
            this.initDatePickers();

            window.addEventListener("components:ready", async () => {
                await this.loadDataClient();

                if (!this.searchResult.clientidsearch) await this.loadDataProjects();
                if (!this.searchResult.projectidsearch) await this.loadDataTasks();

                this.initClientTypeahead();
                this.initFormHandlers();
            });
        });
    }
}

const filter = new FilterComponent();