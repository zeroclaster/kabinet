/*
НЕ УДАЛЯТЬ!

Функционально не несет ничего
Нужно только для того что бы заработала Pinia
Uncaught Error: [🍍]: "getActivePinia()" was called but there was no active Pinia. Are you trying to use a store before calling "app.use(pinia)"?

Эта ошибка возникает при использовании Pinia (хранилища состояний Vue.js) до его инициализации в приложении. Давайте разберём решение:
Основные причины ошибки:
Вы пытаетесь использовать хранилище Pinia до вызова app.use(pinia)
Хранилище импортируется напрямую, а не через useStore()
Код выполняется вне компонента Vue
 */
const adminApplication = BX.Vue3.BitrixVue.createApp({
    data() {
        return {

        }
    },
    computed: {
        ...BX.Vue3.Pinia.mapState(usekabinetStore, ['Notify','NotifyOk']),
    },
    created(){
    },
    mounted() {
    },
    components: {
    },
    // language=Vue
    template: ''
});
adminApplication.use(store);
adminApplication.mount('#admincontent');