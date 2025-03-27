<?php
namespace Bitrix\Kabinet\task;


/**
 * провайдер не будект создаваться несколько экземпляяров, поэтому он Singleton
 * нужна только одна версия провайдера
 */
class Providertask{
    private static $instance;
    protected $user = null;
    protected $object = null;

    protected function __construct() {
        $this->user = (\KContainer::getInstance())->get('user');
    }

    protected function __clone() { }

    public function __wakeup()
    {
        throw new SystemException("Cannot unserialize a singleton.");
    }

    public static function getInstance()
    {
        $cls = static::class;
        if (!isset(self::$instance)) {
            self::$instance = new static();
        }

        return self::$instance;
    }

    public function build(){

        if ($this->object) return $this->object;

        $user = $this->user;
		$HLBClass = (\KContainer::getInstance())->get(TASK_HL);

		if ($GLOBALS['task_test'] == 'y') return new Taskmanagertest(TASK,$HLBClass);

        return new Taskmanagercache(TASK,$HLBClass);
    }
}
