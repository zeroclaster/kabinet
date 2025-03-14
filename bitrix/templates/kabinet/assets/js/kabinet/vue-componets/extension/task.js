/*
for use
methods: {
...taskMethods()
}
 */

var taskMethods = function(){
    return {
        closemodal:function(){
            this.$root.myModal.hide();
        },
        closemodal2:function(){
            this.$root.myModal2.hide();
        },
        addbuttorder: function (project) {

            this.modaldata.project = project.ID;

            if (project.UF_ORDER_ID) {
                this.modaldata.order = project.UF_ORDER_ID;
            }

            //this.modaldata.title = item.ID;
            this.$root.myModal = new bootstrap.Modal(document.getElementById('exampleModal'), {});
            this.$root.myModal.show();
        },
        removeProductModal:function (product){

            this.modal2data.basketitem = product.BASKET_ID;
            this.modal2data.order_id = product.ORDER_ID;
            this.modal2data.message = '';

            //this.modaldata.title = item.ID;
            this.$root.myModal2 = new bootstrap.Modal(document.getElementById('exampleModal2'), {});
            this.$root.myModal2.show();
        },
        increment:function (product){
            if (product.MAXIMUM_QUANTITY_MONTH>0 && parseInt(product.COUNT) > product.MAXIMUM_QUANTITY_MONTH) {
                const kabinetStore = usekabinetStore();
                kabinetStore.Notify = '';
                kabinetStore.Notify = this.message.error1;
                return ;
            }
            product.COUNT = parseInt(product.COUNT) + 1;
        },
        decrease:function (product){
            if (product.MINIMUM_QUANTITY_MONTH>0 && parseInt(product.COUNT) <= product.MINIMUM_QUANTITY_MONTH) {
                const kabinetStore = usekabinetStore();
                kabinetStore.Notify = '';
                kabinetStore.Notify = this.message.error3;
                return ;
            }

            if (parseInt(product.COUNT) == 0) return ;
            product.COUNT = parseInt(product.COUNT) - 1;
        },
        chooseadd: function(product){
            if (parseInt(product.COUNT) == 0) {
                const kabinetStore = usekabinetStore();
                kabinetStore.Notify = '';
                kabinetStore.Notify = this.message.error2;
                return ;
            }

            this.addproduct(product.ID, product.COUNT,this.modaldata.order,this.modaldata.project);
        },
        removeproduct: function (BASKET_ID,ORDER_ID){
            this.removeproductorder(BASKET_ID, ORDER_ID,this.modal2data);
        },
        addproduct(ID,COUNT,ORDER_ID,PROJECT_ID){
            var cur = this;

            var form_data = this.dataToFormData({
                'id': ID,
                'count': COUNT,
                'order_id': ORDER_ID,
                'project_id': PROJECT_ID,
            });

            this.saveData('bitrix:kabinet.evn.briefevents.addproduct',form_data,function(data){

                let taskID = data.id;

                for (index in data.datatask){
                    cur.datataskCopy[index] = data.datatask[index];
                }

                cur.makeData(cur.datataskCopy);

                const briefStore = brieflistStore();
                const orderStore = orderlistStore();
                const taskStore = tasklistStore();
                briefStore.data = data.data;
                orderStore.data2 = data.data2;
                taskStore.datatask = data.datatask;

                // закрываем окно добавления новой задачи
                cur.closemodal();

                if (typeof cur.viewTask != "undefined")
                    setTimeout(function () {
                        cur.viewTask(taskID);
                    },1500);
                else
                    //window.open('https://kupi-otziv.ru/kabinet/projects/planning/?p='+PROJECT_ID+'#produkt'+ID, '_blank');
                    window.document.location.href = 'https://kupi-otziv.ru/kabinet/projects/planning/?p='+PROJECT_ID+'#produkt'+taskID;

            });
        },
        removeproductorder(ID,ORDER_ID,modal){
            var cur = this;

            var form_data = this.dataToFormData({
                'id': ID,
                'order_id': ORDER_ID,
            });

            this.saveData('bitrix:kabinet.evn.briefevents.addproduct',form_data,function(data){
                modal.message = data.message;

                const briefStore = brieflistStore();
                const orderStore = orderlistStore();
                const taskStore = tasklistStore();
                briefStore.data = data.data;
                orderStore.data2 = data.data2;
                taskStore.datatask = data.datatask;
            });
        },
    };
}