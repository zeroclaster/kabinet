var profile_user = document.adminclient_list || {};
profile_user= (function (){
    return {
        start(PHPPARAMS){

            const userProfileApplication = BX.Vue3.BitrixVue.createApp({
                data() {
                    return {
                        password:{one:'',two:''},
                    }
                },
                computed: {
                    ...BX.Vue3.Pinia.mapState(userStore, ['datauser']),
                },
                methods: {
                    savepassword() {
                        const kabinetStore = usekabinetStore();
                        if (this.password.one != this.password.two) {
                            kabinetStore.Notify = "Введенные пароли не равны";
                        }
                    },
                        savefields(){
                            var cur = this;

                            kabinet.loading();

                            var form_data = new FormData();
                            for ( var key in this.datauser ) {

                                if (key=="PERSONAL_PHOTO"){
                                    for (const file of this.datauser[key]) {
                                        form_data.append(key , file);
                                    }
                                }

                                    if (Array.isArray(this.datauser[key])){
                                        this.datauser[key].forEach(function (item,index) {
                                            form_data.append(key + '[]', item.VALUE);
                                        });
                                    }else
                                        form_data.append(key, this.datauser[key]);
                            }



                            const kabinetStore = usekabinetStore();
                            BX.ajax.runAction('bitrix:kabinet.evn.userevents.edit', {
                                data : form_data,
                                // usr_id_const нужен для админа, задается в footer.php
                                getParameters: {usr : usr_id_const},
                                //processData: false,
                                //preparePost: false
                            })
                                .then(function(response) {
                                    console.log(response)
                                    const data = response.data;
                                    kabinetStore.NotifyOk = '';
                                    kabinetStore.NotifyOk = data.message;


                                    for (name in data.fields){
                                        cur.datauser[name] = data.fields[name];
                                    }

                                    kabinet.loading(false);
                                }, function (response) {
                                    //console.log(response);
                                    kabinet.loading(false);
                                    response.errors.forEach((error) => {
                                        kabinetStore.Notify = '';
                                        kabinetStore.Notify = error.message;
                                    });

                                });

                    },
                    saveevent(){
                        const kabinetStore = usekabinetStore();
                        kabinetStore.Notify = '';
                        kabinetStore.Notify = "Находится в рвзработке!";
                    },
                    onChangeFile(event) {
                        var cur = this;
                        const kabinetStore = usekabinetStore();

                        this.previmg = [];

                        for (let file of event.target.files){
                            if ((typeof file.type !== "undefined" ? file.type.match('image.*') : file.name.match('\\.(gif|png|jpe?g)$')) && typeof FileReader !== "undefined") {
                                /*
                                var reader = new FileReader();
                                reader.onload = function(e) {
                                    cur.previmg.push({src:e.target.result,name:file.name});
                                }

                                reader.readAsDataURL(file)
                                */
                            }else{
                                kabinetStore.Notify = "Error file type";
                                event.target.value = '';
                                return false;
                            }
                        }

                        console.log(event.target.files)
                        this.datauser ["PERSONAL_PHOTO"] = event.target.files;
                        this.savefields();
                    }
                },
                created(){
                },
                mounted() {
                    var cur = this;
                },
                components: {
                },
                // language=Vue
                template: '#kabinet-content'
            });


            const componentCounters = new WeakMap()
            // The "this" object is the current component instance.
            const getId = function (indicator) {
                if (!componentCounters.has(this)) {
                    componentCounters.set(this, kabinet.uniqueId())
                }
                const componentCounter = componentCounters.get(this)
                return `uid-${componentCounter}` + (indicator ? `-${indicator}` : '')
            }
            userProfileApplication.config.globalProperties.$href = function (indicator) {
                return `#${getId.call(this, indicator)}` }

            userProfileApplication.config.globalProperties.$id = getId;

            userProfileApplication.use(store);
            userProfileApplication.mount('#kabinetcontent');
        }
    }
}());