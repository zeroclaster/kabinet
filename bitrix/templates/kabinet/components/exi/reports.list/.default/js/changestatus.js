const changestatus = BX.Vue3.BitrixVue.mutableComponent('change status', {
    template: `
                          <div class="mt-3 statusworkblock" v-if="catalog.length>0">
                    <div class="h4">Действия:</div>
                    <div class="form-group select-status" v-for="Status in catalog">
                        <div class="form-check">
                          <input @change="saveStatus" :name="$id('name')" class="form-check-input" :id="$id(Status.ID)" v-model="localModelValue" type="radio" :value="Status.ID">
                          <label style="color: #FFF !important;" class="form-check-label text-primary btn btn-primary" :for="$id(Status.ID)" v-html="Status.USER_BUTTON"></label>
                        </div>
                    </div>
                </div>
                `,
    data(){
        return{
            id_input:'inpid'+kabinet.uniqueId()
        }
    },
    props: ['modelValue','tindex','catalog'],
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
    mounted () {
        // Add event handler
        const this_ = this;

        this.$.iid = function() {
            return "uid-" + kabinet.uniqueId();
        };
    },
    methods: {
        makeUniqeId(){
            return 'inpid'+kabinet.uniqueId();
        },
        saveStatus(){
            if (typeof this.$.inpSaveTimer != 'undefined') clearTimeout(this.$.inpSaveTimer);
            this.$.inpSaveTimer = setTimeout(()=>{this.$root.savetask(this.tindex);},1000);
        }
    }
});