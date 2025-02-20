<?
namespace Bitrix\Kabinet\container;

use Bitrix\Highloadblock\HighloadBlockTable as HlTable;

class Hlbuilder{
	public $entity_;
	
	public function __construct() {			
	}
	
	public function set($entity_id){
		$hlblock   = HlTable::getById((int)$entity_id)->fetch();
		$entity   = HlTable::compileEntity( $hlblock ); //генерация класса
		$MyentityClass = $entity->getDataClass();

		$this->entity_[$entity_id] = $MyentityClass;		
	}
	
	public function get($entity_id){
		if (empty($this->entity_[$entity_id]))
			$this->set($entity_id);
		
		return $this->entity_[$entity_id];
	}

}
