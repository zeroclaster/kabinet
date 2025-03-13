/*
 * Copyright (c) 24.05.2024
 * Suharkov Sergey (sexiterra@mail.ru)
 */

const textInfoTask = BX.Vue3.BitrixVue.mutableComponent('text-Info-Task', {
    template: `
                    <!-- Однократное выполнение -->
                    <template v-if="task.UF_CYCLICALITY == 33">
                    <div class="mt-3 mb-3" v-if="task.UF_STATUS==0">
                        Заданное количество будет равномерно выполнено до заданной даты. Вы всегда сможете дозаказать ещё нужное количество.
                    </div>
                    <div class="mt-3 mb-3" v-if="task.UF_STATUS>0">
                        Вы можете заказать ещё «{{task.UF_NAME}}», указав нужное количество и продлить выполнение задачи до выбранной даты.
                    </div>
                    </template>


                    <!-- Одно исполнение -->
                    <template v-if="task.UF_CYCLICALITY == 1">
                    <div class="mt-3 mb-3" v-if="task.UF_STATUS==0">
                        Заданное количество будет равномерно выполнено до заданной даты. Вы всегда сможете дозаказать ещё нужное количество.
                    </div>
                    <div class="mt-3 mb-3" v-if="task.UF_STATUS>0">
                        Вы можете заказать ещё «{{task.UF_NAME}}», указав нужное количество и продлить выполнение задачи до выбранной даты.
                    </div>
                    </template>

                    <!-- Повторяется ежемесячно -->
                    <template v-if="task.UF_CYCLICALITY == 2">
                    <div class="mt-3 mb-3" v-if="task.UF_STATUS==0">
                        Запустится ежемесячное выполнение задачи с заданным количеством. Средства с баланса зарезервируются при запуске, а далее ежемесячно 1 числа. Вы сможете изменить количество или остановить выполнение в любой момент с 1 числа следующего месяца.
                    </div>
                    <div class="mt-3 mb-3" v-if="task.UF_STATUS>0">
                        Вы можете изменить ежемесячное количество. Изменение вступит в силу с {{getmomment().add(1, 'months').startOf('month').format('DD.MM.YYYY')}}.
                    </div>
                    </template>

                    <!-- Ежемесячная услуга -->
                    <template v-if="task.UF_CYCLICALITY == 34">
                    <div class="mt-3 mb-3" v-if="task.UF_STATUS==0">
                        Запустится ежемесячное выполнение задачи. Средства с баланса зарезервируются при запуске, а далее ежемесячно 1 числа. Пополняйте баланс заблаговременно до 1 числа ежемесячно.
                    </div>
                    <div class="mt-3 mb-3" v-if="task.UF_STATUS>0">
                        Ежемесячная услуга, ближайшая отчетная дата и дата следующего списания средств: {{task.RUN_DATE}}. <span v-if="task.UF_STATUS == 15" style="word-wrap: unset;"><button class="btn btn-link btn-link-site" type="button" style="padding: 0" @click="stoptask(taskindex)">Остановить с {{task.RUN_DATE}}</button></span>
                    </div>
                    </template>`,
    data(){
        return{}
    },
    props: ['task','taskindex'],
    setup(){
        const getmomment = ()=>moment();
        return {getmomment};
    },
    methods: {
        stoptask(taskindex){
            this.$root.stoptask(taskindex);
        }
    },
    mounted () {
    }
});