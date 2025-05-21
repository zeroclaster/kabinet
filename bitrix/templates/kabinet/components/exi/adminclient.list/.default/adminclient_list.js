const adminclient_list = {
    data() {
        return {
        }
    },

    computed: {
        isViewMore() {
            return this.total > this.countview && this.showloadmore;
        },

        viewedcount() {
            return this.dataclient?.length || 0;
        }
    },

    methods: {
        ...helperVueComponents(),

        async moreload(e) {
            e.preventDefault();
            const kabinetStore = usekabinetStore();

            try {
                kabinet.loading();
                this.$root.offset = (this.$root.offset || 0) + 2;

                const formData = new FormData();
                formData.append("OFFSET", this.$root.offset);
                formData.append("countview", this.countview);

                // Добавляем поля фильтра
                Object.entries(filterclientlist).forEach(([key, value]) => {
                    formData.append(key, value);
                });

                const response = await BX.ajax.runComponentAction(
                    "exi:adminclient.list",
                    "loadmore",
                    {
                        mode: 'class',
                        data: formData,
                        timeout: 300
                    }
                );

                kabinet.loading(false);
                this.processResponseData(response.data);

            } catch (error) {
                kabinet.loading(false);
                this.handleLoadError(error, kabinetStore);
            }

            return false;
        },

        processResponseData(data) {
            // Обработка клиентов
            if (data.CLIENT_DATA?.length) {
                this.dataclient.push(...data.CLIENT_DATA);
                this.showloadmore = data.CLIENT_DATA.length < this.total;
            } else {
                this.showloadmore = false;
            }

            // Обработка проектов
            if (data.PROJECT_DATA) {
                Object.assign(this.dataproject, data.PROJECT_DATA);
            }

            // Обработка задач
            if (data.TASK_DATA) {
                Object.assign(this.datatask, data.TASK_DATA);
            }

            // Обработка заказов
            if (data.ORDER_DATA) {
                Object.assign(this.dataorder, data.ORDER_DATA);
            }

            // Обработка исполнений
            if (data.RUNNER_DATA) {
                Object.assign(this.datarunner, data.RUNNER_DATA);
            }
        },

        handleLoadError(error, kabinetStore) {
            const message = error.errors?.[0]?.code !== 0
                ? error.errors[0].message
                : "Возникла системная ошибка! Пожалуйста обратитесь к администратору сайта.";

            kabinetStore.Notify = message;
        },

        getClientExecution(clientId) {
            if (!this.datatask[clientId] || !Array.isArray(this.datatask[clientId])) {
                return [];
            }

            return this.datatask[clientId]
                .flatMap(task =>
                    this.datarunner[clientId]?.[task.ID]
                        ? [...this.datarunner[clientId][task.ID]]
                        : []
                );
        },

        badTask(task_id, client_id, order_id, product_id) {
            if (!this.dataorder[client_id]?.[order_id]?.[product_id]) {
                console.warn(`Ошибка в задаче (${task_id})! В проекте нет услуги (${product_id}) из задачи.`);
            }
        }
    },

    mounted() {
        this.$root.offset = 0;
        this.showloadmore = this.total > this.countview;
    },

    template: '#kabinet-content'
};