const commentWrite = BX.Vue3.BitrixVue.mutableComponent('comment Write', {
    template: `
<div class="modal fade" :id="ModalID" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
<div class="modal-dialog modal-lg">
    <div class="modal-content">
        <div class="modal-header">
            <h3 class="modal-title fs-5" id="exampleModalLabel">Введите комментарий</h3>
        </div>
        <div class="modal-body messanger-block">
                        <div class="sender-block">                
                        <div class="d-flex">
							<!--
                            <div class="upload-file-block">
                                    <messUploadFileComponent v-model="sendFiles"/>
                            </div>
							-->
                            <div class="message-text-block">
                                 <div class="upload-file-list d-flex flex-wrap" v-if="sendFiles.length>0">
                                        <div class="mr-2 p-2" v-for="(upl_file,fileIndex) of sendFiles">{{upl_file.name}} <div class="remove-upload-file text-primary" @click="removeUplFile(fileIndex)"><i class="fa fa-times" aria-hidden="true"></i></div></div>
                                </div>
                                    <textarea ref="textares" class="form-control" :name="$id('comment')" rows="10" v-model="localModelValue" placeholder="Напишите причину по которой вы отклоняете"></textarea>
                            </div>
                            <div class="sender-block ml-auto d-flex align-items-center">
                                    <button type="button" class="btn btn-primary btn-sm send-message-button" @click="save">Отправить</button>
                            </div>
                        </div>
                        </div>
        </div>
        <div class="modal-footer">
            
            <button type="button" class="btn btn-secondary" @click="closemodal">Закрыть</button>
        </div>
    </div>
</div>
</div>
                `,
    data(){
        return{
            ModalID:'modale'+kabinet.uniqueId(),
        }
    },
    props: ['modelValue','tindex'],
    computed: {
        sendFiles:{
            get() {
                if (typeof this.$root.datarunner[this.tindex].messagePics != "undefined") return this.$root.datarunner[this.tindex].messagePics;
                return [] },
            set(newValue) {
                this.$root.datarunner[this.tindex].messagePics = newValue;
            },
        },
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
    mounted () {
        // Add event handler
        const this_ = this;
    },
    methods: {
        removeUplFile(fileIndex){
            const dt = new DataTransfer();
            let arr_files = [...this.sendFiles];
            arr_files.forEach((f, i)=>{
                if (i != fileIndex)
                    dt.items.add(f);
            });

            this.sendFiles = dt.files;
        },
        save:function () {
            this.$.notrest = false;
            this.closemodal();
            this.$root.savetask(this.tindex);
            this.$.notrest = true;
        },
        closemodal:function(){
            this.$.myModal.hide();
            this.$.ckEditor.destroy().catch( error => {
                console.log( error );
            } );
        },
        showmodale(){
            const this_ = this;
            if (typeof this.$.myModal == 'undefined') {
                this.$.myModal = new bootstrap.Modal(document.getElementById(this.ModalID), {});
                this.$.notrest = true;
                $('#'+this.ModalID).on('hidden.bs.modal', function (e) {
                    if(this_.$.notrest)
                        this_.$root.resetSave(this_.tindex)
                })
            }
            this.$.myModal.show();


            let node = this.$refs.textares;

            CKEDITOR.ClassicEditor.create( node, this.ckSetup() ).then(  ( editor )=> {
                this.$.ckEditor = editor;
                editor.model.document.on('change:data', (evt, data) => {
                    //console.log(editor.getData());
                    this.$emit('update:modelValue', editor.getData());
                });
            });
        },
        ckSetup(){
            return {
                //toolbar: [ "heading", 'bold', 'italic', 'link',"imageUpload"],
                toolbar: [  ],
                minHeight: '600px',
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
    },
    components: {
        messUploadFileComponent,
    }
});