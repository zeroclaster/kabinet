var questionactivity_vuecomponent = document.questionactivity_vuecomponent || {};
questionactivity_vuecomponent = (function (){
    return {
        start(PHPPARAMS){
            return BX.Vue3.BitrixVue.mutableComponent('question_activity', {
                template: `    <!-- Modal -->
    <div class="modal fade" :id="ModalID" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">               
                <div class="modal-body">   						
                   <div class="alert alert-danger" role="alert" v-if="alert_mode==''">{{question}}</div>
				   <div class="alert alert-danger" role="alert" v-if="alert_mode!=''">{{alert_mode}}</div>
                </div>
                <div class="modal-footer">  
                    <button type="button" class="btn btn-primary" v-if="alert_mode==''" @click="ok">Ок</button>   
                    <button type="button" class="btn btn-secondary" @click="closemodal">Отмена</button>
                </div>
            </div>
        </div>
    </div>`,
                data(){
                    return{
                        ModalID:'modale'+kabinet.uniqueId(),
						alert_mode: '',
                    }
                },
                props: ['question'],
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
					addAlert(message){
						this.alert_mode = message;
					},
                    ok(){
                        this.closemodal();
                        this.$.callback.call(this.$root,this.$.taskindex);
                    },
                    closemodal:function(){
                        this.$.myModal.hide();
						this.alert_mode = '';
                    },
                    showmodale(taskindex,callback){
                        this.selectedPhohto = [];
                        this.$.taskindex = taskindex;
                        this.$.callback = callback;
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