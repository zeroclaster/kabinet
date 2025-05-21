<?php
namespace Bitrix\Kabinet\taskrunner\admin;


/**
 * провайдер не будект создаваться несколько экземпляяров, поэтому он Singleton
 * нужна только одна версия провайдера
 */
class Providerrunner{
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

        $a = $GLOBALS["USER_FIELD_MANAGER"]->getUserFields('HLBLOCK_'.FULF,null,LANGUAGE_ID);
        $fields = array_keys($a);
        $fields[] = 'ID';
		
        $allowFileds = array_diff($fields,['UF_OPERATION',]);

        $HLBClass = (\KContainer::getInstance())->get(FULF_HL);
		
		$c1 = include __DIR__ . '/../config.php';
        $c2 = include __DIR__ . '/config.php';
        $config = array_merge($c1,$c2);
		
        return new \Bitrix\Kabinet\taskrunner\Runnermanager(FULF,$HLBClass,$allowFileds,$config);
    }
}
