const photoload = BX.Vue3.BitrixVue.mutableComponent('photo-load', {
    template: `
<div class="vue-component-photoload">
    <div class="uploaded-photo" v-if="original"><img :src="original.SRC" alt=""></div>
    <div class="upload-button">
        <input type="file" @change="onChangeFile" name="file"/>
        <button class="btn btn-primary" type="button">{{buttonTitle}}</button>
    </div>
</div>
`,
    data(){
        return{
        }
    },
    props: ['modelValue','original'],
    computed: {
        buttonTitle(){
            if(!this.original) return "Загрузить";

            return "Заменить";
        }
    },
    mounted () {
        // Add event handler
        const this_ = this;
    },
    methods: {
        onChangeFile(event){
            console.log(event.target.files);
            var cur = this;
            const kabinetStore = usekabinetStore();

            for (let file of event.target.files){
                if ((typeof file.type !== "undefined" ? file.type.match('image.*') : file.name.match('\\.(gif|png|jpe?g)$')) && typeof FileReader !== "undefined") {
                    /*
                    Можно предварительно отобразить, пока такой функционал не требуется
                    */
                    /*
					var reader = new FileReader();
                    reader.onload = function(e) {
                        cur.previmg.push({src:e.target.result,name:file.name});
                    }

                    reader.readAsDataURL(file)
					*/
                }else{
                    kabinetStore.Notify = "Ошибка типа загружаемых файлов";
                    event.target.value = '';
                    return false;
                }
            }

            this.$emit('update:modelValue', event.target.files);
            this.$root.saveentity();
        },
    }
});