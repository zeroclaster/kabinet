/*
 * Copyright (c) 24.05.2024
 * Suharkov Sergey (sexiterra@mail.ru)
 */

var task_status = function (){

    const calendarStore_ = calendarStore();
    const datatask_ = tasklistStore();

        const countQueu = function (index){
            const task = datatask_.datatask[index];
            var countTaskQueue = 0;

            for(queue of calendarStore_.datacalendarQueue){
                if (queue.UF_TASK_ID == task.ID) countTaskQueue++;
            }
            return countTaskQueue;
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

    return {countQueu,taskStatus_m};
}