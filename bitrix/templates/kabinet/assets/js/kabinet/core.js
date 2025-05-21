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
