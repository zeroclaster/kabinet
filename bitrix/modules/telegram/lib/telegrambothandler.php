<?php
namespace Bitrix\telegram;

use \Bitrix\telegram\contracts\Middlewareinterface,
    \Bitrix\telegram\exceptions\TelegramException;
use Bitrix\Main\UserTable;

class Telegrambothandler
{
    private string $botToken;
    private array $middlewares = [];

    public function __construct()
    {
        $botToken = \COption::GetOptionString("telegram", "bottoken", "");

        if (empty($botToken)) {
            throw new TelegramException('Bot token cannot be empty');
        }
        $this->botToken = $botToken;
    }

    public function addMiddleware(Middlewareinterface $middleware): void
    {
        $this->middlewares[] = $middleware;
    }

    public function handleRequest(): void
    {
        $input = file_get_contents("php://input");


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

    protected function processMessage(array $message): void
    {
        $pipeline = array_reduce(
            array_reverse($this->middlewares),
            fn($next, $middleware) => fn($msg) => $middleware->handle($msg, $next),
            fn($msg) => $this->executeCommand($msg)
        );

        $pipeline($message);
    }

    private function executeCommand(array $message): void
    {
        $command = CommandFactory::create($message['text'] ?? '', $this);
        if (!$command) {
            throw new TelegramException('Unknown command: ' . ($message['text'] ?? ''));
        }
        $command->execute($message);
    }

    /**
     * Отправляет сообщение пользователю через Telegram
     * @param int $userId - ID пользователя в Bitrix
     * @param string $message - Текст сообщения
     * @throws TelegramException
     */
    public function sendMessageToUserTelegram(int $userId, string $message): void
    {
        // Получаем данные пользователя
        $user = UserTable::getList([
            'filter' => ['=ID' => $userId],
            'select' => ['UF_TELEGRAM_ID', 'UF_TELEGRAM_CHAT_ID']
        ])->fetch();

        if (!$user || empty($user['UF_TELEGRAM_ID'])) {
            throw new TelegramException("Пользователь не привязан к Telegram");
        }

        $chatId = $user['UF_TELEGRAM_CHAT_ID'] ?? $user['UF_TELEGRAM_ID'];

        // Логирование перед отправкой
        $this->log("Sending to user {$userId} (chat: {$chatId}): {$message}");

        // Вызываем родительский метод отправки
        $this->sendMessage($chatId, $message);
    }

    // Функция для преобразования HTML в Telegram-совместимый формат
    public function convertHtmlToTelegram($html) {
        $html = str_replace(['<p>', '</p>'], "\n", $html);
        $html = strip_tags($html, '<b><i><u><s><a><code><pre>');
        return trim($html);
    }

    public function sendMessage(int $chatId, string $text): void
    {
        $url = "https://api.telegram.org/bot" . $this->botToken . "/sendMessage";

        $params = [
            'chat_id' => $chatId,
            'text' => $this->convertHtmlToTelegram($text),
            'parse_mode' => 'HTML'
        ];

        $ch = curl_init($url);
        if ($ch === false) {
            throw new TelegramException('Failed to initialize cURL');
        }

        curl_setopt_array($ch, [
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => $params,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT => 10,
            CURLOPT_CONNECTTIMEOUT => 5
        ]);

        $response = curl_exec($ch);
        if ($response === false) {
            $error = curl_error($ch);
            curl_close($ch);
            throw new TelegramException('cURL error: ' . $error);
        }

        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($httpCode !== 200) {
            $responseData = json_decode($response, true);
            $errorDescription = $responseData['description'] ?? 'No description provided';
            throw new TelegramException('Telegram API error. HTTP code: ' . $httpCode. ', Description: ' . $errorDescription);
        }
    }

    public function getUserByTelegramId(int $telegramId): ?array
    {
        if (!\CModule::IncludeModule("main")) {
            throw new TelegramException('Main module not available');
        }

        $rsUser = \CUser::GetList(
            "ID",
            "ASC",
            ['UF_TELEGRAM_ID' => $telegramId],
            ['SELECT' => ['ID', 'NAME', 'EMAIL']]
        );

        return $rsUser->Fetch();
    }

    public function generateAuthLink(int $telegramUserId): string
    {
        // Находим пользователя Bitrix по telegram_id
        $user = $this->getUserByTelegramId($telegramUserId);

        if (!$user) {
            throw new TelegramException('Пользователь не найден');
        }

        $token = bin2hex(random_bytes(32));
        $expire = time() + 1800; // 30 минут

        // Сохраняем в БД (пример для MySQL)
        \Bitrix\Main\Application::getConnection()->queryExecute(
            "INSERT INTO b_auth_tokens
         (user_id, token, expire_time) 
         VALUES ({$user['ID']}, '{$token}', {$expire})"
        );

        return "?token={$token}";
    }

    public function log(string $message): void
    {
        file_put_contents(
            $_SERVER['DOCUMENT_ROOT'].'/telegram_bot.log',
            date('[Y-m-d H:i:s] ').$message."\n",
            FILE_APPEND
        );
    }
}
