/*
NotificationChecker — класс для отслеживания уведомлений на сайте Битрикс
Класс автоматически проверяет количество уведомлений у пользователя через регулярные промежутки времени и визуально обновляет иконку колокольчика в шапке:

🟢 Красный цвет (site-red) — есть новые уведомления.
⚪ Серый цвет (site-gray) — нет уведомлений.


Пример использования:
new NotificationChecker({
    bellSelector: '.fa-bell',
    action: 'bitrix:kabinet.evn.messengerevents.getcount',
    interval: 60000,
    filter: { TYPE: 'important' },
    activeClass: 'site-red',
    inactiveClass: 'site-gray'
});

 */
class NotificationChecker {
    constructor(options) {
        this.bellSelector = options.bellSelector || '.rd-navbar-panel-cell .fa-bell';
        this.action = options.action || 'bitrix:kabinet.evn.messengerevents.getcount';
        this.interval = options.interval || 60000; // 60 секунд
        this.classes = {
            active: options.activeClass || 'site-red',
            inactive: options.inactiveClass || 'site-white'
        };

        this.filter = options.filter;

        this.bellElement = document.querySelector(this.bellSelector);
        if (!this.bellElement) {
            console.error('Элемент колокольчика не найден по селектору:', this.bellSelector);
            return;
        }

        // Запускаем проверку
        this.start();
    }

    check() {
        const filter = this.filter;
        var form_data = new FormData();
        Object.entries(filter).forEach(([key, value]) => {
            form_data.append("FILTER-" + key, value);
        });
        BX.ajax.runAction(this.action, {
            data : form_data,
            //processData: false,
            //preparePost: false
        })
            .then((response) => {
                const count = parseInt(response.data.count) || 0;

                if (count > 0) {
                    BX.removeClass(this.bellElement, this.classes.inactive);
                    BX.addClass(this.bellElement, this.classes.active);
                } else {
                    BX.removeClass(this.bellElement, this.classes.active);
                    BX.addClass(this.bellElement, this.classes.inactive);
                }
            })
            .catch((error) => {
                console.error('Ошибка при проверке уведомлений:', error);
            });
    }

    start() {
        // Проверяем сразу при старте
        this.check();

        // Затем по таймеру
        setInterval(() => {
            this.check();
        }, this.interval);
    }
}