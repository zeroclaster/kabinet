const mytypeahead = BX.Vue3.BitrixVue.mutableComponent('type-ahead', {
    template: `<div class="row form-group mb-3 link-list-input-block">
                <div class="col-sm-1 text-sm-right">
                    <label class="col-form-label" :for="$id('id_input')">Ссылка:</label>
                </div>
                <div class="col-sm-11" v-if="isEdit()">
                    <div class="input-group">
                        <input ref="input" :id="$id('id_input')" class="form-control" type="text" 
                               :class="{ 'is-invalid': hasInvalidValue }"
                               @change="sendvalinput" @focus="showSuggestions" @click="showSuggestions"
                               v-model="localModelValue" placeholder="начните вводить или выберите из списка">
                        <button class="btn btn-outline-secondary cvadrat-button button-click-clibort2" type="button" @click="copyToClipboard" title="Копировать ссылку">
                           <i class="fa fa-files-o" aria-hidden="true"></i>
                        </button>
                    </div>
                    <div class="invalid-feedback" v-if="hasInvalidValue">
                        Введенное значение отсутствует в списке доступных вариантов
                    </div>
                </div>
                 <div class="col-sm-11 col-form-label link-block" v-else>
                    <div class="d-flex align-items-center">
                        <span class="me-2">{{localModelValue}}</span>
                        <button class="btn btn-sm btn-outline-secondary cvadrat-button button-click-clibort" style="margin-left: 20px;" type="button" @click="copyToClipboard" title="Копировать ссылку">
                           <i class="fa fa-files-o" aria-hidden="true"></i> 
                        </button>
                    </div>
                </div>               
</div>`,
    data(){
        return{
            typeaheadInstance: null
        }
    },
    props: ['modelValue','tindex','catalog'],
    computed: {
        localModelValue: {
            get() { return this.modelValue },
            set(newValue) {
                this.$emit('update:modelValue', newValue)
            },
        },
        // Проверяем, есть ли введенное значение в списке доступных вариантов
        hasInvalidValue() {
            if (!this.localModelValue) return false;

            // Ищем введенное значение в каталоге
            const found = this.catalog.some(item =>
                item.VALUE === this.localModelValue
            );

            return !found;
        }
    },
    mounted () {
        // Add event handler
        const this_ = this;

        let node = this.$refs.input;

        window.addEventListener("components:ready", function(event) {});
        let inp = $(node);

        // Сохраняем экземпляр typeahead для дальнейшего использования
        this.typeaheadInstance = inp.typeahead(
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
                    // ВСЕГДА возвращаем все элементы, независимо от запроса
                    let matches = [];
                    this_.catalog.forEach(function (element) {
                        matches.push(element);
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
        // Метод для показа предложений при фокусе/клике
        showSuggestions() {
            if (this.typeaheadInstance) {
                // Открываем выпадающий список typeahead
                this.typeaheadInstance.typeahead('open');

                // Если поле пустое, показываем все варианты
                if (!this.localModelValue) {
                    this.typeaheadInstance.typeahead('val', '');
                }
            }
        },
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
        },
        copyToClipboard() {
            if (!this.localModelValue) return;

            navigator.clipboard.writeText(this.localModelValue)
                .then(() => {
                    // Можно добавить уведомление об успешном копировании
                    console.log('Ссылка скопирована в буфер обмена');
                })
                .catch(err => {
                    console.error('Ошибка при копировании: ', err);
                    // Fallback для старых браузеров
                    this.fallbackCopyToClipboard(this.localModelValue);
                });
        },
        fallbackCopyToClipboard(text) {
            const textArea = document.createElement("textarea");
            textArea.value = text;
            document.body.appendChild(textArea);
            textArea.focus();
            textArea.select();
            try {
                document.execCommand('copy');
                console.log('Ссылка скопирована в буфер обмена (fallback)');
            } catch (err) {
                console.error('Ошибка при копировании (fallback): ', err);
            }
            document.body.removeChild(textArea);
        }
    }
});
