class TaskCalculator {
    constructor(taskManager) {
        this.taskManager = taskManager;
    }

    // Основной метод для пересчета всех параметров задачи
    recalculateTask(task, product) {
        const calculator = this.getCalculator(task, product);

        return {
            ...task,
            UF_DATE_COMPLETION: calculator.calculateDateCompletion(task, product),
            FINALE_PRICE: calculator.calculateFinalPrice(task, product),
            RUN_DATE: calculator.calculateRunDate(task, product)
        };
    }

    getCalculator(task, product) {
        const cyclicality = task.UF_CYCLICALITY;
        const elementType = product?.ELEMENT_TYPE?.VALUE;

        if (elementType === 'multiple') {
            return new MultipleTaskCalculator(this.taskManager);
        }

        switch (cyclicality) {
            case "1": // Однократное выполнение
            case "33": // Одно исполнение
                return new BoundTaskCalculator(this.taskManager);
            case "2": // Повторяется ежемесячно
            case "34": // Ежемесячная услуга
                return new CyclicalTaskCalculator(this.taskManager);
            default:
                return new DefaultTaskCalculator(this.taskManager);
        }
    }
}

class BaseTaskCalculator {
    constructor(taskManager) {
        this.taskManager = taskManager;
    }

    calculateDateCompletion(task, product) {
        return task.UF_DATE_COMPLETION;
    }

    calculateFinalPrice(task, product) {
        const price = product?.CATALOG_PRICE_1 || 0;
        const quantity = task.UF_NUMBER_STARTS || 1;
        return price * quantity;
    }

    calculateRunDate(task, product) {
        return task.RUN_DATE;
    }
}

class BoundTaskCalculator extends BaseTaskCalculator {
    calculateDateCompletion(task, product) {
        if (!task.UF_NUMBER_STARTS || !product?.MINIMUM_INTERVAL?.VALUE) {
            return task.UF_DATE_COMPLETION;
        }

        const dateStart = this.calculateDateStart(task, product);
        const hours = (task.UF_NUMBER_STARTS - 1) * product.MINIMUM_INTERVAL.VALUE;

        return moment.unix(dateStart).add(hours, 'hours').unix();
    }

    calculateDateStart(task, product) {
        const today = moment();

        // Если задача не начата
        if (!task.UF_STATUS || task.UF_STATUS === 0) {
            return today.add(product.DELAY_EXECUTION?.VALUE || 0, 'hours').unix();
        }

        // TODO: Добавить логику для начатых задач (поиск последнего исполнения)
        return today.unix();
    }

    calculateFinalPrice(task, product) {
        const price = product?.CATALOG_PRICE_1 || 0;
        const quantity = task.UF_NUMBER_STARTS || 1;
        return price * quantity;
    }
}

class CyclicalTaskCalculator extends BaseTaskCalculator {
    calculateDateCompletion(task, product) {
        const dateStart = this.calculateDateStart(task, product);
        const monthEnd = moment.unix(dateStart).endOf('month');
        return monthEnd.unix();
    }

    calculateDateStart(task, product) {
        const today = moment();

        if (task.UF_STATUS > 0) {
            // TODO: Добавить логику для начатых задач
            const nextMonth = today.add(1, 'month').startOf('month');
            return nextMonth.unix();
        }

        // Новая задача
        today.add(product.DELAY_EXECUTION?.VALUE || 0, 'hours');
        const firstDayNextMonth = today.add(1, 'month').startOf('month');

        if (today.isAfter(firstDayNextMonth)) {
            return firstDayNextMonth.add(product.DELAY_EXECUTION?.VALUE || 0, 'hours').unix();
        }

        return today.unix();
    }
}

class MultipleTaskCalculator extends BaseTaskCalculator {
    calculateFinalPrice(task, product) {
        const price = product?.CATALOG_PRICE_1 || 0;
        const quantity = task.UF_NUMBER_STARTS || 1;
        return price * quantity;
    }

    calculateDateCompletion(task, product) {
        // Для multiple задач дата завершения обычно равна дате выполнения
        return task.UF_DATE_COMPLETION;
    }
}

class DefaultTaskCalculator extends BaseTaskCalculator {}