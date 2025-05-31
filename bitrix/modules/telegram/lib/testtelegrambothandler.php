<?php
namespace Bitrix\telegram;

use \Bitrix\telegram\contracts\Middlewareinterface,
    \Bitrix\telegram\exceptions\TelegramException;

class Testtelegrambothandler extends Telegrambothandler
{
    public function handleRequest(): void
    {
        // Читаем как входящий запрос
        $input = file_get_contents('/var/www/kupi_otziv_r_usr/data/www/kupi-otziv.ru/test_webhook.json');


        if ($input === false) {
            throw new TelegramException('Failed to read input data');
        }

        $data = json_decode($input, true);
        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new TelegramException('JSON decode error: ' . json_last_error_msg());
        }

        if (empty($data) || !isset($data['update_id'])) {
            throw new TelegramException('Invalid Telegram request format');
        }

        if (isset($data['message']['text'])) {
            $this->processMessage($data['message']);
        }
    }

    public function sendMessage(int $chatId, string $text): void
    {
        $message = "Отправка сообщения в Telegram (chat_id: {$chatId}):\n{$text}\n\n";

        // Тестовая реализация - выводим в консоль
        //echo $message;

        $this->log($message);
    }

}
