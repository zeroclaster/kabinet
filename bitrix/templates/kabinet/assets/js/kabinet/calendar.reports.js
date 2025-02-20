var calendar_reports = document.calendar_reports || {};
calendar_reports = (function (){
    return {
        start(PHPPARAMS){

            if (typeof PHPPARAMS.TASK_ID === "undefined" || PHPPARAMS.TASK_ID == '')
                throw "Field TASK_ID not found!";

            // bitrix/templates/kabinet/components/exi/task.list/.default/queue.data.php
            const calendar = calendarStore();
            const find_queue = [];
            calendar.datacalendarQueue.forEach(function (element) {
              if (PHPPARAMS.TASK_ID == element.UF_TASK_ID) find_queue.push(element);
            });

			calendar.updatecalendareReports(find_queue);
        }
    }
}());