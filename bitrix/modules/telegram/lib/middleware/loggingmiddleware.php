<?php
namespace Bitrix\telegram\middleware;

use \Bitrix\telegram\contracts\Middlewareinterface;

class Loggingmiddleware implements Middlewareinterface
{
    public function handle(array $message, \Closure $next): void
    {
        file_put_contents(
            'bot.log',
            date('[Y-m-d H:i:s]') . " User {$message['from']['id']} ChatID {$message['chat']['id']}: {$message['text']}\n",
            FILE_APPEND
        );
        $next($message); // Передаем сообщение дальше
    }
}
