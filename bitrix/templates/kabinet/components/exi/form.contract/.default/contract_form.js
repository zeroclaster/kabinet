const form_contract = {
    data() {
        return {
            id:0,
            action: '',
            counter: 0,
        }
    },
    computed: {
        ...BX.Vue3.Pinia.mapState(AgreementFormStore, ['fields','contractsettings','fields2','banksettings','contracttype']),
    },
    methods: {
        // bitrix/templates/kabinet/assets/js/kabinet/vue-componets/extension/addnewmethods.js
        ...addNewMethods(),
        // Кнопка сохранить в форме
        saveentity:function(){
            var cur = this;

            var form_data = this.dataToFormData(this.fields,null,'HLBLOCK_16_');
            var form_data2 = this.dataToFormData(this.fields2,form_data,'HLBLOCK_17_');

            // поле нужно только для того что бы включать проверку на запоненность или нет
            // cur.contracttype устанавливается селектом из формы
            form_data2.append("contracttype", cur.contracttype.value);

            this.saveData('bitrix:kabinet.evn.contractevents.editcontract',form_data2,function(data){
                // поля Договора
                const AgreementStore = AgreementFormStore();
                AgreementStore.fields = data.fields;
                AgreementStore.fields2 = data.fields2;
            });
        },
        isShowfield(type_view) {
            // Если type_view не передан или пуст, показываем поле (возвращаем true)
            if (!type_view?.length) return true;

            // Проверяем, содержится ли текущий contracttype.value в массиве type_view
            return type_view.includes(this.contracttype.value);
        },
        //bitrix/templates/kabinet/components/bitrix/main.field.string/main.edit/.default.php
        showlimitcount(settings){        
            if (typeof settings == 'undefined') return false;
			if (parseInt(settings.MAX_LENGTH)==0) return false;
            return true;
        },
        limitchars(fieldName,limitsetup){
            const kabinetStore = usekabinetStore();
            if (fieldName.length > limitsetup.MAX_CHARS) {
                kabinetStore.Notify = "Привышено ограничение по количеству символов";
                fieldName = fieldName.slice(0,limitsetup.MAX_CHARS);
            }
            return fieldName.length + '/'+ limitsetup.MAX_CHARS;
        },
    },
    mounted() {
        BX.message(this.message);
        //BX.MFInput.init(this.settinginp);

    },
    // language=Vue
    template: '#kabinet-content'
};


