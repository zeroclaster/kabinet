<?
namespace Bitrix\kabinet\fields;

abstract class Base{
	protected $item;

	function __construct($item){

		$this->item = $item;
	}

	abstract public function viewHTML();


}
