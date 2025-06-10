<?php
namespace Bitrix\telegram;

use Bitrix\Main\DI\ServiceLocator;
use Bitrix\Main\SystemException;
use Bitrix\Main\Mail\Event;
use Bitrix\Kabinet\Messanger\Messanger;
use Bitrix\telegram\contracts\Notificationtransportinterface;
use Bitrix\telegram\contracts\Notificationruleinterface;
use Bitrix\telegram\Exceptions\NotificationException;
use Bitrix\telegram\exceptions\TelegramException;

class Notificationhandler
{
    private $rulesChain;
    private $transport;

    public function __construct(Notificationtransportinterface $transport)
    {
        $this->transport = $transport;
        $this->initDefaultRules();
    }

    private function initDefaultRules() {
        $this->rulesChain = new \Bitrix\telegram\notificationrule\Validateinput();
        /*
        $this->rulesChain
            ->setNext(new Validateinput())
            ->setNext(new MessageTypeRule());
        */
    }

    public function addRule(Notificationruleinterface $rule): void {
        $rule->setNext($this->rulesChain);
        $this->rulesChain = $rule;
    }

    public function handleMessageAdd(int $message_id) {
        try {
            $messageData = $this->getMessageData($message_id);
            $recipientData = $this->getRecipientData($messageData);
            $messageType = $this->determineMessageType($messageData);

            if (!$this->rulesChain->shouldSend($messageData, $recipientData)) {
                $this->logSkippedNotification($messageData, $recipientData);
                return;
            }

            $message = $this->prepareMessageContent(
                $messageData,
                $messageType['isForUser'],
                $recipientData
            );

            $FULL_MESSAGE = $this->buildMessageWithContext(
                $message,
                $messageType['isSystem'],
                $recipientData
            );

            $this->transport->send($recipientData, $messageData,$FULL_MESSAGE);


        } catch (NotificationException $e) {
            $this->logError($e->getMessage());
        }
    }

    /**
     * Получает данные получателя (добавлен email)
     */
    private function getRecipientData(array $fields)
    {
        $recipientData = null;

        if ($fields['UF_QUEUE_ID'] > 0) {
            $recipientData = $this->getQueueRecipientData($fields['UF_QUEUE_ID']);
        } elseif ($fields['UF_TASK_ID'] > 0) {
            $recipientData = $this->getTaskRecipientData($fields['UF_TASK_ID']);
        } elseif ($fields['UF_PROJECT_ID'] > 0) {
            $recipientData = $this->getProjectRecipientData($fields['UF_PROJECT_ID']);
        } elseif ($fields['UF_TARGET_USER_ID'] > 0) {
            $recipientData = $this->getUserData($fields['UF_TARGET_USER_ID']);
        }

        return $recipientData;
    }

    /**
     * Получает данные пользователя (включая email)
     */
    private function getUserData(int $userId)
    {
        $user = \Bitrix\Main\UserTable::getList([
            'select' => ['*','UF_TELEGRAM_ID'],
            'filter' => ['=ID' => $userId],
            'limit' => 1
        ])->fetchObject();
        return $user;
    }

    /**
     * Определяет тип сообщения
     */
    private function determineMessageType(array $fields): array
    {
        return [
            'isSystem' => $fields['UF_TYPE'] == Messanger::SYSTEM_MESSAGE,
            'isForUser' => $fields['UF_TYPE'] == Messanger::USER_MESSAGE
        ];
    }

    /**
     * Получает данные сообщения
     */
    private function getMessageData(int $id)
    {
        return \Bitrix\Kabinet\messanger\datamanager\LmessangerTable::getById($id)->fetch();
    }

    private function getQueueRecipientData(int $queueId)
    {

        $Fulfillment = \Bitrix\Kabinet\taskrunner\datamanager\FulfillmentTable::getList([
            'select' => [
                '*',
                'TASK.*',
                'TASK.PROJECT.*',
                'TASK.USER.*',
                'TASK.USER.UF_TELEGRAM_ID',
                'TASK.PROJECT.USER.*',
                'TASK.PROJECT.USER.UF_TELEGRAM_ID',
            ],
            'filter' => ['ID' => $queueId],
            'limit' => 1
        ])->fetchObject();

        return $Fulfillment ?: null;
    }

    private function getTaskRecipientData(int $taskId)
    {
        return \Bitrix\Kabinet\task\datamanager\TaskTable::getList([
            'select' => [
                '*',
                'PROJECT.*',
                'USER.*',
                'USER.UF_TELEGRAM_ID',
            ],
            'filter' => ['ID' => $taskId],
            'limit' => 1
        ])->fetchObject() ?: null;
    }

    private function getProjectRecipientData(int $projectId)
    {
        return \Bitrix\Kabinet\project\datamanager\ProjectsTable::getList([
            'select' => [
                '*',
                'USER.*',
                'USER.UF_TELEGRAM_ID',
            ],
            'filter' => ['ID' => $projectId],
            'limit' => 1
        ])->fetchObject() ?: null;
    }

    /**
     * Подготавливает текст сообщения
     */
    private function prepareMessageContent($messageData, bool $isForUser, $recipientData): string
    {
        $cleaned = trim(strip_tags($messageData['UF_MESSAGE_TEXT']));
        $message = mb_substr($cleaned, 0, 4096);

        if ($isForUser){
            $message_auther = $this->getUserData($messageData['UF_AUTHOR_ID']);
        }

        if ($this->getRecipientSource($recipientData) == 'FulfillmentTable') $userParams = $recipientData->get('TASK')->get("USER");
        if ($this->getRecipientSource($recipientData) == 'TaskTable') $userParams = $recipientData->get("USER");
        if ($this->getRecipientSource($recipientData) == 'ProjectsTable') $userParams = $recipientData->get("USER");
        if ($this->getRecipientSource($recipientData) == 'UserTable') $userParams = $recipientData;

        $fullUserName = current(array_filter([
            trim(implode(" ", [$message_auther['LAST_NAME'], $message_auther['NAME'], $message_auther['SECOND_NAME']])),
            $message_auther['LOGIN']
        ]));

            return $isForUser
            ? "<p>{$fullUserName}</p><p>{$message}</p>"
            : $message;
    }

    /**
     * Формирует полное сообщение с контекстом
     */
    private function buildMessageWithContext(string $message, bool $isSystem, $recipientData): string
    {
        if (!$isSystem) {
            return $message;
        }

        $parts = [];

        $project = null;
        $task = null;

        if ($this->getRecipientSource($recipientData) == 'FulfillmentTable'){
            $project = $recipientData->get('TASK')->get('PROJECT');
            $task = $recipientData->get('TASK');
        }

        if ($this->getRecipientSource($recipientData) == 'TaskTable'){
            $task = $recipientData;
            $project = $recipientData->get('PROJECT');
        }
        if ($this->getRecipientSource($recipientData) == 'ProjectsTable'){
            $project = $recipientData;
        }

        if ($project) {
            $parts[] = "Проект «{$project['UF_NAME']}» #{$project['UF_EXT_KEY']}";
        }

        if ($task) {
            $taskLink = "https://kupi-otziv.ru/kabinet/projects/reports/?t={$task['ID']}";
            $parts[] = "Задача <a href=\"{$taskLink}\">«{$task['UF_NAME']}» #{$task['UF_EXT_KEY']}</a>";
        }

        return implode(', ', $parts) . $message;
    }

    private function getRecipientSource($recipientData): string
    {
        if ($recipientData instanceof \Bitrix\Kabinet\taskrunner\datamanager\Fulfillment) {
            return 'FulfillmentTable';
        } elseif ($recipientData instanceof \Bitrix\Kabinet\task\datamanager\Task) {
            return 'TaskTable';
        } elseif ($recipientData instanceof \Bitrix\Kabinet\project\datamanager\Project) {
            return 'ProjectsTable';
        } elseif ($recipientData instanceof \Bitrix\Main\User) {
            return 'UserTable';
        }
        return 'unknown';
    }

    /**
     * Логирование ошибок
     */
    private function logError(string $message): void
    {
        AddMessage2Log($message, 'telegram_error');
    }
}