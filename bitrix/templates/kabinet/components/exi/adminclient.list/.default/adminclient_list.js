const adminclient_list = {
    data() {
        return {
        }
    },
    computed: {
        showloadmore() {
            // Если telegramusers пуст или достигнут total — скрываем кнопку
            return !(
                (this.dataclient && this.dataclient.length === 0) ||
                this.dataclient?.length >= this.total
            );
        },
    },
    methods: {
        ...helperVueComponents(),
        async moreload(e) {
            e.preventDefault();
            await loadMoreDataExtended({
                componentName: "exi:adminclient.list",
                context: this,
                stores: {
                    dataclient: "CLIENT_DATA",
                    dataproject: "PROJECT_DATA",
                    datatask: "TASK_DATA",
                    dataorder: "ORDER_DATA",
                    datarunner: "RUNNER_DATA",
                },
                filter: filterclientlist,
            });
        },
        gotocearchstatus(event){
            event.target.form.submit();
        },
        getClientExecution2(id_client,taskID){
            if (typeof this.datarunner[id_client][taskID] != 'undefined') return this.datarunner[id_client][taskID];
            return [];
        },
        getClientExecution(clientId) {
            if (!this.datatask[clientId] || !Array.isArray(this.datatask[clientId])) {
                return [];
            }

            return this.datatask[clientId]
                .flatMap(task =>
                    this.datarunner[clientId]?.[task.ID]
                        ? [...this.datarunner[clientId][task.ID]]
                        : []
                );
        },
        getExecutionStatusCount(id_client,id_status){
            let count = 0;
            let data = this.getClientExecution(id_client);

            for (index in data){
                if (data[index].UF_STATUS == id_status) count = count + 1;
            }

            return count;
        },
        getExecutionStatusCount2(id_client,taskID,id_status){

            let count = 0;
            let data = this.getClientExecution2(id_client,taskID);

            for (index in data){
                if (data[index].UF_STATUS == id_status) count = count + 1;
            }

            return count;
        },
        badTask(task_id, client_id, order_id, product_id) {
            if (!this.dataorder[client_id]?.[order_id]?.[product_id]) {
                console.warn(`Ошибка в задаче (${task_id})! В проекте нет услуги (${product_id}) из задачи.`);
            }
        }
    },

    template: '#kabinet-content'
};