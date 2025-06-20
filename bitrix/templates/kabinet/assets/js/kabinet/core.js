'use strict';
const store = BX.Vue3.Pinia.createPinia();

const  usekabinetStore = BX.Vue3.Pinia.defineStore('kabinetStore',
    () => {
        const Notify = BX.Vue3.ref('');
        const NotifyOk = BX.Vue3.ref('');
		const config = BX.Vue3.ref(kabinetCongig);
        //const state = BX.Vue3.reactive({ Notify: '' })

        BX.Vue3.watch(()=>Notify, (newVal,oldVal) => {
            if (newVal.value)
            PNotify.alert({
                type: 'danger',
                title: 'Ошибка!',
                text: newVal.value,
                animation: 'fade',
                width: '300px',
                shadow: false,
                styling: 'bootstrap4',
                icons: 'fontawesome4'
            });

        }, { deep: true });

        BX.Vue3.watch(()=>NotifyOk, (newVal,oldVal) => {
            if (newVal.value)
            PNotify.alert({
                type: 'success',
                title: 'Выполнена!',
                text: newVal.value,
                animation: 'fade',
                width: '300px',
                shadow: false,
                styling: 'bootstrap4',
                icons: 'fontawesome4'
            });

        }, { deep: true });

        return { Notify, NotifyOk, config}
    }
    );


async function loadMoreDataExtended(options) {
    const {
        componentName,
        actionName = "loadmore",
        context,
        stores = {},
        filter = {},
        processMessages = false,
    } = options;

    const kabinetStore = usekabinetStore();
    try {
        kabinet.loading();
        context.$root.offset = (context.$root.offset || 0) + (context.countview || 25);

        const formData = new FormData();
        formData.append("OFFSET", context.$root.offset);
        formData.append("countview", context.countview);

        Object.entries(filter).forEach(([key, value]) => {
            if (value !== undefined) formData.append(key, value);
        });

        const response = await BX.ajax.runComponentAction(componentName, actionName, {
            mode: 'class',
            data: formData,
            timeout: 300,
        });

        kabinet.loading(false);
        const data = response.data;

        if (processMessages && data.MESSAGE_DATA) {
            const message_store = messageStore();
            Object.entries(data.MESSAGE_DATA).forEach(([index, message]) => {
                message_store.datamessage[index] = message;
            });
        }

        Object.entries(stores).forEach(([storeName, dataKey]) => {
            if (data[dataKey] !== undefined) {
                if (Array.isArray(context[storeName])) {
                    context[storeName].push(...data[dataKey]);
                } else {
                    Object.assign(context[storeName], data[dataKey]);
                }
            }
        });

    } catch (error) {
        kabinet.loading(false);
        const message = error.errors?.[0]?.message || "Системная ошибка. Обратитесь к администратору.";
        kabinetStore.Notify = message;
    }
}
