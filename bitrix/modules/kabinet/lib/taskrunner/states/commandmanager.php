<?php
namespace Bitrix\Kabinet\taskrunner\states;

use Bitrix\Main\SystemException,
    Bitrix\Main\Entity,
    Bitrix\Main\Event;

class Commandmanager{
    protected $command = [];
    private $container;

    public function __construct($container)
    {
        $this->container = $container;
    }

    public function setCommand(string $name, \Bitrix\Kabinet\taskrunner\states\contracts\Icommand $onCommand){
        $this->command[$name] = $onCommand;
        $onCommand->setObject($this->container);
    }

    public function executeCommand(string $name,array $params = []){

		if (!$this->command[$name])	return false;

        return $this->command[$name]->execute($params);        
    }
}