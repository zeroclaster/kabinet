const  briefFormStore = BX.Vue3.Pinia.defineStore('briefForm', {
    state: () => (briefFormStoreData),
    actions: {
        setAction(action)
        {
        },
    },
});

const formApplication = BX.Vue3.BitrixVue.createApp({
    data() {
        return {
            id:0,
            action: '',
            verific_cond:{},
            counter: 0,
			asx:[],
			moreitems:[1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1]
        }
    },
    computed: {
        ...BX.Vue3.Pinia.mapState(briefFormStore, ['fields']),
        ...BX.Vue3.Pinia.mapState(usekabinetStore, ['Notify','NotifyOk'])
    },
    methods: {
        submit:function (form){
            const action = this.action;
            var cur = this;

            let formData = new FormData(form);
            formData.append('id', this.id);
            const kabinetStore = usekabinetStore();
            BX.ajax.runAction('bitrix:kabinet.evn.briefevents.'+action, {
                data : formData,
                // usr_id_const нужен для админа, задается в footer.php
                getParameters: {usr : usr_id_const}
                //processData: false,
                //preparePost: false
            })
                .then(function(response) {
                    const data = response.data;
                    kabinetStore.NotifyOk = '';
                        kabinetStore.NotifyOk = data.message;
                        cur.id = data.id;
                    //console.log(data)
                }, function (response) {
                    //console.log(response);
                    response.errors.forEach((error) => {
                        kabinetStore.Notify = '';
                        kabinetStore.Notify = error.message;
                    });

                });
        },
        checkForm: function (e) {
            const kabinetStore = usekabinetStore();
            const form = e.target;
            let err_flag = false;
            console.log('form submit!');
            //debugger;
            for (let [key, params] of Object.entries(this.fields)) {
                if(params.required && !params.value){
                    kabinetStore.Notify = 'Вы не заполнили обязательное поле '+params['EDIT_FORM_LABEL'];
                    err_flag = true;
                }
                if (params.preg){
                    const regex = new RegExp(params.preg,"igmu");
                    let result = regex.test(params.value);
                    if (!result) {
                        kabinetStore.Notify = 'Вы неправильно заполнили поле '+params['EDIT_FORM_LABEL'];
                        err_flag = true;
                    }
                }
            }

            // for debug!
            //this.submit(form);

            if (!err_flag){
                this.submit(form);
            }

            e.preventDefault();
        }
    },
    mounted() {
        BX.message(this.message);
        BX.MFInput.init(this.settinginp);

    },
        // language=Vue
    template: '#kabinet-content'
});
formApplication.use(store);
formApplication.mount('#kabinetcontent');