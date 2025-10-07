const balance_operations = {
    computed: {
        calculateCommission() {
            if (!this.bankTransfer.amount || this.bankTransfer.amount <= 0) return '0.00 руб.';
            const commission = this.bankTransfer.amount * 0.03;
            return commission.toFixed(2) + ' руб.';
        },
        calculateFinalAmount() {
            if (!this.bankTransfer.amount || this.bankTransfer.amount <= 0) return '0.00 руб.';
            const finalAmount = this.bankTransfer.amount * 0.97;
            return finalAmount.toFixed(2) + ' руб.';
        },
        // ВЫЧИСЛЯЕМОЕ СВОЙСТВО ДЛЯ ТЕКУЩЕГО КЛИЕНТА
        currentClient() {
            // dataclient содержит либо одного выбранного клиента, либо пустой массив
            return this.dataclient.length > 0 ? this.dataclient[0] : null;
        }
    },
    mounted() {
        // Сохраняем ссылку на инстанс для доступа из фильтра
        window.balanceOperationsApp = this;
    },
    methods: {
        ...helperVueComponents(),

        /**
         * Валидация числа - только цифры и точка
         */
        validateNumber(value) {
            return /^\d*\.?\d*$/.test(value);
        },

        /**
         * Обработчик ввода для полей суммы
         */
        handleAmountInput(event, formType) {
            const value = event.target.value;

            // Разрешаем только цифры и точку
            if (!this.validateNumber(value)) {
                event.target.value = value.replace(/[^\d.]/g, '');
            }

            // Ограничиваем количество знаков после запятой
            const parts = event.target.value.split('.');
            if (parts.length > 1 && parts[1].length > 2) {
                event.target.value = parts[0] + '.' + parts[1].substring(0, 2);
            }

            // Обновляем соответствующее поле данных
            if (formType === 'bankTransfer') {
                this.bankTransfer.amount = parseFloat(event.target.value) || 0;
            } else if (formType === 'freeReplenishment') {
                this.freeReplenishment.amount = parseFloat(event.target.value) || 0;
            } else if (formType === 'withdraw') {
                this.withdraw.amount = parseFloat(event.target.value) || 0;
            }
        },

        /**
         * Проверка максимальной суммы (50 000)
         */
        validateMaxAmount(amount, operationType) {
            const MAX_AMOUNT = 50000;
            if (amount > MAX_AMOUNT) {
                this.showFormMessage(`Максимальная сумма для ${operationType} - 50 000 руб.`, false, operationType);
                return false;
            }
            return true;
        },

        /**
         * Показ сообщения для конкретной формы
         */
        showFormMessage(text, success, formType) {
            if (formType === 'bankTransfer') {
                this.bankTransfer.message = {
                    text: text,
                    success: success
                };
                setTimeout(() => {
                    this.bankTransfer.message = null;
                }, 2000);
            } else if (formType === 'freeReplenishment') {
                this.freeReplenishment.message = {
                    text: text,
                    success: success
                };
                setTimeout(() => {
                    this.freeReplenishment.message = null;
                }, 2000);
            } else if (formType === 'withdraw') {
                this.withdraw.message = {
                    text: text,
                    success: success
                };
                setTimeout(() => {
                    this.withdraw.message = null;
                }, 2000);
            }
        },

        async submitBankTransfer() {
            // ИСПОЛЬЗУЕМ currentClient ВМЕСТО selectedClient
            if (!this.currentClient || !this.bankTransfer.amount || this.bankTransfer.amount <= 0) {
                this.showFormMessage('Введите корректную сумму', false, 'bankTransfer');
                return;
            }

            // Проверка максимальной суммы
            if (!this.validateMaxAmount(this.bankTransfer.amount, 'пополнения банковским переводом')) {
                return;
            }

            try {
                const response = await BX.ajax.runComponentAction(
                    PHPPARAMS.componentName,
                    'bankTransfer',
                    {
                        mode: 'class',
                        data: {
                            signedParameters: PHPPARAMS.signedParameters,
                            client_id: this.currentClient.ID, // ИСПОЛЬЗУЕМ currentClient
                            amount: parseFloat(this.bankTransfer.amount)
                        },
                        getParameters: {usr : this.currentClient.ID}
                    }
                );

                if (response.data && response.data.success) {
                    this.showFormMessage(response.data.message, true, 'bankTransfer');
                    this.clearForms();
                }
            } catch (error) {
                console.error('Ошибка пополнения баланса:', error);
                const errorMessage = error.errors && error.errors[0] ? error.errors[0].message : 'Ошибка при пополнении баланса';
                this.showFormMessage(errorMessage, false, 'bankTransfer');
            }
        },

        async submitFreeReplenishment() {
            // ИСПОЛЬЗУЕМ currentClient ВМЕСТО selectedClient
            if (!this.currentClient || !this.freeReplenishment.amount || this.freeReplenishment.amount <= 0) {
                this.showFormMessage('Введите корректную сумму', false, 'freeReplenishment');
                return;
            }

            // Проверка максимальной суммы
            if (!this.validateMaxAmount(this.freeReplenishment.amount, 'свободного пополнения')) {
                return;
            }

            try {
                const response = await BX.ajax.runComponentAction(
                    PHPPARAMS.componentName,
                    'freeReplenishment',
                    {
                        mode: 'class',
                        data: {
                            signedParameters: PHPPARAMS.signedParameters,
                            client_id: this.currentClient.ID, // ИСПОЛЬЗУЕМ currentClient
                            amount: parseFloat(this.freeReplenishment.amount),
                            comment: this.freeReplenishment.comment
                        },
                        getParameters: {usr : this.currentClient.ID}
                    }
                );

                if (response.data && response.data.success) {
                    this.showFormMessage(response.data.message, true, 'freeReplenishment');
                    this.clearForms();
                }
            } catch (error) {
                console.error('Ошибка свободного пополнения:', error);
                const errorMessage = error.errors && error.errors[0] ? error.errors[0].message : 'Ошибка при пополнении баланса';
                this.showFormMessage(errorMessage, false, 'freeReplenishment');
            }
        },

        async submitWithdraw() {
            // ИСПОЛЬЗУЕМ currentClient ВМЕСТО selectedClient
            if (!this.currentClient || !this.withdraw.amount || this.withdraw.amount <= 0) {
                this.showFormMessage('Введите корректную сумму', false, 'withdraw');
                return;
            }

            // Проверка максимальной суммы
            if (!this.validateMaxAmount(this.withdraw.amount, 'списания')) {
                return;
            }

            try {
                const response = await BX.ajax.runComponentAction(
                    PHPPARAMS.componentName,
                    'withdraw',
                    {
                        mode: 'class',
                        data: {
                            signedParameters: PHPPARAMS.signedParameters,
                            client_id: this.currentClient.ID, // ИСПОЛЬЗУЕМ currentClient
                            amount: parseFloat(this.withdraw.amount),
                            comment: this.withdraw.comment
                        },
                        getParameters: {usr : this.currentClient.ID}
                    }
                );

                if (response.data && response.data.success) {
                    this.showFormMessage(response.data.message, true, 'withdraw');
                    this.clearForms();
                }
            } catch (error) {
                console.error('Ошибка списания:', error);
                const errorMessage = error.errors && error.errors[0] ? error.errors[0].message : 'Ошибка при списании с баланса';
                this.showFormMessage(errorMessage, false, 'withdraw');
            }
        },

        clearForms() {
            this.bankTransfer.amount = 0;
            this.freeReplenishment.amount = 0;
            this.freeReplenishment.comment = '';
            this.withdraw.amount = 0;
            this.withdraw.comment = '';


        },

        showMessage(text, success) {
            this.operationMessage = {
                text: text,
                success: success
            };

            setTimeout(() => {
                this.operationMessage = null;
            }, 5000);
        }
    },

    template: '#balance-operations-content'
};