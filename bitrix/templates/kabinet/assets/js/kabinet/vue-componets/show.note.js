var showmessage_vuecomponent = document.showmessage_vuecomponent || {};
showmessage_vuecomponent = (function (){
    return {
        start(PHPPARAMS){
            return BX.Vue3.BitrixVue.mutableComponent('show_message', {
                template: `    <!-- Modal -->
    <div class="modal fade" :id="ModalID" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">               
                <div class="modal-body">                 
                   <div class="alert alert-success" role="alert">{{note}}</div>
                </div>
                <div class="modal-footer">          
                    <button type="button" class="btn btn-secondary" @click="closemodal">Закрыть</button>
                </div>
            </div>
        </div>
    </div>`,
                data(){
                    return{
                        note : PHPPARAMS.note,
                        ModalID:'modale'+kabinet.uniqueId(),
                    }
                },
                props: [],
                computed: {
                },
                watch:{
                },
                mounted () {
                    // Add event handler
                    const this_ = this;

                    if (this.note) this.showmodale();
                },
                methods: {
                    closemodal:function(){
                        this.$.myModal.hide();
                    },
                    showmodale(){
                        this.selectedPhohto = [];

                        if (typeof this.$.myModal == 'undefined')
                            this.$.myModal = new bootstrap.Modal(document.getElementById(this.ModalID), {});
                        this.$.myModal.show();
                    },
                },
                components: {
                }
            });
        }
    }
}());