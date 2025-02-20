<?php
namespace Bitrix\Kabinet\billing;

use \Bitrix\Main\SystemException;

/**
 * провайдер не будект создаваться несколько экземпляяров, поэтому он Singleton
 * нужна только одна версия провайдера
 */
class providerbilling{
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
		$HLBClass = (\KContainer::getInstance())->get(BILLING_HL);

        AddEventHandler("", "\Billing::OnBeforeUpdate", function($id,$fields,$object,$oldData){
            //throw new SystemException("Невозможно выполнить команду. Нехватает прав.");
        });
        AddEventHandler("", "\Billing::OnBeforeDelete", function($id){
            throw new SystemException("Невозможно выполнить команду. Нехватает прав.");
        });
        $config = include __DIR__ . '/config.php';
        return new Billing(BILLING,$HLBClass,$config);
    }
}
