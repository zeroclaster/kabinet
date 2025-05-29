const data_helper = function () {

    const projectOrder = function (id){
        var findOrder = 0;
        this.data.forEach(function(element){
            if (!findOrder && element.ID == id){
                findOrder = element.UF_ORDER_ID;
            }
        });

        return findOrder;
    };

    const projectTask = function(project_id) {
        if (!this.datatask || !Array.isArray(this.datatask)) return [];
        return this.datatask.filter(task => task.UF_PROJECT_ID == project_id);
    }

    return {projectOrder, projectTask};
}