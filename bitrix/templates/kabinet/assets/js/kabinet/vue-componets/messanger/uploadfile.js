const messUploadFileComponent = BX.Vue3.BitrixVue.mutableComponent('messUploadFile-Component', {
    data(){
        return{
        }
    },
    props: ['modelValue'],
	computed: {
        /*
        usekabinetStore задает в bitrix/templates/kabinet/footer.php
         */
	...BX.Vue3.Pinia.mapState(usekabinetStore, ['config']),
	},
    methods: {
        onChangeFile(event) {
            console.log(event.target.files);
            var cur = this;
            const kabinetStore = usekabinetStore();
            let uploadSize = 0;

            // Проверяем файлы по допустимым для отправки расширениям
            const regex = new RegExp('\\.('+this.config.CHART.ALLOWED_EXTENSIONS+')$', 'i')
            for (let file of event.target.files) {
                uploadSize += file.size;
                if (!file.name.match(regex)) {
                    kabinetStore.Notify = "Error file type";
                    event.target.value = '';
                    return false;
                }
            }

            // ограничиваем отправку по размеру
            if (uploadSize > this.config.CHART.UPLOAD_SIZE_LIMIT) {
                kabinetStore.Notify = '';
                kabinetStore.Notify = "Превышен максимально допустисы ("+this.config.CHART.UPLOAD_SIZE_LIMIT+" байт) размер загрузки файлов.";
                return;
            }

            // не отправляем нулевые файлы
            if(uploadSize > 0) this.$emit('update:modelValue', event.target.files);
        }
    },
    template:`
<input type="file" @change="onChangeFile" multiple>
<div class="butt-upload text-primary d-flex align-items-center"><i class="fa fa-paperclip" aria-hidden="true"></i></div>
`
});