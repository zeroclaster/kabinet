class BaseListApp {
    constructor(params = {}) {
        // Устанавливаем значения по умолчанию
        this.componentName = params.componentName || "exi:billing.view";
        this.actionName = params.actionName || "loadmore";
        this.signedParameters = params.signedParameters || "";
        this.container = params.container || params.CONTAINER || '#kabinetcontent';

        this.stores = params.stores || {

        };

        this.initConfiguration(params);
        this.initApplication();
    }

    initConfiguration(params) {
        this.PHPPARAMS = params || {};
        this.filterclientlist = this.PHPPARAMS.FILTER || {};
        this.template = this.PHPPARAMS.TEMPLATE || '';
    }

    initApplication() {
        this.app = BX.Vue3.BitrixVue.createApp(this.getAppConfig());
        this.configureVueApp();
    }

    getDefaultComputed() {
        const computed = {};

        // Добавляем mapState для каждого хранилища
        for (const [storeName, properties] of Object.entries(this.stores)) {
            Object.assign(
                computed,
                BX.Vue3.Pinia.mapState(window[storeName], properties)
            );
        }

        return computed;
    }

    getAppConfig() {
        return {
            data: () => ({
                countview: this.PHPPARAMS.viewcount,
                total: this.PHPPARAMS.total,
                showloadmore: true,
                limitpics: 5
            }),
            methods: {
                moreload: (e) => this.handleMoreLoad(e),
                ...this.getAdditionalMethods()
            },
            computed: {
                ...BX.Vue3.Pinia.mapState(brieflistStore, ['data']),
                ...BX.Vue3.Pinia.mapState(tasklistStore, ['datatask']),
                ...BX.Vue3.Pinia.mapState(userStore, ['datauser']),
                ...this.getDefaultComputed(),
                ...this.getAdditionalComputed()
            },
            template: this.template // Добавляем template в базовую конфигурацию
        };
    }

    async handleMoreLoad(e) {
        e.preventDefault();
        try {
            kabinet.loading();
            const formData = this.prepareFormData();
            const response = await this.executeComponentAction(formData);
            this.processResponse(response.data);
        } catch (error) {
            this.handleError(error);
        } finally {
            kabinet.loading(false);
        }
    }

    prepareFormData() {
        const formData = new FormData();
        formData.append("OFFSET", this.offset);
        Object.entries(this.filterclientlist).forEach(([key, value]) => {
            formData.append(key, value);
        });
        return formData;
    }

    async executeComponentAction(formData) {
        return BX.ajax.runComponentAction(
            this.componentName,
            this.actionName,
            {
                mode: 'class',
                data: formData,
                signedParameters: this.signedParameters
            }
        );
    }

    configureVueApp() {
        this.app.config.globalProperties.$id = (indicator) =>
            `uid-${kabinet.uniqueId()}${indicator ? `-${indicator}` : ''}`;

        this.app.config.globalProperties.$href = (indicator) =>
            `#${this.$id(indicator)}`;

        this.app.use(store);
        this.app.mount(this.container);
    }

    processResponse(data) {
        // Базовая реализация, переопределяется в дочерних классах
    }

    handleError(error) {
        const kabinetStore = usekabinetStore();
        kabinetStore.Notify = error.errors?.[0]?.message ||
            "Возникла системная ошибка!";
    }

    getAdditionalMethods() {
        return {};
    }

    getAdditionalComputed() {
        return {};
    }
}