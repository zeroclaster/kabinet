/*
 * Copyright (c) 24.05.2024
 * Suharkov Sergey (sexiterra@mail.ru)
 */

/*
 * Copyright (c) 24.05.2024
 * Suharkov Sergey (sexiterra@mail.ru)
 */

var task_status = function (PHPPARAMS ={}){
    const calendarStore_ = calendarStore();
    const datatask_ = tasklistStore();

    const ALERT_STATUS = PHPPARAMS.ALERT_STATUS;


    // Общие константы статусов для всех функций (теперь как строки)
    const STATUS = {
        STOPPED: ['9', '10'],      // Остановленные/отмененные статусы
        PLANNED: ['0'],           // Запланированные статусы
        ACTIVE: ['1', '2', '3', '4', '5', '6', '7', '8'], // Активные статусы
        COMPLETED: ['9'],         // Завершенные статусы (часть STOPPED)
        INACTIVE: ['10'],         // Неактивные статусы (часть STOPPED)
        ALERT: ALERT_STATUS || [] // Статусы, требующие внимания
    };

    // Общие HTML-шаблоны для отображения статусов
    const TEMPLATES = {
        BASE: {
            stopped: '<div class="alert-status iphone-style-2 task-stoped text-nowrap"><i class="fa fa-times"></i></div>',
            wait: '<div class="alert-status iphone-style-2 task-wait text-nowrap"><i class="fa fa-clock-o"></i></div>',
            start: '<div class="alert-status iphone-style-2 task-start text-nowrap"><i class="fa fa-hourglass-start"></i></div>',
            alert: '<div class="alert-status iphone-style-2 task-alert text-nowrap"><i class="fa fa-exclamation-triangle"></i></div>',
            done: '<div class="alert-only-text alert-done font-bold text-nowrap">Оформите заказ</div>',
            cancel: '<div class="alert-only-text alert-cancel font-bold text-nowrap"><i class="fa fa-times"></i> Остановлена</div>',
            planned: '<div class="alert-only-text alert-planned font-bold text-nowrap"><i class="fa fa-clock-o"></i> Запланирована</div>',
            worked: '<div class="alert-only-text alert-worked font-bold text-nowrap"><i class="fa fa-hourglass-start"></i> Выполняется</div>',
            attention: '<div class="alert-only-text alert-user-attention font-bold text-nowrap"><i class="fa fa-exclamation-triangle"></i> Требует внимания</div>'
        }
    };

    // Базовая функция проверки статусов задачи
    const checkStatus = (taskEvents, task = null) => {
        // Если нет событий, считаем задачу выполненной
        if (!taskEvents.length) {
            return { type: 'done', isStopped: false, isPlanned: false, hasAlert: false };
        }

        // Получаем все статусы событий задачи
        const statuses = taskEvents.map(q => q.UF_STATUS);

        // Проверяем, есть ли статусы, требующие внимания
        const hasAlert = statuses.some(s => STATUS.ALERT.includes(s));

        // Проверяем, остановлена ли задача (статус 14 или все события в STOPPED)
        const isStopped = (task && task.UF_STATUS === '14') ||
            statuses.every(s => STATUS.STOPPED.includes(s));

        // Проверяем, есть ли запланированные события
        const isPlanned = statuses.some(s => STATUS.PLANNED.includes(s));

        // Определяем основной тип статуса с учетом тревожных статусов
        let type;
        if (hasAlert) {
            type = 'alert';
        } else if (isStopped) {
            type = 'stopped';
        } else if (isPlanned) {
            type = 'planned';
        } else {
            type = 'active';
        }

        // Возвращаем тип статуса и флаги
        return {
            type,
            isStopped,
            isPlanned,
            hasAlert
        };
    };

    // Функция для отображения базового статуса задачи (иконка)
    const taskStatus_b = function(id_task) {
        const taskEvents = calendarStore_.getEventsByTaskId(id_task);
        const { type } = checkStatus(taskEvents);

        switch (type) {
            case 'alert': return TEMPLATES.BASE.alert;
            case 'stopped': return TEMPLATES.BASE.stopped;
            case 'planned': return TEMPLATES.BASE.wait;
            case 'done': return '';
            default: return TEMPLATES.BASE.start;
        }
    }

    // Функция для отображения расширенного статуса задачи (текст)
    const taskStatus_m = function(id_task) {
        const task = datatask_.datatask.find(t => t.ID === id_task);
        if (!task) return TEMPLATES.BASE.done;

        const taskEvents = calendarStore_.getEventsByTaskId(id_task);
        const { type } = checkStatus(taskEvents, task);

        switch (type) {
            case 'alert': return TEMPLATES.BASE.attention;
            case 'stopped': return TEMPLATES.BASE.cancel;
            case 'planned': return TEMPLATES.BASE.planned;
            case 'done': return TEMPLATES.BASE.done;
            default: return TEMPLATES.BASE.worked;
        }
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
                alert: 0,
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

            // Считаем события, требующие внимания
            if (STATUS.ALERT.includes(queue.UF_STATUS)) {
                acc.alert += increment;
            }

            return acc;
        }, { stopwark: 0, work: 0, endwork: 0, alert: 0 });

        // Определяем общий статус на основе подсчетов
        counts.status = counts.alert > 0 ? 'alert' :
            counts.endwork > 0 ? 'completed' :
                counts.stopwark > 0 ? 'planned' :
                    counts.work > 0 ? 'active' : 'inactive';

        return counts;
    };

    // Дополнительная функция для проверки наличия статусов тревоги
    const hasAlertStatus = function(task_id) {
        const counts = taskStatus_v(task_id);
        return counts.alert > 0;
    };

    return {
        taskStatus_m,
        taskStatus_v,
        taskStatus_b,
        hasAlertStatus
    };
}