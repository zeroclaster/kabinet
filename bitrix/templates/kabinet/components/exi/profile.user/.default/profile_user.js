var profile_user = document.profile_user || {};
profile_user= (function (){
    return {
        start(PHPPARAMS){
						
            const userProfileApplication = BX.Vue3.BitrixVue.createApp({
                data() {
                    return {
                        password:{one:'',two:''}				
                    }
                },
                computed: {				
                    ...BX.Vue3.Pinia.mapState(userStore, ['datauser']),
                    ...BX.Vue3.Pinia.mapState(usekabinetStore, ['config']),
                },
                methods: {
                    // bitrix/templates/kabinet/assets/js/kabinet/vue-componets/extension/addnewmethods.js
					...addNewMethods(),
                    savepassword() {
                        var cur = this;
                        const kabinetStore = usekabinetStore();
                        if (this.password.one != this.password.two) {
                            kabinetStore.Notify = "Введенные пароли не равны";
                        }else{
                            var form_data = this.dataToFormData(this.datauser);
                            this.saveData('bitrix:kabinet.evn.userevents.edit',form_data,function(data){
                                const usrStore = userStore();
                                usrStore.datauser = data.fields;
                            });
                        }
                    },
                    savefields(){
                            var cur = this;
							var form_data = this.dataToFormData(this.datauser);
							this.saveData('bitrix:kabinet.evn.userevents.edit',form_data,function(data){
								const usrStore = userStore();
								usrStore.datauser = data.fields;							
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
                   
                        this.datauser ["PERSONAL_PHOTO"] = event.target.files;
                        this.savefields();
                    }
                },
                // language=Vue
                template: '#kabinet-content'
            });

            configureVueApp(userProfileApplication);
        }
    }
}());