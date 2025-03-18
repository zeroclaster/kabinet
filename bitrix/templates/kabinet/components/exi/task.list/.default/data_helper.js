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
    const projectTask = function (project_id){
        let task = [];
        for(index in  this.datatask){
            if (this.datatask[index]['UF_PROJECT_ID'] == project_id) task.push(this.datatask[index]);
        }

        return task;
    }

    return {projectOrder, projectTask};
}