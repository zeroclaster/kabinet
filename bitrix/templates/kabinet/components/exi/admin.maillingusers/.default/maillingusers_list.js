const telegram_users = {
    data() {
        return {
        }
    },
    computed: {
        viewedcount(){
            return this.telegramusers.length;
        }
    },
    methods: {
        // bitrix/templates/kabinet/assets/js/kabinet/vue-componets/extension/addnewmethods.js
        ...addNewMethods(),
        moreload:function (e) {
            const this_ = this;
            let formData = new FormData;
            this.$root.offset = this.$root.offset + 25;
            formData.append("OFFSET",this.$root.offset);
            for (fieldname in filtertelegramuserlist) formData.append(fieldname,filtertelegramuserlist[fieldname]);

            formData.append("countview",this_.countview);
            const kabinetStore = usekabinetStore();
            kabinet.loading();
            var data = BX.ajax.runComponentAction("exi:admin.maillingusers", "loadmore", {
                mode: 'class',
                data: formData,
                timeout: 300
            }).then(function (response) {
                kabinet.loading(false);
                const data = response.data;

                if (typeof data.DATA != "undefined" && data.DATA.length == 0) this_.showloadmore = false;
                if (this_.telegramusers.length == this_.total) this_.showloadmore = false;

                // исполнения
                if (typeof data.DATA != "undefined"){ data.DATA.forEach((elm)=>{this_.telegramusers.push(elm)});
                }
            }, function (response) {
                kabinet.loading(false);
                if (response.errors[0].code != 0) {
                    kabinetStore.Notify = '';
                    kabinetStore.Notify = response.errors[0].message;
                }else {
                    kabinetStore.Notify = '';
                    kabinetStore.Notify = "Возникла системная ошибка! Пожалуйста обратитесь к администратору сайта.";
                }
            });

            e.preventDefault();
            return false;
        },
        showall: function (task) {
            task.LIMIT = 1000;
        },
        saveData(id){
            const this_ = this;

            const kabinetStore = usekabinetStore();
            kabinet.loading();

            var userData = this.telegramusers.find(user => user.ID == id);
            let formData = new FormData;
            const form_data = this.dataToFormData(userData,formData);

            console.log(form_data);


            var data = BX.ajax.runComponentAction("exi:admin.maillingusers", "saveuser", {
                mode: 'class',
                data: formData,
                timeout: 300
            }).then(function (response) {
                kabinet.loading(false);
                const data = response.data;
                kabinetStore.NotifyOk = '';
                kabinetStore.NotifyOk = data.message;

                userData = data.fields;


            }, function (response) {
                kabinet.loading(false);
                if (response.errors[0].code != 0) {
                    kabinetStore.Notify = '';
                    kabinetStore.Notify = response.errors[0].message;
                }else {
                    kabinetStore.Notify = '';
                    kabinetStore.Notify = "Возникла системная ошибка! Пожалуйста обратитесь к администратору сайта.";
                }
            });
        },
        disableEmailSender(id){
            var userData = this.telegramusers.find(user => user.ID == id);
            userData.UF_EMAIL_NOTIFI = 0;
            this.saveData(id);
        },
        showFieldEnum(id,fieldValue){

            if (!fieldValue) return '';

            var userData = this.telegramusers.find(user => user.ID == id);
            datalist = userData.USER_FIELD_ID_ORIGINAL.find(fieldParams => fieldParams.ID == fieldValue);

            return datalist.VALUE;
        }
    },
    mounted() {
        this.$root.offset = 0;
        if(parseInt(this.total) <= parseInt(this.countview)) this.showloadmore = false;
    },
    template: '#kabinet-content'
};