/*
 * Copyright (c) 24.05.2024
 * Suharkov Sergey (sexiterra@mail.ru)
 */

var task_status = function (){
    const calendarStore_ = calendarStore();
    const datatask_ = tasklistStore();

    // Общие константы статусов для всех функций (теперь как строки)
    const STATUS = {
        STOPPED: ['9', '10'],      // Остановленные/отмененные статусы
        PLANNED: ['0'],           // Запланированные статусы
        ACTIVE: ['1', '2', '3', '4', '5', '6', '7', '8'], // Активные статусы
        COMPLETED: ['9'],         // Завершенные статусы (часть STOPPED)
        INACTIVE: ['10']         // Неактивные статусы (часть STOPPED)
    };

    // Общие HTML-шаблоны для отображения статусов
    const TEMPLATES = {
        BASE: {
            stopped: '<div class="alert-status iphone-style-2 task-stoped"><i class="fa fa-times"></i></div>',
            wait: '<div class="alert-status iphone-style-2 task-wait"><i class="fa fa-clock-o"></i></div>',
            start: '<div class="alert-status iphone-style-2 task-start"><i class="fa fa-hourglass-start"></i></div>',
            done: '<div class="alert-only-text alert-done font-bold">Оформите заказ</div>',
            cancel: '<div class="alert-only-text alert-cancel font-bold"><i class="fa fa-times"></i> Остановлена</div>',
            planned: '<div class="alert-only-text alert-planned font-bold"><i class="fa fa-clock-o"></i> Запланирована</div>',
            worked: '<div class="alert-only-text alert-worked font-bold"><i class="fa fa-hourglass-start"></i> Выполняется</div>'
        }
    };

    // Базовая функция проверки статусов задачи
    const checkStatus = (taskEvents, task = null) => {
        // Если нет событий, считаем задачу выполненной
        if (!taskEvents.length) {
            return { type: 'done', isStopped: false, isPlanned: false };
        }

        // Получаем все статусы событий задачи
        const statuses = taskEvents.map(q => q.UF_STATUS);
        // Проверяем, остановлена ли задача (статус 14 или все события в STOPPED)
        const isStopped = (task && task.UF_STATUS === '14') ||
            statuses.every(s => STATUS.STOPPED.includes(s));
        // Проверяем, есть ли запланированные события
        const isPlanned = statuses.some(s => STATUS.PLANNED.includes(s));

        // Возвращаем тип статуса и флаги
        return {
            type: isStopped ? 'stopped' : isPlanned ? 'planned' : 'active',
            isStopped,
            isPlanned
        };
    };

    // Функция для отображения базового статуса задачи (иконка)
    const taskStatus_b = function(id_task) {
        const taskEvents = calendarStore_.getEventsByTaskId(id_task);
        const { type } = checkStatus(taskEvents);

        return type === 'stopped' ? TEMPLATES.BASE.stopped :
            type === 'planned' ? TEMPLATES.BASE.wait :
                type === 'done' ? '' : TEMPLATES.BASE.start;
    }

    // Функция для отображения расширенного статуса задачи (текст)
    const taskStatus_m = function(id_task) {
        const task = datatask_.datatask.find(t => t.ID === id_task);
        if (!task) return TEMPLATES.BASE.done;

        const taskEvents = calendarStore_.getEventsByTaskId(id_task);
        const { type } = checkStatus(taskEvents, task);

        return type === 'stopped' ? TEMPLATES.BASE.cancel :
            type === 'planned' ? TEMPLATES.BASE.planned :
                type === 'done' ? TEMPLATES.BASE.done : TEMPLATES.BASE.worked;
    }

    // Функция для получения статистики по статусам событий задачи
    const taskStatus_v = function(task_id) {
        const taskEvents = calendarStore_.getEventsByTaskId(task_id);

        // Если нет событий, возвращаем нулевые значения
        if (taskEvents.length === 0) {
            return {
                stopwark: 0,
                work: 0,
                endwork: 0,
                status: 'inactive'
            };
        }

        // Считаем количество событий по статусам
        const counts = taskEvents.reduce((acc, queue) => {
            // Для повторяющихся событий учитываем количество повторов
            const isMultiple = queue.UF_ELEMENT_TYPE === 'multiple';
            const increment = isMultiple ? parseInt(queue.UF_NUMBER_STARTS) || 0 : 1;

            // Распределение по статусам с использованием констант
            if (STATUS.COMPLETED.includes(queue.UF_STATUS)) {
                acc.endwork += increment;    // Завершенные события
            } else if (STATUS.PLANNED.includes(queue.UF_STATUS)) {
                acc.stopwark += increment;   // Запланированные события
            } else if (!STATUS.INACTIVE.includes(queue.UF_STATUS)) {
                acc.work += increment;      // Активные события (все кроме INACTIVE)
            }

            return acc;
        }, { stopwark: 0, work: 0, endwork: 0 });


        // Определяем общий статус на основе подсчетов
        counts.status = counts.endwork > 0 ? 'completed' :
            counts.stopwark > 0 ? 'planned' :
                counts.work > 0 ? 'active' : 'inactive';

        return counts;
    };

    return {taskStatus_m,taskStatus_v,taskStatus_b};
}