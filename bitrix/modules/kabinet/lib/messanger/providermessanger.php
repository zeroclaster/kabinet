<?php
namespace Bitrix\Kabinet\messanger;

use \Bitrix\Main\SystemException;

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

        $user = $this->user;
		$HLBClass = (\KContainer::getInstance())->get(LMESSANGER_HL);

		$config = include __DIR__ . '/config.php';

        AddEventHandler("", "\Lmessanger::OnBeforeUpdate", function($id,$fields,$object,$oldData){

            $UF_STATUS = $oldData->get('UF_STATUS');
            if ($UF_STATUS != Messanger::NEW_MASSAGE) throw new SystemException("Невозможно выполнить команду. Нехватает прав.");

        });


        return new Messanger(LMESSANGER,$HLBClass,$config);
    }
}
