<?
namespace Bitrix\telegram\exceptions;

use \Bitrix\Main\SystemException;

/**
 * Exception is thrown Telegram.
 */
class TelegramException extends SystemException
{
    public function __construct($message = "", \Exception $previous = null)
    {
        parent::__construct($message, 800, '', 0, $previous);
    }
}

class TelegramAuthException extends TelegramException
{
    private ?int $chatId;

    public function __construct(string $message, ?int $chatId = null)
    {
        parent::__construct($message);
        $this->chatId = $chatId;
    }

    public function getChatId(): ?int
    {
        return $this->chatId;
    }
}

class TelegramMiddlewareException extends SystemException
{
    public function __construct($message = "", \Exception $previous = null)
    {
        parent::__construct($message, 800, '', 0, $previous);
    }
}