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