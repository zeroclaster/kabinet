<?
namespace Bitrix\kabinet\fields;

class Stringfield extends Base{

	public function viewHTML(){

		return $this->item->get('UF_VALUE_STR');
	}
	

}