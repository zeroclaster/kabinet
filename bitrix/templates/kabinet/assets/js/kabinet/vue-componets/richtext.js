const richtext = BX.Vue3.BitrixVue.mutableComponent('rich-text', {
    template: `
<textarea ref="textares" cols="20" rows="10" :id="$id('richtext')" class="fields string form-control" v-model="localModelValue"></textarea>
<div class="text-right" v-if="showsavebutton"><button class="btn btn-primary" type="button" @click="gotosave">сохранить</button></div>
`,	data(){
        return{
            notSave: false,
        }
    },
    props: ['modelValue','original','autosave','tindex','showsavebutton'],
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
            get() {
                return this.modelValue },
            set(newValue) {
                this.$emit('update:modelValue', newValue)
            },
        },
    },
    watch:{
        original: {
            handler(val, oldVal) {
                this.notSave = true;
                this.$.ckEditor.setData(val);
            },
            deep: true
        },
    },
    mounted () {
        // Add event handler
        const this_ = this;

        let node = this.$refs.textares;

        CKEDITOR.ClassicEditor.create( node, this.ckSetup() ).then(  ( editor )=> {
            this.$.ckEditor = editor;
            editor.model.document.on('change:data', (evt, data) => {
                //console.log(editor.getData());
                this.$emit('update:modelValue', editor.getData());
                console.log('11')
                if (typeof this.autosave != "undefined" && !this.notSave) this.$root.inpsave(this.tindex);
                else this.notSave = false;
            });
            // TODO AKULA сделать отправку по нажатию enter
            /*
            editor.on('key', function(event) {
                var enterKeyPressed = event.data.keyCode === 13;
                console.log(event.data.keyCode)
            });
             */
        });

    },
    methods: {
        gotosave(){
            this.$root.inpsave(this.tindex);
        },
        clear(){
            this.$.ckEditor.setData('');
        },
        focusEditor(){
            this.$.ckEditor.model.change( writer => {
                writer.setSelection( writer.createPositionAt( this.$.ckEditor.model.document.getRoot(), 'end' ) );
                this.$.ckEditor.editing.view.focus();
            } );

        },
        ckSetup(){
            return {
                //toolbar: [ "heading", 'bold', 'italic', 'link',"imageUpload"],
                toolbar: [  ],
                minHeight: '600px',
                placeholder: 'Написать сообщение...',
                heading: {
                    options: [
                        { model: 'paragraph', title: 'Заголовок', class: 'ck-heading_paragraph' },
                        { model: 'heading1', view: 'h2', title: 'Заголовок2', class: 'ck-heading_heading2' },
                        { model: 'heading2', view: 'h3', title: 'Заголовок3', class: 'ck-heading_heading3' },
                        { model: 'heading3', view: 'h4', title: 'Заголовок4', class: 'ck-heading_heading4' }

                    ]
                },
                ckfinder: {
                    // eslint-disable-next-line max-len
                    uploadUrl: '/tools/connector.php?command=QuickUpload&type=Files&responseType=json'
                },
                link: {
                    addTargetToExternalLinks: true
                }
            }
        }
    }

});