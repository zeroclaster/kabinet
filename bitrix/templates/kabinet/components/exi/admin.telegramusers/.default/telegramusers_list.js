const telegram_users = {
    data() {
        return {
        }
    },
    computed: {
        showloadmore() {
            // Если telegramusers пуст или достигнут total — скрываем кнопку
            return !(
                (this.telegramusers && this.telegramusers.length === 0) ||
                this.telegramusers?.length >= this.total
            );
        },
    },
    methods: {
        // bitrix/templates/kabinet/assets/js/kabinet/vue-componets/extension/addnewmethods.js
        ...addNewMethods(),
        async moreload(e) {
            e.preventDefault();
            await loadMoreDataExtended({
                componentName: "exi:admin.telegramusers",
                context: this,
                stores: { telegramusers: "DATA" },
                filter: filtertelegramuserlist,
            });
        },
        showall: function (task) {
            task.LIMIT = 1000;
        },
        saveData(id){
            const kabinetStore = usekabinetStore();
            kabinet.loading();

            var userData = this.telegramusers.find(user => user.ID == id);
            let formData = new FormData;
            const form_data = this.dataToFormData(userData,formData);

            var data = BX.ajax.runComponentAction("exi:admin.telegramusers", "saveuser", {
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
        disableTELEGRAM(id){
            var userData = this.telegramusers.find(user => user.ID == id);
            userData.UF_TELEGRAM_ID = 0;
            this.saveData(id);
        },
        connectTELEGRAM(id){
            var userData = this.telegramusers.find(user => user.ID == id);
            userData.UF_TELEGRAM_ID = userData.UF_TELEGRAM_CHAT_ID;
            this.saveData(id);
        }
    },
    template: '#kabinet-content'
};