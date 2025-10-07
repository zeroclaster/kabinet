<?php
namespace Bitrix\telegram;

use Bitrix\Kabinet\billing\datamanager\BillinghistoryTable;
use Bitrix\Main\Type\DateTime;
use Bitrix\Main\Entity\Query;

/**
 * Класс для управления рассылкой уведомлений об операциях биллинга
 * Наследует базовый функционал от NotificationSender
 */
class BillingNotificationSender extends NotificationSender
{
    const MODULE_ID = 'billing_notifications'; // Идентификатор модуля для настроек биллинга

    /**
     * Основной метод для запуска обработки уведомлений биллинга
     */
    public function execute()
    {
        $this->log('Запуск обработки уведомлений биллинга');

        // Получаем текущее время
        $currentHour = (int)date('G');
        $currentDate = new DateTime();

        // Для тестирования можно задать конкретный час
         //$currentHour = 18;

        // Обрабатываем текущий период, если он есть в настройках
        if (isset(self::PERIODS[$currentHour])) {
            $this->processPeriod($currentHour, $currentDate);
        }

        // Проверяем и восстанавливаем пропущенные периоды
        $this->checkMissedPeriods($currentDate);

        $this->log('Обработка уведомлений биллинга завершена');
    }

    /**
     * Получает операции биллинга для пользователя за указанный период
     *
     * @param array $user Данные пользователя
     * @param array $period Параметры периода
     * @param DateTime $date Дата
     * @return array Массив операций биллинга
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

        // Фильтр для выборки операций биллинга
        $filter = [
            '>=UF_PUBLISH_DATE' => $start, // Не раньше начала периода
            '<=UF_PUBLISH_DATE' => $end,   // Не позже конца периода
            '=UF_AUTHOR_ID' => $user['ID'], // Операции конкретного пользователя
            '=UF_ACTIVE' => true,           // Только активные записи
        ];


        try {
            // Получаем операции биллинга из БД
            $res = BillinghistoryTable::getList([
                'filter' => $filter,
                'order' => ['UF_PUBLISH_DATE' => 'DESC'], // Сортировка по дате (новые сначала)
            ]);

            return $res->fetchAll();
        } catch (\Exception $e) {
            $this->log("Ошибка получения операций биллинга для пользователя {$user['ID']}: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Отправляет уведомление об операциях биллинга пользователю
     *
     * @param array $user Данные пользователя
     * @param array $billingOperations Массив операций биллинга
     * @param string $periodName Название периода
     */
    protected function sendNotification(array $user, array $billingOperations, string $periodName)
    {
        if (empty($billingOperations)) {
            return;
        }

        $this->log("Подготовка уведомления биллинга для пользователя {$user['ID']}: " . count($billingOperations) . " операций");

        // Создаем обработчик для уведомлений биллинга
        $billingHandler = new Billingnotificationhandler();

        // Отправляем уведомление для каждой операции биллинга
        foreach ($billingOperations as $operation) {
            try {
                $billingHandler->handleBillingOperation($operation['ID']);
                $this->log("Уведомление биллинга отправлено пользователю {$user['ID']}: операция ID {$operation['ID']}");
            } catch (\Exception $e) {
                $this->log("Ошибка отправки уведомления биллинга для операции {$operation['ID']}: " . $e->getMessage());
            }
        }

        // Также можно отправить сводное уведомление по email
        //$this->sendBillingSummaryEmail($user, $billingOperations, $periodName);
    }

    /**
     * Отправляет сводное email-уведомление об операциях биллинга
     *
     * @param array $user Данные пользователя
     * @param array $billingOperations Массив операций биллинга
     * @param string $periodName Название периода
     */
    protected function sendBillingSummaryEmail(array $user, array $billingOperations, string $periodName)
    {
        if (empty($user['EMAIL'])) {
            $this->log("Не удалось отправить сводное уведомление: email пользователя {$user['ID']} не указан");
            return;
        }

        try {
            // Подготавливаем данные для email
            $operationsData = [];
            $totalAmount = 0;

            foreach ($billingOperations as $operation) {
                $amount = (float)$operation['UF_VALUE'];
                $operationsData[] = [
                    'DATE' => $operation['UF_PUBLISH_DATE']->format('d.m.Y H:i'),
                    'OPERATION' => $operation['UF_OPERATION'],
                    'AMOUNT' => $amount,
                    'PROJECT' => $operation['UF_PROJECT'] ?? 'Не указан'
                ];

                // Суммируем общую сумму (для операций пополнения - положительные, для списаний - отрицательные)
                if (strpos($operation['UF_OPERATION'], 'пополнение') !== false ||
                    strpos($operation['UF_OPERATION'], 'начисление') !== false) {
                    $totalAmount += $amount;
                } else {
                    $totalAmount -= $amount;
                }
            }

            /*
            // Отправляем email через почтовые события Bitrix
            $result = \Bitrix\Main\Mail\Event::send([
                'EVENT_NAME' => 'BILLING_SUMMARY_NOTIFICATION',
                'LID' => SITE_ID,
                'C_FIELDS' => [
                    'EMAIL' => $user['EMAIL'],
                    'USER_NAME' => $user['NAME'] . ' ' . $user['LAST_NAME'],
                    'PERIOD_NAME' => $periodName,
                    'OPERATIONS_COUNT' => count($billingOperations),
                    'TOTAL_AMOUNT' => $totalAmount,
                    'OPERATIONS_DATA' => $operationsData,
                    'SUMMARY_DATE' => date('d.m.Y H:i')
                ],
                "DUPLICATE" => "N"
            ]);

                        if ($result->isSuccess()) {
                $this->log("Сводное email-уведомление биллинга отправлено на {$user['EMAIL']}");
            } else {
                $this->log("Ошибка отправки сводного email-уведомления: " . implode(', ', $result->getErrorMessages()));
            }
            */

            AddMessage2Log(print_r([
                'EMAIL' => $user['EMAIL'],
                'USER_NAME' => $user['NAME'] . ' ' . $user['LAST_NAME'],
                'PERIOD_NAME' => $periodName,
                'OPERATIONS_COUNT' => count($billingOperations),
                'TOTAL_AMOUNT' => $totalAmount,
                'OPERATIONS_DATA' => $operationsData,
                'SUMMARY_DATE' => date('d.m.Y H:i')
            ],true), "my_module_id");


        } catch (\Exception $e) {
            $this->log("Ошибка при подготовке сводного уведомления биллинга: " . $e->getMessage());
        }
    }

    protected function isNotificationSent(int $userId, string $periodKey, DateTime $date, string $type = 'BILLING'): bool
    {
        return parent::isNotificationSent($userId, $periodKey, $date, $type);
    }

    protected function markNotificationSent(int $userId, string $periodKey, DateTime $date, string $type = 'BILLING')
    {
        parent::markNotificationSent($userId, $periodKey, $date, $type);
    }

    /**
     * Получает время последнего запуска для биллинга
     *
     * @return DateTime|null Объект DateTime или null если запуск первый
     */
    protected function getLastRunTime(): ?DateTime
    {
        $time = \Bitrix\Main\Config\Option::get(self::MODULE_ID, 'last_run_time');
        return $time ? new DateTime($time) : null;
    }

    /**
     * Сохраняет время последнего запуска для биллинга
     *
     * @param DateTime $time Время для сохранения
     */
    protected function setLastRunTime(DateTime $time)
    {
        \Bitrix\Main\Config\Option::set(self::MODULE_ID, 'last_run_time', $time->toString());
    }

    /**
     * Записывает сообщение в лог биллинга
     *
     * @param string $message Сообщение для логирования
     */
    protected function log(string $message)
    {
        file_put_contents(
            $_SERVER['DOCUMENT_ROOT'] . '/upload/billing_notifications.log',
            date('[Y-m-d H:i:s] ') . $message . "\n",
            FILE_APPEND
        );
    }
}