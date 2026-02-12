class BillingViewApp extends BaseListApp {
    constructor(params = {}) {
        params.stores = {
            ...params.stores,
        };

        super(params);
        this.EXPENSES_NEXT_MONTH = params.PHPPARAMS?.EXPENSES_NEXT_MONTH;
        this.usr_id_const = usr_id_const;
    }

    getAppConfig() {
        const baseConfig = super.getAppConfig();

        return {
            ...baseConfig,
            data: () => ({
                ...baseConfig.data(),
                usr_id_const: '?usr=' + usr_id_const,
                // Добавляем offset в данные компонента
                offset: 0
            }),
            computed: {
                ...baseConfig.computed,
                ...BX.Vue3.Pinia.mapState(billingStore, ['databilling']),
                ...BX.Vue3.Pinia.mapState(historylistStore, ['historybillingdata']),
                viewedcount() {
                    return this.historybillingdata.length;
                },
                isAlertFinance() {
                    if (parseInt(this.databilling.UF_VALUE_ORIGINAL) < parseInt(this.EXPENSES_NEXT_MONTH)) return 'btn-danger';
                    return '';
                }
            },
            methods: {
                ...baseConfig.methods,
                project(history) {
                    if (!history.UF_PROJECT_ID) return false;

                    for (let p of this.data)
                        if (p.ID == history.UF_PROJECT_ID) return p;

                    return false;
                },
                task(history) {
                    if (!history.UF_TASK_ID) return false;

                    for (let t of this.datatask)
                        if (t.ID == history.UF_TASK_ID) return t;

                    return false;
                }
            },
            mounted() {
                // Правильный способ установки offset
                this.offset = 0;
                if (parseInt(this.total) <= parseInt(this.countview)) this.showloadmore = false;
            }
        };
    }

    processResponse(data) {
        if (typeof data.HISTORY_DATA !== "undefined" && data.HISTORY_DATA.length === 0) {
            this.app._instance.proxy.showloadmore = false;
        }

        // Add new history data
        if (typeof data.HISTORY_DATA !== "undefined") {
            for (let element of data.HISTORY_DATA) {
                this.app._instance.proxy.historybillingdata.push(element);
            }
        }

        // Обновляем offset после успешной загрузки
        this.app._instance.proxy.offset += data.HISTORY_DATA?.length || 0;

        if (this.app._instance.proxy.historybillingdata.length >= this.app._instance.proxy.total) {
            this.app._instance.proxy.showloadmore = false;
        }
    }

    prepareFormData() {
        const formData = super.prepareFormData();
        formData.append("countview", this.app._instance.proxy.countview);
        // Используем текущее значение offset
        formData.append("OFFSET", this.app._instance.proxy.offset);
        return formData;
    }
}