const form_brief = {
    data() {
        return {
			PROJECT_ID:PHPPARAMS.PROJECT_ID,
            filterView: 'showRequire', // начальное значение - показываем только обязательные
        }
    },
    computed: {
        ...BX.Vue3.Pinia.mapState(projectFormStore, ['fields','projectsettings']),
        ...BX.Vue3.Pinia.mapState(infoFormStore, ['fields2','infosettings']),
        ...BX.Vue3.Pinia.mapState(detailsFormStore, ['fields3','detailssettings']),
        ...BX.Vue3.Pinia.mapState(targerFormStore, ['fields4','targetsettings']),
        ...BX.Vue3.Pinia.mapState(usekabinetStore, ['Notify','NotifyOk']),
        ...BX.Vue3.Pinia.mapState(orderlistStore, ['data2']),
        UF_TOPICS_LIST: {
            /* liveHack
             нужна что бы можно было обновлять modelValue, и не возникала ошибка modelValue только для чтения
             тут v-model приходит в переменной props: ['modelValue'
            <mytypeahead v-model="runner.UF_LINK" ....
            и ее же помещаем в
            <input v-model="modelValue" ....
            для обновления сохраненного значения, но при изменении идет обращение к modelValue, но она только для чтения
             */
            get() { return this.fields2.UF_TOPICS_LIST_ORIGINAL },
            set(newValue) {
            },
        },
    },
    watch:{
        UF_TOPICS_LIST: {
            handler(val, oldVal) {
                console.log('watch UF_TOPICS_LIST');
                this.$root.select21.val(val).trigger("change");;
            },
            deep: true
        },
    },
    methods: {
        // bitrix/templates/kabinet/assets/js/kabinet/vue-componets/extension/addnewmethods.js
        ...addNewMethods(),
		...BX.Vue3.Pinia.mapActions(orderlistStore, ['getRequireFields']),
        // Новый метод для переключения фильтра
        toggleFilterView() {
            this.filterView = this.filterView === 'showRequire' ? 'showAll' : 'showRequire';
        },
		isRequire(field){
			const RequireFields = this.getRequireFields(this.fields.UF_ORDER_ID);
			if(RequireFields.indexOf(field) == -1) return '';
			

			if (typeof this.fields[field] != 'undefined'){
				if (this.fields[field] == '') 
					return ' markRequire';		
				else 
					return '';
			}
			if (typeof this.fields2[field] != 'undefined'){
				if (this.fields2[field] == '') 
					return ' markRequire';		
				else 
					return '';
			}
			if (typeof this.fields3[field] != 'undefined'){
				if (this.fields3[field] == '') 
					return ' markRequire';		
				else 
					return '';
			}
			if (typeof this.fields4[field] != 'undefined'){
				if (this.fields4[field] == '') 
					return ' markRequire';		
				else 
					return '';
			}			
			
			return ' markRequire';
		},
		isView(field){
			const RequireFields = this.getRequireFields(this.fields.UF_ORDER_ID);
			if(RequireFields.indexOf(field) == -1 && this.filterView == 'showRequire') return ' hide-field';
			
						
			return '';
		},
        isViewGroupTitle(groupFields) {
            // Если показываем все поля, всегда показываем заголовок группы
            if (this.filterView === 'showAll') return '';

            // Для режима "только обязательные" проверяем есть ли обязательные поля в группе
            const RequireFields = this.getRequireFields(this.fields.UF_ORDER_ID);
            let count = 0;
            for (field of groupFields) {
                if (RequireFields.indexOf(field) != -1) count++;
            }

            if (count == 0) return 'hidden-group';

            return '';
        },
        moreitems(field){
            const kabinetStore = usekabinetStore();
            if (field.length > 19){
                kabinetStore.Notify = '';
                kabinetStore.Notify = "Привышен лимит добавления";
                return;
            }
            field.push({ VALUE:'' });
        },
        // Кнопка сохранить в форме
        saveentity:function(){
            var cur = this;
            var form_data = this.dataToFormData(this.fields,null,'HLBLOCK_4_');
            var form_data2 = this.dataToFormData(this.fields2,form_data,'HLBLOCK_8_');
            var form_data3 = this.dataToFormData(this.fields3,form_data2,'HLBLOCK_9_');
            var form_data4 = this.dataToFormData(this.fields4,form_data3,'HLBLOCK_12_');


            this.saveData('bitrix:kabinet.evn.briefevents.edit',form_data4,function(data){

                const projectStore = projectFormStore();
                projectStore.fields = data.fields;

                const infoStore = infoFormStore();
                infoStore.fields2 = data.fields2;

                const detailsStore = detailsFormStore();
                detailsStore.fields3 = data.fields3;

                const targerStore = targerFormStore();
                targerStore.fields4 = data.fields4;

                if (cur.PROJECT_ID=='') setTimeout(()=>window.location.href = '/kabinet/projects/planning/?p='+projectStore.fields.ID,1000);
            });
        },
        isShowfield: function (type_view){
            var ret = false;
            if (!type_view) return true;
            for(const val of type_view){
                ret = true;
            }
            return ret;
        },
        showlimitcount(settings){
            if (typeof settings == 'undefined') return false;
            if (parseInt(settings.MAX_LENGTH)==0) return false;
            return true;
        },
        limitchars(fieldName,settings){
            const kabinetStore = usekabinetStore();
            if (fieldName.length > settings.MAX_LENGTH) {
                kabinetStore.Notify = "Привышено ограничение по количеству символов";
                //fieldName = fieldName.slice(0,limitsetup.MAX_CHARS);
            }
            return fieldName.length + '/'+ settings.MAX_LENGTH;
        },
        sizeFemale(){

		    let size = 22;
            let genders = this.fields4.UF_RATIO_GENDERS;
            size -= (parseInt(genders) / 2);

            return 'font-size:'+size+'px';
        },
        sizeMan(){

            let size = 22;
            let genders = this.fields4.UF_RATIO_GENDERS;
            size += (parseInt(genders) / 2);

            return 'font-size:'+size+'px';
        },
        split_percent(){
            let percent = 10;
            let genders = parseInt(this.fields4.UF_RATIO_GENDERS);
            percent += genders;
            percent = (percent*100)/20;
            return 'background: linear-gradient(90deg, #0c63e4 '+percent+'%, #e17f50 '+percent+'%);';
        },
        percentFemale(){
            let percent = 10;
            let genders = 0;
            if (this.fields4.UF_RATIO_GENDERS !='') genders = parseInt(this.fields4.UF_RATIO_GENDERS);
            percent += genders;
            percent = (percent*100)/20;
            percent = 100 - percent;
            return percent+'%';
        },
        percentMan(){
            let percent = 10;
            let genders = 0;
            if (this.fields4.UF_RATIO_GENDERS !='') genders = parseInt(this.fields4.UF_RATIO_GENDERS);
            percent += genders;
            percent = (percent*100)/20;
            return percent+'%';
        }
    },
    components: {
        richtext,
        customoption,
        photoload,
    },
    mounted() {
        const this_ = this;
        BX.message(this.message);
        //BX.MFInput.init(this.settinginp);

        let node = document.querySelector( '.select2' );
        this.$root.select21 = $( node );
        $( node ).select2({
            placeholder:$( node ).attr( 'data-placeholder' ) || null
        }).on('change', function (e) {
            let collection = this.selectedOptions;
            let newVals = [];
            for(index in this.selectedOptions){
                if (typeof this.selectedOptions[index].value != "undefined"){
                    newVals.push(this.selectedOptions[index].value);
                }
            }
            this_.fields2.UF_TOPICS_LIST = newVals;
        });
        //this.$root.select21.val(['2']).trigger("change");;

    },
    // language=Vue
    template: '#kabinet-content'
};
