<?php
namespace Bitrix\Kabinet\messanger\admin;


/**
 * провайдер не будект создаваться несколько экземпляяров, поэтому он Singleton
 * нужна только одна версия провайдера
 */
class Providermessanger{
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

        $c1 = include __DIR__ . '/../config.php';
        $c2 = include __DIR__ . '/config.php';
        $config = array_merge($c1,$c2);
        
        $user = $this->user;
		$HLBClass = (\KContainer::getInstance())->get(LMESSANGER_HL);
        return new \Bitrix\Kabinet\messanger\Messanger(LMESSANGER,$HLBClass,$config);
    }
}
