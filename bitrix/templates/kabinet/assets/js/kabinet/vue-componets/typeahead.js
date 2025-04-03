const mytypeahead = BX.Vue3.BitrixVue.mutableComponent('type-ahead', {
    template: `<div class="row form-group mb-3">
                <div class="col-sm-1 text-sm-right">
                    <label class="col-form-label" :for="$id('id_input')">Ссылка:</label>
                </div>
                <div class="col-sm-11" v-if="isEdit()">
                    <input ref="input" :id="$id('id_input')" class="form-control" type="text" @change="sendvalinput" v-model="localModelValue" placeholder="начните вводить или выберите из списка">
                </div>
                 <div class="col-sm-11 col-form-label" v-else>                 
                    {{localModelValue}}
                </div>               
</div>
`,	data(){
        return{
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

        let node = this.$refs.input;

        window.addEventListener("components:ready", function(event) {});
            let inp = $(node);
            inp.typeahead(
                {
                    hint: true,
                    highlight: true,
                    minLength: 0
                },
                {
                    limit: 1000000,
                    name: inp.attr('placeholder'),
                    displayKey: 'VALUE',
                    source: function findMatches(q, cb, async) {
                        let matches = [];
                        this_.catalog.forEach(function (element) {
                            if ((new RegExp(q, 'i')).test(element.VALUE)) matches.push(element);
                        });
                        cb(matches);
                    }
                }
            );

            inp.bind('typeahead:select', function (ev, suggestion) {
                this_.updateValue(suggestion.VALUE);
            });

    },
    methods: {
        updateValue (value) {
            this.$emit('update:modelValue', value);
            this.$root.savetask(this.tindex);
        },
        sendvalinput(event){
            const inp = event.target;
            console.log(inp)
            this.$root.savetask(this.tindex);
        },
        isEdit(){
            if (typeof this.$root.isUserrEdit == "undefined") return true;
            return this.$root.isUserrEdit(this.tindex);
        }
    }

});
