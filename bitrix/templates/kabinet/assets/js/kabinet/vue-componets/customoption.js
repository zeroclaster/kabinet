const customoption = BX.Vue3.BitrixVue.mutableComponent('custom-option', {
    template: `
    <span class="fields enumeration enumeration-checkbox field-item">
    <span class="fields separator"></span>
    <label><input type="checkbox" v-model="isVisibly" value="1"> Ваш вариант</label><br>
    </span>
    <div v-if="isVisibly">
    <div class="mb-2" v-for="inplist in localModelValue">
        <input size="20" :id="$id('UF_ORDER_PROCESS_USER')" class="fields string form-control" tabindex="0" type="text" v-model="inplist.VALUE">
    </div>
    <div class="mt-3" style="position: relative;">
                                        <button class="btn btn-primary btn-sm" type="button" @click="addmoreinput">+</button>
                                    </div>
    </div>
`,	data(){
        return{
            isVisibly:false,
        }
    },
    props: ['modelValue'],
    computed: {
        localModelValue: {
            /* liveHack
             нужна что бы можно было обновлять modelValue, и не возникала ошибка modelValue только для чтения
             тут v-model приходит в переменной props: ['modelValue'
            <mytypeahead v-model="runner.UF_LINK" ....
            и ее же помещаем в
            <input v-model="modelValue" ....
            для обновления сохраненного значения, но при изменении идет обращение к modelValue, но она только для чтения
             */
            get() { return this.modelValue },
            set(newValue) {
                this.$emit('update:modelValue', newValue)
            },
        },
    },
    watch:{
        /*
        original: {
            handler(val, oldVal) {
                console.log('111')
                this.$.ckEditor.setData(val);
            },
            deep: true
        },
         */
    },
    mounted () {
        // Add event handler
        if (this.modelValue && this.modelValue.length == 1 && this.modelValue[0].VALUE == ''){
            this.isVisibly = false;
        }else
            this.isVisibly = true;

    },
    methods: {
        addmoreinput() {
            const kabinetStore = usekabinetStore();
            if (this.localModelValue.length > 4){
                kabinetStore.Notify = '';
                kabinetStore.Notify = "Привышен лимит добавления";
                return;
            }
            this.localModelValue.push({ VALUE:'' });
        },
    }

});