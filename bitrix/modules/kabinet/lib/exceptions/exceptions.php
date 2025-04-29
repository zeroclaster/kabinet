<?
namespace Bitrix\Kabinet\exceptions;

use \Bitrix\Main\SystemException;

/**
 * Exception is thrown Task.
 */
class TaskException extends SystemException
{
    public function __construct($message = "", \Exception $previous = null)
    {
        parent::__construct($message, 700, '', 0, $previous);
    }
}
/**
 * Exception is thrown Project.
 */
class ProjectException extends SystemException
{
    public function __construct($message = "", \Exception $previous = null)
    {
        parent::__construct($message, 700, '', 0, $previous);
    }
}
/**
 * Exception is thrown Fulfillment.
 */
class FulfiException extends SystemException
{
    public function __construct($message = "", \Exception $previous = null)
    {
        parent::__construct($message, 700, '', 0, $previous);
    }
}
/**
 * Exception is thrown Fulfillment.
 */
class MessangerException extends SystemException
{
    public function __construct($message = "", \Exception $previous = null)
    {
        parent::__construct($message, 700, '', 0, $previous);
    }
}
/**
 * Exception is thrown Billing.
 */
class BillingException extends SystemException
{
    public function __construct($message = "", \Exception $previous = null)
    {
        parent::__construct($message, 700, '', 0, $previous);
    }
}
/**
 * Exception is thrown Contract.
 */
class ContractException extends SystemException
{
    public function __construct($message = "", \Exception $previous = null)
    {
        parent::__construct($message, 700, '', 0, $previous);
    }
}
/**
 * Exception is thrown Contract.
 */
class TestException extends SystemException
{
    public function __construct($message = "", \Exception $previous = null)
    {
        parent::__construct($message, 750, '', 0, $previous);
    }
}