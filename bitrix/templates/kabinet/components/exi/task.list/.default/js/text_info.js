/*
 * Copyright (c) 24.05.2024
 * Suharkov Sergey (sexiterra@mail.ru)
 */

const STATUS_CONDITIONS = {
    NOT_ACTIVE: (status) => status === 0,
    IN_WORK: (status) => status > 0
};

const textInfoMessages = [
    {
        cyclicality: 1,
        statusCondition: STATUS_CONDITIONS.NOT_ACTIVE,
        isActive: false,
        text: "Заданное количество будет равномерно выполнено до заданной даты. Вы всегда сможете дозаказать ещё нужное количество."
    },
    {
        cyclicality: 1,
        statusCondition: STATUS_CONDITIONS.IN_WORK,
        isActive: true,
        text: "Вы можете заказать ещё «{{tsk.UF_NAME}}», указав нужное количество и продлить выполнение задачи до выбранной даты."
    },
    {
        cyclicality: 2,
        statusCondition: STATUS_CONDITIONS.NOT_ACTIVE,
        isActive: false,
        text: "Запустится ежемесячное выполнение задачи с заданным количеством. Средства с баланса зарезервируются при запуске, а далее ежемесячно 1 числа. Вы сможете изменить количество или остановить выполнение в любой момент с 1 числа следующего месяца."
    },
    {
        cyclicality: 2,
        statusCondition: STATUS_CONDITIONS.IN_WORK,
        isActive: true,
        text: "Это ежемесячная задача, исполнения автоматически планируются 1 числа на следующий месяц в количестве {{tsk.UF_NUMBER_STARTS}}. \n" +
            "Вы можете сейчас изменить ежемесячное количество, изменение вступит с {{tsk.RUN_DATE}}."
    },
    {
        cyclicality: 34,
        statusCondition: STATUS_CONDITIONS.NOT_ACTIVE,
        isActive: false,
        text: "Запустится ежемесячное выполнение задачи. Средства с баланса зарезервируются при запуске, а далее ежемесячно 1 числа. Пополняйте баланс заблаговременно до 1 числа ежемесячно."
    },
    {
        cyclicality: 34,
        statusCondition: STATUS_CONDITIONS.IN_WORK,
        isActive: true,
        text: "Ежемесячная услуга, ближайшая отчетная дата и дата следующего списания средств: {{tsk.RUN_DATE34}}."
    }
];

const textInfoTask = BX.Vue3.BitrixVue.mutableComponent('text-Info-Task', {
    template: `
    <template v-for="msg in visibleMessages" :key="msg.cyclicality + '-' + msg.isActive">
      <div class="mt-3 mb-3">
        {{ replacePlaceholders(msg.text) }}
      </div>
    </template>
  `,

    props: ['task', 'copyTask', 'taskindex'],
    setup(){
        const getmomment = ()=>moment();
        return {getmomment};
    },
    computed: {
        tsk() {
            return this.task[this.taskindex];
        },
        copytsk() {
            return this.copyTask[this.taskindex];
        },
        visibleMessages() {
            return textInfoMessages.filter(msg =>
                String(msg.cyclicality) === this.copytsk.UF_CYCLICALITY &&
                msg.statusCondition(this.tsk.UF_STATUS)
            );
        }
    },

    methods: {
        replacePlaceholders(text) {
            return text
                .replace('{{tsk.UF_NAME}}', this.tsk.UF_NAME || '')
                .replace('{{tsk.RUN_DATE}}', this.tsk.RUN_DATE || '')
                .replace('{{tsk.UF_NUMBER_STARTS}}', this.tsk.UF_NUMBER_STARTS || '')
                .replace('{{tsk.RUN_DATE34}}', moment.unix(this.tsk.UF_DATE_COMPLETION).add(1, 'month').startOf('month').format("DD.MM.YYYY") || '');

        },
        stoptask(taskindex) {
            this.$root.stoptask(taskindex);
        }
    }
});