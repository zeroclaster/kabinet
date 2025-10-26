const balance_operations = {
    computed: {
        calculateCommission() {
            if (!this.bankTransfer.amount || this.bankTransfer.amount <= 0) return '0.00 руб.';
            //const commission = this.bankTransfer.amount * 0.0;
            const commission = this.bankTransfer.amount * 1;
            return commission.toFixed(2) + ' руб.';
        },
        calculateFinalAmount() {
            if (!this.bankTransfer.amount || this.bankTransfer.amount <= 0) return '0.00 руб.';
            const finalAmount = this.bankTransfer.amount * 1;
            //const finalAmount = this.bankTransfer.amount * 0.97;
            return finalAmount.toFixed(2) + ' руб.';
        },
        currentClient() {
            return this.dataclient.length > 0 ? this.dataclient[0] : null;
        },
        currentBilling() {
            return this.billingdata && Object.keys(this.billingdata).length > 0 ? this.billingdata : {};
        }
    },
    mounted() {
        window.balanceOperationsApp = this;
    },
    methods: {
        ...helperVueComponents(),

        /**
         * Валидация числа - цифры, точка и запятая
         */
        validateNumber(value) {
            return /^[\d.,]*$/.test(value);
        },

        /**
         * Нормализация разделителя десятичных - заменяем запятую на точку
         */
        normalizeDecimalSeparator(value) {
            return value.replace(',', '.');
        },

        /**
         * Проверка корректности десятичного числа
         */
        isValidDecimal(value) {
            if (!value) return true;

            const normalized = this.normalizeDecimalSeparator(value);
            // Проверяем, что после нормализации это валидное десятичное число
            return /^\d*\.?\d*$/.test(normalized);
        },

        /**
         * Форматирование валюты
         */
        formatCurrency(amount) {
            return new Intl.NumberFormat('ru-RU', {
                style: 'currency',
                currency: 'RUB',
                minimumFractionDigits: 2
            }).format(amount);
        },

        /**
         * Получение времени обновления биллинга
         */
        getBillingUpdateTime() {
            return this.lastBillingUpdate.toLocaleTimeString('ru-RU');
        },

        /**
         * Обновление данных биллинга
         */
        async refreshBillingData() {
            if (!this.currentClient) return;

            try {
                this.lastBillingUpdate = new Date();
                const response = await this.fetchBillingData(this.currentClient.ID);
                if (response.success) {
                    this.billingdata = response.billingData;
                    this.lastBillingUpdate = new Date();
                }
            } catch (error) {
                console.error('Ошибка обновления биллинга:', error);
            }
        },

        /**
         * Запрос данных биллинга с сервера
         */
        async fetchBillingData(clientId) {
            try {
                const response = await BX.ajax.runComponentAction(
                    PHPPARAMS.componentName,
                    'getbillingdata',
                    {
                        mode: 'class',
                        data: {
                            signedParameters: PHPPARAMS.signedParameters,
                            client_id: clientId
                        }
                    }
                );
                return response.data;
            } catch (error) {
                console.error('Ошибка получения данных биллинга:', error);
                return { success: false };
            }
        },

        /**
         * Обработчик ввода для полей суммы
         */
        handleAmountInput(event, formType) {
            const value = event.target.value;

            // Разрешаем только цифры, точку и запятую
            if (!this.validateNumber(value)) {
                event.target.value = value.replace(/[^\d.,]/g, '');
                return;
            }

            // Нормализуем разделитель (запятую в точку)
            const normalizedValue = this.normalizeDecimalSeparator(value);

            // Проверяем валидность десятичного числа
            if (!this.isValidDecimal(normalizedValue)) {
                // Если невалидное, откатываем к предыдущему значению
                if (formType === 'bankTransfer') {
                    event.target.value = this.bankTransfer.amount || '';
                } else if (formType === 'freeReplenishment') {
                    event.target.value = this.freeReplenishment.amount || '';
                } else if (formType === 'withdraw') {
                    event.target.value = this.withdraw.amount || '';
                }
                return;
            }

            // Ограничиваем количество знаков после запятой
            const parts = normalizedValue.split('.');
            if (parts.length > 1 && parts[1].length > 2) {
                event.target.value = parts[0] + '.' + parts[1].substring(0, 2);
            } else {
                event.target.value = value; // Сохраняем оригинальное значение с запятой если нужно
            }

            // Обновляем соответствующее поле данных (сохраняем нормализованное значение)
            const finalValue = parts.length > 1 ? parts[0] + '.' + parts[1] : normalizedValue;
            const numericValue = parseFloat(finalValue) || null;

            if (formType === 'bankTransfer') {
                this.bankTransfer.amount = numericValue;
            } else if (formType === 'freeReplenishment') {
                this.freeReplenishment.amount = numericValue;
            } else if (formType === 'withdraw') {
                this.withdraw.amount = numericValue;
            }
        },

        /**
         * Проверка максимальной суммы
         */
        validateMaxAmount(amount, operationType) {
            const MAX_AMOUNT = 1000000;
            if (amount > MAX_AMOUNT) {
                this.showFormMessage(`Максимальная сумма для ${operationType} - 1 000 000 руб.`, false, operationType);
                return false;
            }
            return true;
        },

        /**
         * Проверка минимальной суммы для копеек
         */
        validateDecimalAmount(amount, formType) {
            if (amount === null || amount === 0) return true;

            // Проверяем, что число имеет не более 2 знаков после запятой
            const amountStr = amount.toString();
            const parts = amountStr.split('.');
            if (parts.length > 1 && parts[1].length > 2) {
                this.showFormMessage('Сумма не может иметь более 2 знаков после запятой', false, formType);
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
                }, 5000);
            } else if (formType === 'freeReplenishment') {
                this.freeReplenishment.message = {
                    text: text,
                    success: success
                };
                setTimeout(() => {
                    this.freeReplenishment.message = null;
                }, 5000);
            } else if (formType === 'withdraw') {
                this.withdraw.message = {
                    text: text,
                    success: success
                };
                setTimeout(() => {
                    this.withdraw.message = null;
                }, 5000);
            }
        },

        async submitBankTransfer() {
            if (!this.currentClient || !this.bankTransfer.amount || this.bankTransfer.amount <= 0) {
                this.showFormMessage('Введите корректную сумму', false, 'bankTransfer');
                return;
            }

            if (!this.validateDecimalAmount(this.bankTransfer.amount, 'bankTransfer') ||
                !this.validateMaxAmount(this.bankTransfer.amount, 'пополнения банковским переводом')) {
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
                            client_id: this.currentClient.ID,
                            amount: parseFloat(this.bankTransfer.amount)
                        },
                        getParameters: {usr : this.currentClient.ID}
                    }
                );

                if (response.data && response.data.success) {
                    this.showFormMessage(response.data.message, true, 'bankTransfer');
                    await this.refreshBillingData();
                    this.clearForms();
                }
            } catch (error) {
                console.error('Ошибка пополнения баланса:', error);
                const errorMessage = error.errors && error.errors[0] ? error.errors[0].message : 'Ошибка при пополнении баланса';
                this.showFormMessage(errorMessage, false, 'bankTransfer');
            }
        },

        async submitFreeReplenishment() {
            if (!this.currentClient || !this.freeReplenishment.amount || this.freeReplenishment.amount <= 0) {
                this.showFormMessage('Введите корректную сумму', false, 'freeReplenishment');
                return;
            }

            if (!this.validateDecimalAmount(this.freeReplenishment.amount, 'freeReplenishment') ||
                !this.validateMaxAmount(this.freeReplenishment.amount, 'свободного пополнения')) {
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
                            client_id: this.currentClient.ID,
                            amount: parseFloat(this.freeReplenishment.amount),
                            comment: this.freeReplenishment.comment
                        },
                        getParameters: {usr : this.currentClient.ID}
                    }
                );

                if (response.data && response.data.success) {
                    this.showFormMessage(response.data.message, true, 'freeReplenishment');
                    await this.refreshBillingData();
                    this.clearForms();
                }
            } catch (error) {
                console.error('Ошибка свободного пополнения:', error);
                const errorMessage = error.errors && error.errors[0] ? error.errors[0].message : 'Ошибка при пополнении баланса';
                this.showFormMessage(errorMessage, false, 'freeReplenishment');
            }
        },

        async submitWithdraw() {
            if (!this.currentClient || !this.withdraw.amount || this.withdraw.amount <= 0) {
                this.showFormMessage('Введите корректную сумму', false, 'withdraw');
                return;
            }

            if (!this.validateDecimalAmount(this.withdraw.amount, 'withdraw') ||
                !this.validateMaxAmount(this.withdraw.amount, 'списания')) {
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
                            client_id: this.currentClient.ID,
                            amount: parseFloat(this.withdraw.amount),
                            comment: this.withdraw.comment
                        },
                        getParameters: {usr : this.currentClient.ID}
                    }
                );

                if (response.data && response.data.success) {
                    this.showFormMessage(response.data.message, true, 'withdraw');
                    await this.refreshBillingData();
                    this.clearForms();
                }
            } catch (error) {
                console.error('Ошибка списания:', error);
                const errorMessage = error.errors && error.errors[0] ? error.errors[0].message : 'Ошибка при списании с баланса';
                this.showFormMessage(errorMessage, false, 'withdraw');
            }
        },

        clearForms() {
            this.bankTransfer.amount = null;
            this.freeReplenishment.amount = null;
            this.freeReplenishment.comment = '';
            this.withdraw.amount = null;
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