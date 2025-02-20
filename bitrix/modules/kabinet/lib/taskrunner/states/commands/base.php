<?php
namespace Bitrix\Kabinet\taskrunner\states\commands;

use Bitrix\Portal\minterface,
    Bitrix\Main\Error,
    Bitrix\Portal\filters;

abstract class Base{
	public $Object;

    public function __construct()
    {
    }

    public function execute(array $params = []){

    }
	
    public function setObject($Object){
        $this->Object = $Object;
    }

    public function getObject(){
       return $this->Object;
    }	
}