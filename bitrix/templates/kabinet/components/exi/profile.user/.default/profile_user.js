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
                    UnlinkTelegram(){
                        this.datauser.UF_TELEGRAM_ID = 0;
                        this.savefields();
                        this.loadTelegramWidget();
                    },
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
                    },
                    loadTelegramWidget() {
                        const script = document.createElement('script');
                        script.async = true;
                        script.src = "https://telegram.org/js/telegram-widget.js?22";
                        script.setAttribute('data-telegram-login', 'kupiotziv_bot');
                        script.setAttribute('data-size', 'large');
                        script.setAttribute('data-auth-url', '/auth/telegram.php');
                        script.setAttribute('data-request-access', 'write');
                        script.setAttribute('data-userpic', 'false');

                        const container = document.getElementById('telegram-login-btn');
                        if (container) {
                            container.appendChild(script);
                        }
                    }
                },
                mounted() {
                    this.loadTelegramWidget();
                },
                template: '#kabinet-content'
            });

            configureVueApp(userProfileApplication);
        }
    }
}());