<?php
namespace Bitrix\telegram;

use Bitrix\Kabinet\exceptions\KabinetException;
use Bitrix\Main\Loader;
use Bitrix\Main\Type\DateTime;
use Bitrix\Main\Config\Option;
use Bitrix\Main\Entity\Query;
use Bitrix\Kabinet\messanger\datamanager\LmessangerTable;

/**
 * Класс для управления рассылкой уведомлений пользователям по расписанию
 *
 * Основные функции:
 * - Отправка уведомлений в заданные периоды времени
 * - Восстановление пропущенных уведомлений при сбоях
 * - Поддержка разных режимов уведомлений для пользователей
 */
class Notificationsender
{
    // Настройки модуля
    const MODULE_ID = 'final_notifications';       // Идентификатор модуля для хранения настроек в БД
    const USERS_PER_PAGE = 100;                   // Количество пользователей для обработки за один шаг
    const MAX_RECOVERY_DAYS = 7;                  // Максимум дней для восстановления пропущенных уведомлений

    /**
     * Настройки периодов рассылки
     *
     * Формат:
     * час => [
     *   'key' => уникальный ключ,
     *   'name' => читаемое название,
     *   'start_hour' => час начала сбора сообщений,
     *   'end_hour' => час окончания периода
     * ]
     */
    const PERIODS = [
        9 => [  // Утренний период (9:00)
            'key' => 'morning',
            'name' => 'утренние',
            'start_hour' => 18,  // Собираем сообщения с 18:00 предыдущего дня
            'end_hour' => 9       // до 9:00 текущего дня
        ],
        13 => [ // Дневной период (13:00)
            'key' => 'day',
            'name' => 'дневные',
            'start_hour' => 9,   // С 9:00
            'end_hour' => 13      // до 13:00
        ],
        18 => [ // Вечерний период (18:00)
            'key' => 'evening',
            'name' => 'вечерние',
            'start_hour' => 13,  // С 13:00
            'end_hour' => 18      // до 18:00
        ]
    ];

    /**
     * Настройки пользователей
     *
     * Формат:
     * 'ID_настройки' => [часы_рассылки]
     */
    const USER_SETTINGS = [
        '36' => [9, 18],   // 2 раза в день (утро и вечер)
        '37' => [9, 13, 18] // 3-5 раз в день (утро, день, вечер)
    ];

    /** @var bool $isRecoveryMode Флаг режима восстановления пропущенных уведомлений */
    protected $isRecoveryMode = false;

    /**
     * Основной метод для запуска обработки уведомлений
     */
    public function execute()
    {
        $this->log('Запуск обработки уведомлений');

        // Получаем текущее время
        $currentHour = (int)date('G'); // Текущий час (0-23)
        $currentDate = new DateTime(); // Текущая дата/время

        // Для тестирования можно задать конкретный час
        // $currentHour = 13;

        // Обрабатываем текущий период, если он есть в настройках
        if (isset(self::PERIODS[$currentHour])) {
            $this->processPeriod($currentHour, $currentDate);
        }

        // Проверяем и восстанавливаем пропущенные периоды
        $this->checkMissedPeriods($currentDate);

        $this->log('Обработка завершена');
    }

    /**
     * Обрабатывает уведомления для указанного периода с пагинацией
     *
     * @param int $hour Час периода (9, 13, 18)
     * @param DateTime $date Текущая дата
     * @param int $page Номер страницы (для рекурсивной обработки)
     */
    protected function processPeriod(int $hour, DateTime $date, int $page = 0)
    {
        $period = self::PERIODS[$hour];
        $this->log("Обработка периода: {$period['name']}, страница {$page}");

        // Получаем порцию пользователей для обработки
        $users = $this->getUsersForPeriod($hour, $page);

        // Если пользователей нет и это не первая страница - завершаем обработку
        if (empty($users) && $page > 0) {
            return;
        }

        // Обрабатываем каждого пользователя
        foreach ($users as $user) {
            // Пропускаем если уведомление уже отправлено
            if ($this->isNotificationSent($user['ID'], $period['key'], $date)) {
                continue;
            }

            // Получаем сообщения для пользователя за период
            $messages = $this->getMessagesForUserPeriod($user, $period, $date);

            // Если есть сообщения - отправляем уведомление
            if (!empty($messages)) {
                $this->sendNotification($user, $messages, $period['name']);
                $this->markNotificationSent($user['ID'], $period['key'], $date);
            }
        }

        // Если получили полную страницу пользователей - обрабатываем следующую страницу
        if (count($users) === self::USERS_PER_PAGE) {
            $this->processPeriod($hour, $date, $page + 1);
        }
    }

    /**
     * Проверяет пропущенные периоды и восстанавливает их
     *
     * @param DateTime $currentDate Текущая дата
     */
    protected function checkMissedPeriods(DateTime $currentDate)
    {
        // Получаем время последнего запуска
        $lastRun = $this->getLastRunTime();
        $this->setLastRunTime($currentDate);

        // Если это первый запуск - пропускаем проверку
        if (!$lastRun) {
            return;
        }

        // Вычисляем сколько часов прошло с последнего запуска
        $hoursDiff = ($currentDate->getTimestamp() - $lastRun->getTimestamp()) / 3600;

        // Если прошло больше 12 часов - восстанавливаем пропущенные периоды
        if ($hoursDiff > 12) {
            $this->log("Обнаружен пропуск запусков: {$hoursDiff} часов");
            $this->isRecoveryMode = true;

            // Вычисляем сколько дней нужно восстановить (не больше MAX_RECOVERY_DAYS)
            $daysToRecover = min(floor($hoursDiff / 24), self::MAX_RECOVERY_DAYS);

            // Восстанавливаем периоды для каждого пропущенного дня
            for ($i = 1; $i <= $daysToRecover; $i++) {
                $checkDate = (clone $currentDate)->add("-{$i} days");
                $this->recoverMissedPeriods($checkDate);
            }

            $this->isRecoveryMode = false;
        }
    }

    /**
     * Восстанавливает пропущенные уведомления за указанную дату
     *
     * @param DateTime $date Дата для восстановления
     */
    protected function recoverMissedPeriods(DateTime $date)
    {
        $this->log("Восстановление пропущенных периодов за " . $date->format('Y-m-d'));

        // Обрабатываем все периоды для указанной даты
        foreach (self::PERIODS as $hour => $period) {
            // Если период еще не был обработан - обрабатываем его
            if (!$this->wasPeriodProcessed($hour, $date)) {
                $this->log("Восстановление периода: {$period['name']}");
                $this->processPeriod($hour, $date);
            }
        }
    }

    /**
     * Проверяет был ли период уже обработан
     *
     * @param int $hour Час периода
     * @param DateTime $date Дата периода
     * @return bool True если период уже обработан
     */
    protected function wasPeriodProcessed(int $hour, DateTime $date): bool
    {
        $periodKey = self::PERIODS[$hour]['key'] ?? null;
        if (!$periodKey) {
            return false;
        }

        // Получаем количество пользователей для этого периода
        $userCount = $this->getUsersCountForPeriod($hour);

        // Если нет пользователей - считаем период обработанным
        if ($userCount === 0) {
            return true;
        }

        // Получаем количество уже отправленных уведомлений за этот период
        $sentCount = HistoryTable::getCount([
            '=PERIOD' => $periodKey,
            '=PERIOD_DATE' => $date,
        ]);

        // Период обработан если отправлены все уведомления
        return $sentCount >= $userCount;
    }

    /**
     * Получает пользователей для указанного периода с пагинацией
     *
     * @param int $hour Час периода
     * @param int $page Номер страницы
     * @return array Массив пользователей
     */
    protected function getUsersForPeriod(int $hour, int $page = 0): array
    {
        $users = [];

        // Получаем ключи настроек, которые включают указанный период
        $settingKeys = array_keys(array_filter(
            self::USER_SETTINGS,
            function($periods) use ($hour) {
                return in_array($hour, $periods);
            }
        ));

        // Если нет подходящих настроек - возвращаем пустой массив
        if (empty($settingKeys)) {
            return [];
        }

        // Получаем пользователей с пагинацией
        $dbUsers = \CUser::GetList(
            $by = 'ID',         // Сортировка по ID
            $order = 'ASC',     // По возрастанию
            [                   // Фильтр:
                '!UF_EMAIL_NOTIFI' => false,       // Настройка включена
                '=UF_EMAIL_NOTIFI' => $settingKeys // Только нужные настройки
            ],
            [                   // Дополнительные параметры
                'SELECT' => ['ID', 'EMAIL', 'NAME', 'LAST_NAME', 'UF_EMAIL_NOTIFI'],
                'NAV_PARAMS' => [
                    'nPageSize' => self::USERS_PER_PAGE, // Размер страницы
                    'iNumPage' => $page + 1              // Номер страницы (начинается с 1)
                ]
            ]
        );

        // Формируем массив пользователей
        while ($user = $dbUsers->Fetch()) {
            $users[] = $user;
        }

        return $users;
    }

    /**
     * Получает количество пользователей для указанного периода
     *
     * @param int $hour Час периода
     * @return int Количество пользователей
     */
    protected function getUsersCountForPeriod(int $hour): int
    {
        // Получаем ключи настроек для этого периода
        $settingKeys = array_keys(array_filter(
            self::USER_SETTINGS,
            function($periods) use ($hour) {
                return in_array($hour, $periods);
            }
        ));

        // Запрос количества пользователей
        $dbUsers = \CUser::GetList(
            $by = 'ID',
            $order = 'ASC',
            [
                '!UF_EMAIL_NOTIFI' => false,
                '=UF_EMAIL_NOTIFI' => $settingKeys
            ],
            ['SELECT' => ['ID']] // Только ID для оптимизации
        );

        return $dbUsers->SelectedRowsCount();
    }

    /**
     * Получает сообщения для пользователя за указанный период
     *
     * @param array $user Данные пользователя
     * @param array $period Параметры периода
     * @param DateTime $date Дата
     * @return array Массив сообщений
     */
    protected function getMessagesForUserPeriod(array $user, array $period, DateTime $date): array
    {
        // Устанавливаем границы периода
        $start = (clone $date)->setTime($period['start_hour'], 0, 0);
        $end = (clone $date)->setTime($period['end_hour'], 0, 0);

        // Корректировка для периодов, переходящих через полночь
        if ($period['start_hour'] > $period['end_hour']) {
            $start->add('-1 day');
        }

        // Фильтр для выборки сообщений
        $filter = [
            '>=UF_PUBLISH_DATE' => $start, // Не раньше начала периода
            '<=UF_PUBLISH_DATE' => $end,   // Не позже конца периода
            'UF_STATUS' => \Bitrix\Kabinet\messanger\Messanger::NEW_MASSAGE, // Только новые сообщения
            ['LOGIC' => 'OR', // Логическое ИЛИ для условий
                '=UF_TARGET_USER_ID' => $user['ID'],      // Сообщения для пользователя
                '=FULFI.TASK.UF_AUTHOR_ID' => $user['ID'], // Задачи пользователя
                '=TASK.UF_AUTHOR_ID' => $user['ID'],
                '=PROJECT.UF_AUTHOR_ID' => $user['ID'],
            ]
        ];

        try {
            // Получаем сообщения из БД
            $res = LmessangerTable::getList([
                'filter' => $filter,
                'order' => ['UF_PUBLISH_DATE' => 'DESC'], // Сортировка по дате (новые сначала)
            ]);
            return $res->fetchAll();
        } catch (\Exception $e) {
            $this->log("Ошибка получения сообщений для пользователя {$user['ID']}: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Проверяет было ли уведомление уже отправлено
     *
     * @param int $userId ID пользователя
     * @param string $periodKey Ключ периода
     * @param DateTime $date Дата
     * @return bool True если уведомление уже отправлено
     */
    protected function isNotificationSent(int $userId, string $periodKey, DateTime $date): bool
    {
        $res = HistoryTable::getList([
            'filter' => [
                '=USER_ID' => $userId,
                '=PERIOD' => $periodKey,
                '=PERIOD_DATE' => $date,
            ],
            'select' => ['ID'], // Выбираем только ID для оптимизации
            'limit' => 1,       // Ограничиваем одну запись
        ]);

        return (bool)$res->fetch();
    }

    /**
     * Помечает уведомление как отправленное
     *
     * @param int $userId ID пользователя
     * @param string $periodKey Ключ периода
     * @param DateTime $date Дата
     */
    protected function markNotificationSent(int $userId, string $periodKey, DateTime $date)
    {
        HistoryTable::add([
            'USER_ID' => $userId,
            'PERIOD' => $periodKey,
            'PERIOD_DATE' => $date,
            'DATE_SENT' => new DateTime(),
            'IS_RECOVERY' => $this->isRecoveryMode ? 'Y' : 'N', // Флаг восстановленного уведомления
        ]);
    }

    /**
     * Отправляет уведомление пользователю
     *
     * @param array $user Данные пользователя
     * @param array $messages Массив сообщений
     * @param string $periodName Название периода
     */
    protected function sendNotification(array $user, array $messages, string $periodName)
    {
        // Инициализация
        $compositeTransport = new \Bitrix\telegram\notificationtransport\Compositetransport();
        $compositeTransport->addTransport(new \Bitrix\telegram\notificationtransport\Emailtransport());
        $handler = new \Bitrix\telegram\Notificationhandler($compositeTransport);
        // Добавление кастомного правила
        $handler->addRule(new \Bitrix\telegram\notificationrule\Userpreferencerule());
        // В реальной реализации здесь должна быть отправка email/telegram/etc
        foreach ($messages as $message) {
            $handler->handleMessageAdd($message['ID']);
            $this->log("Отправка уведомления пользователю {$user['ID']}: сообщение ID {$message['ID']}");
        }
    }

    /**
     * Получает время последнего запуска
     *
     * @return DateTime|null Объект DateTime или null если запуск первый
     */
    protected function getLastRunTime(): ?DateTime
    {
        $time = Option::get(self::MODULE_ID, 'last_run_time');
        return $time ? new DateTime($time) : null;
    }

    /**
     * Сохраняет время последнего запуска
     *
     * @param DateTime $time Время для сохранения
     */
    protected function setLastRunTime(DateTime $time)
    {
        Option::set(self::MODULE_ID, 'last_run_time', $time->toString());
    }

    /**
     * Записывает сообщение в лог
     *
     * @param string $message Сообщение для логирования
     */
    protected function log(string $message)
    {
        file_put_contents(
            $_SERVER['DOCUMENT_ROOT'] . '/upload/notifications.log',
            date('[Y-m-d H:i:s] ') . $message . "\n",
            FILE_APPEND
        );
    }
}