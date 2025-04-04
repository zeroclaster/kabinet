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

        if (isRuned == countQueu2(task)) return '<div class="alert-status iphone-style-2 task-stoped"><i class="fa fa-times" aria-hidden="true"></i></div>';

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

        if (isRuned > 0) return '<div class="alert-status iphone-style-2 task-wait"><i class="fa fa-clock-o" aria-hidden="true"></i></div>';


        return '<div class="alert-status iphone-style-2 task-start"><i class="fa fa-hourglass-start" aria-hidden="true"></i></div>';
    }


        const taskStatus_m = function (id_task){
            const task = this.findinArrayByID(datatask_.datatask,id_task);

            if (countQueu2(task) == 0) return '<div class="alert-only-text alert-done font-bold">Оформите заказ</div>';


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

            if (isRuned == countQueu2(task)) return '<div class="alert-only-text alert-cancel font-bold"><i class="fa fa-times" aria-hidden="true"></i> Остановлена</div>';

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

            if (isRuned > 0) return '<div class="alert-only-text alert-planned font-bold"><i class="fa fa-clock-o" aria-hidden="true"></i> Запланирована</div>';


            return '<div class="alert-only-text alert-worked font-bold"><i class="fa fa-hourglass-start" aria-hidden="true"></i> Выполняется</div>';
        }


    const taskStatus_v = function (index){
        if (countQueu(index) == 0) return '<div class="alert-only-text alert-done">Не выполняется</div>';
        const task = datatask_.datatask[index];

        let stopwark = 0;
        let work = 0;
        let endwork = 0;

        for(queue of calendarStore_.datacalendarQueue){
            if (queue.UF_TASK_ID != task.ID) continue;
            if (queue.UF_STATUS == 9) {
                if (queue.UF_ELEMENT_TYPE == 'multiple') endwork = endwork + parseInt(queue.UF_NUMBER_STARTS);
                else
                endwork++;
            }
            else if(queue.UF_STATUS == 0){
                if (queue.UF_ELEMENT_TYPE == 'multiple') stopwark = stopwark + parseInt(queue.UF_NUMBER_STARTS);
                else
                stopwark++;
            }
            else if(queue.UF_STATUS != 10)
                if (queue.UF_ELEMENT_TYPE == 'multiple') work = work + parseInt(queue.UF_NUMBER_STARTS);
                else
                work++
        }

        return {'stopwark':stopwark,'work':work,'endwork':endwork};
    }

    return {countQueu,taskStatus_m,taskStatus_v,taskStatus_b};
}