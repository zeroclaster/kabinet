const sharephoto = BX.Vue3.BitrixVue.mutableComponent('share-photo', {
    template: '#sharephoto-template',
    data(){
        return{
            id_input:'inpid'+kabinet.uniqueId(),
            ModalID:'modale'+kabinet.uniqueId(),
            selectedPhohto:[],
        }
    },
    props: ['modelValue','tindex','catalog'],
    computed: {
        notload(){
            if (this.selectedPhohto.length > 0 ) return false;
            return true;
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
            this.$root.savetask(this.tindex);
            this.$.myModal.hide();
        },
        closemodal:function(){
            this.$.myModal.hide();
        },
        showmodale(){
            this.selectedPhohto = [];

            if (typeof this.$.myModal == 'undefined')
                        this.$.myModal = new bootstrap.Modal(document.getElementById(this.ModalID), {});
            this.$.myModal.show();
        },
        selphoto(ID){
            let index = this.selectedPhohto.indexOf(ID);
            if(index != -1){
                this.selectedPhohto.splice(index,1);
            }else{
                this.selectedPhohto.push(ID);
            }
        },
        isSelectedPhohto(ID){
            if(this.selectedPhohto.indexOf(ID) != -1) return true;
            return false;
        },
        addphoto(){
            if (this.selectedPhohto.length > 0) {
                this.$root.addSelectedPhoto(this.tindex,this.selectedPhohto);
                this.$root.savetask(this.tindex);
                this.$.myModal.hide();
            }
        }
    }
});