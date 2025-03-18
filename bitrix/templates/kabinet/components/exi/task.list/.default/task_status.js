/*
 * Copyright (c) 24.05.2024
 * Suharkov Sergey (sexiterra@mail.ru)
 */

var task_status = function (){

    const calendarStore_ = calendarStore();
    const datatask_ = tasklistStore();

        const getTaskByID = function (id){

        }

        const countQueu = function (index){
            const task = datatask_.datatask[index];
            var countTaskQueue = 0;

            for(queue of calendarStore_.datacalendarQueue){
                if (queue.UF_TASK_ID == task.ID) countTaskQueue++;
            }
            return countTaskQueue;
        }

    const countQueu2 = function (task){
        var countTaskQueue = 0;

        for(queue of calendarStore_.datacalendarQueue){
            if (queue.UF_TASK_ID == task.ID) countTaskQueue++;
        }
        return countTaskQueue;
    }

    const taskStatus_b = function (id_task){

        const task = this.findinArrayByID(datatask_.datatask,id_task);

        if (countQueu2(task) == 0) return '';


        let isRuned = 0;

        isRuned = 0;
        for(queue of calendarStore_.datacalendarQueue){
            if (
                queue.UF_TASK_ID == task.ID &&
                (
                    queue.UF_STATUS == 10 ||
                    queue.UF_STATUS == 9
                )
            ) {
                isRuned++;
            }
        }

        if (isRuned == countQueu2(task)) return '<i class="fa fa-times" aria-hidden="true"></i>';

        isRuned = 0;
        for(queue of calendarStore_.datacalendarQueue){
            if (
                queue.UF_TASK_ID == task.ID &&
                (
                    queue.UF_STATUS == 0
                )
            ) {
                isRuned++;
            }
        }

        if (isRuned > 0) return '<i class="fa fa-clock-o" aria-hidden="true"></i>';


        return '<i class="fa fa-hourglass-start" aria-hidden="true"></i>';
    }

        const taskStatus_m = function (index){
            if (countQueu(index) == 0) return '<div class="status-task-1 text-warning">Не выполняется</div>';
            const task = datatask_.datatask[index];

            let isRuned = 0;

            isRuned = 0;
            for(queue of calendarStore_.datacalendarQueue){
                if (
                    queue.UF_TASK_ID == task.ID &&
                    (
                        queue.UF_STATUS == 10 ||
                        queue.UF_STATUS == 9
                    )
                ) {
                    isRuned++;
                }
            }

            if (isRuned == countQueu(index)) return '<div class="status-task-1 text-secondary">Остановлена</div>';

            isRuned = 0;
            for(queue of calendarStore_.datacalendarQueue){
                if (
                    queue.UF_TASK_ID == task.ID &&
                    (
                        queue.UF_STATUS == 0
                    )
                ) {
                    isRuned++;
                }
            }

            if (isRuned > 0) return '<div class="status-task-1 text-warning">Запланирована</div>';


            return '<div class="status-task-1 text-success">Выполняется</div>';
        }


    const taskStatus_v = function (index){
        if (countQueu(index) == 0) return '<div class="status-task-1 text-warning">Не выполняется</div>';
        const task = datatask_.datatask[index];

        let stopwark = 0;
        let work = 0;
        let endwork = 0;

        for(queue of calendarStore_.datacalendarQueue){
            if (queue.UF_TASK_ID != task.ID) continue;
            if (queue.UF_STATUS == 9) {
                endwork++;
            }
            else if(queue.UF_STATUS == 0){
                stopwark++;
            }
            else if(queue.UF_STATUS != 10)
                work++
        }

        return {'stopwark':stopwark,'work':work,'endwork':endwork};
    }

    return {countQueu,taskStatus_m,taskStatus_v,taskStatus_b};
}