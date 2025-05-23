<?php
namespace Bitrix\Kabinet\container;

use \Bitrix\Main\SystemException,
    \Bitrix\Kabinet\Exceptions\KabinetException,
    \Bitrix\Kabinet\Exceptions\TestException;

abstract class Base{
    protected $HB_ID = 0;
    protected $HLBCClass;
    public $requiredField;

    public function __construct($HLBCClass)
    {
        $hlblock = \Bitrix\Highloadblock\HighloadBlockTable::getList([
            'filter' => ['=TABLE_NAME' => $HLBCClass::getTableName()]
        ])->fetch();

        $this->HB_ID = $hlblock['ID']; // Получим ID
		$this->HLBCClass = $HLBCClass;
    }

    /**
     * Извлекает оригинальные поля HL-блока из массива данных
     *
     * @param array $fields Входной массив данных (обычно $_POST или $_REQUEST)
     * @return array Ассоциативный массив только с полями текущего HL-блока
     */
    public function retrieveOriginalFields(array $fields): array
    {
        $result = [];
        $prefix = "HLBLOCK_{$this->HB_ID}_";

        foreach ($fields as $field => $value) {
            // Пропускаем поля не из этого HL-блока
            if (strpos($field, $prefix) !== 0) {
                continue;
            }

            // Удаляем префикс и проверяем что поле начинается с UF_
            $cleanField = substr($field, strlen($prefix));
            if (strpos($cleanField, 'UF_') === 0) {
                $result[$cleanField] = $value;
            }
        }

        return $result;
    }
	
	public function retrieveAdditionalsFields(array $fields,int $HB_ID=0){
		if (!$HB_ID) $HB_ID = $this->HB_ID;
        $ret = array();

        foreach($fields as $f => $v){
            // НЕ ПРИНАДЛЕЖИТ ОБЪЕКТУ
            if  (strpos($f,"HLBLOCK_".$HB_ID) === false) continue;

            $f = str_replace("HLBLOCK_".$HB_ID."_","",$f);

                $ret[$f] = $v;

        }

        return $ret;
    }

    public function getHLBClass(){
        return $this->HLBCClass;
    }

    public function sortFieldMAx(){
        $max_sort = 10;

        $user = \Bitrix\Main\DI\ServiceLocator::getInstance()->get('user');
        $userID = $user->get('ID');

        $HLBClass = $this->getHLBClass();
        $executeQuery = $HLBClass::query()
            ->addSelect(new \Bitrix\Main\Entity\ExpressionField('SORT', 'MAX(%s)', array('UF_SORT')))
        ->addFilter('=UF_AUTHOR_ID' ,$userID);

        //var_dump($executeQuery->getQuery());
        $item_ = $executeQuery->exec()->fetchAll();
        unset($executeQuery); // clear Memory
        //echo \Bitrix\Main\Entity\Query::getLastQuery();

        if (count($item_) > 0){
            $max_sort = $item_[0]['SORT'];
            $max_sort = $max_sort + 10;
        }

        return $max_sort;
    }

    protected function editDefault($fields){
        global $USER;

        // пользователя изменяющий истенный
        $fields['UF_USER_EDIT_ID'] = $USER->GetID();
        $fields['UF_PUBLISH_DATE'] = new \Bitrix\Main\Type\DateTime();

        return $fields;
    }

    protected function addDefault($fields){
        global $USER;
        $HLBClass = $this->getHLBClass();

        $fields['UF_PUBLISH_DATE'] = new \Bitrix\Main\Type\DateTime();
        $fields['UF_ACTIVE'] = 1;

        $user = \Bitrix\Main\DI\ServiceLocator::getInstance()->get('user');
        $fields['UF_AUTHOR_ID'] = $user->get('ID');

        // пользователя изменяющий настоящий
        $fields['UF_USER_EDIT_ID'] = $USER->GetID();

        // пока не используется
        $fields['UF_EXT_KEY'] = '';

        // Если есть поле UF_SORT пытаемся его добавить
        if ($HLBClass::getEntity()->hasField('UF_SORT')) $fields['UF_SORT'] = $this->sortFieldMAx();

        return $fields;
    }

    public function siftFields($fields,int $HLBLOCK_ID = 0){
		if (!$HLBLOCK_ID) $HLBLOCK_ID = $this->HB_ID;
        $hl_fields = $GLOBALS["USER_FIELD_MANAGER"]->getUserFields('HLBLOCK_'.$HLBLOCK_ID,null,LANGUAGE_ID);

        $addFields = [];
        $colFldNAme = array_column($hl_fields, 'FIELD_NAME');
        foreach ($fields as $field=>$value) {
            $key = array_search(
                str_replace(['_DELETE','_DOUBLE'],['',''],$field)
                , $colFldNAme);
            if ($key !== false){
                $addFields[$field] = $value;
            }
        }

        return $addFields;
    }

    public function checkFields($fields,int $HLBLOCK_ID = 0){
		if (!$HLBLOCK_ID) $HLBLOCK_ID = $this->HB_ID;
        $hl_fields = $GLOBALS["USER_FIELD_MANAGER"]->getUserFields('HLBLOCK_'.$HLBLOCK_ID,null,LANGUAGE_ID);

        $this->requiredField = '';
        $check = [];
        foreach ($hl_fields as $field=>$hfield) {

            if($hfield['MANDATORY'] == 'Y' && empty($fields[$field])){
                $this->requiredField = $hfield["EDIT_FORM_LABEL"];
                break;
            }

            $value = isset($fields[$field])? $fields[$field]: '';

            if (!isset($hfield["SETTINGS"]["REGEXP"]) || $hfield["SETTINGS"]["REGEXP"] == '') continue;
            if (!preg_match($hfield["SETTINGS"]["REGEXP"], $value, $matches) && $matches[0] == $value) {
                $this->requiredField = $hfield["EDIT_FORM_LABEL"];
                break;
            }
        }

        if ($this->requiredField) return false;
        return true;
    }
	
    protected function RestrictForm(){

        $HLBClass = $this->getHLBClass();

        if (
            (intval($this->RESTRICT_TIME) > 0) &&
            ($HLBClass::getEntity()->hasField('UF_AUTHOR_ID'))
        ){

            $executeQuery = $HLBClass::query()->addSelect('*');

            $DC2 = time();
            $DC1 = $DC2 - intval($this->RESTRICT_TIME);

			$user = \Bitrix\Main\DI\ServiceLocator::getInstance()->get('user');
            $arFilter = array("UF_AUTHOR_ID" => $user->get('ID'));

            $arFilter = array_merge($arFilter, array(
                ">=UF_PUBLISH_DATE" => ConvertTimeStamp($DC1, "FULL"),
                "<UF_PUBLISH_DATE" => ConvertTimeStamp($DC2, "FULL"),
            ));

            $executeQuery->setFilter($arFilter);
            $executeQuery->setLimit(1);

            $item = $executeQuery->exec()->fetch();
            unset($executeQuery); // clear Memory

			// for debug!!
            //echo \Bitrix\Main\Entity\Query::getLastQuery();

            return !$item;
        }

        return true;
    }

    public function isnotUserElement(int $id){
        global $USER;

        $SUPER_EDITOR = MANAGER;
        $USER_ID = $USER->GetID();;
        if (array_intersect(array($SUPER_EDITOR,), \CUser::GetUserGroup($USER_ID))) return false;
        $HLBClass = $this->getHLBClass();
        $item_ = $HLBClass::getlist(
            [
                'select'=>['ID'],
                'filter'=>['ID'=>$id,'UF_AUTHOR_ID'=>$USER_ID],
                'limit'=>1
            ]
        )->fetch();
        return !$item_;
    }

    public function getUserFields($ID = 0){
		if (!$ID) $ID = $this->HB_ID;
        $fields = $GLOBALS["USER_FIELD_MANAGER"]->getUserFields('HLBLOCK_'.$ID,null,LANGUAGE_ID);
        return $fields;
    }

    public function removeSystemFields($oldFileds,$fields){
        $sysFields = [
            'UF_SORT',
            'UF_AUTHOR_ID',
            'UF_EXT_KEY',
            //'UF_PROJECT_ID',
            //'UF_PRODUKT_ID',
            'UF_MANAGER_ID',
            'UF_USER_EDIT_ID',
        ];

        if($oldFileds){
            foreach ($sysFields as $name){
                if (isset($fields[$name])) $fields[$name] = $oldFileds[$name];
            }
        }else{
            foreach ($sysFields as $name){
                unset($fields[$name]);
            }
        }

        return $fields;
    }


    public function transformField($oldFileds,$fields){
        $UFields = $this->getUserFields();

        // для полей тип файл
        // поле НАЗВАНИЕ_ПОЛЯ_DELETE отправляется со значениями а само поле не отправляетмся
        // потому что оно заполняется только когда отправляется файл
        foreach ($fields as $name=>$value){
            if (strpos($name,'_DELETE') !== false){
                $findFieldName = str_replace(['_DELETE','_DOUBLE'],'',$name);
                if (!isset($fields[$findFieldName])) $fields[$findFieldName] = [];
            }
            if (strpos($name,'_DOUBLE') !== false){
                $findFieldName = str_replace(['_DELETE','_DOUBLE'],'',$name);
                if (!isset($fields[$findFieldName])) $fields[$findFieldName] = [];
            }
        }

        foreach ($fields as $name=>&$value){
            if (!isset($UFields[$name])) continue;

            $HL_FIELD_DATA = $UFields[$name];
            if ($HL_FIELD_DATA["USER_TYPE_ID"] == 'datetime' && $value) {
                $value = \Bitrix\Main\Type\DateTime::createFromTimestamp($value);
            }
			if ($HL_FIELD_DATA["USER_TYPE_ID"] == 'file') {
			    if (is_array($value) && $value[0]===0) $value = [];

			    // если была комманда удалить файлы
			    if(!empty($fields[$name.'_DELETE'])){
			        $del_id = $fields[$name.'_DELETE'];
			        if (is_array($oldFileds[$name])){
			            foreach ($oldFileds[$name] as &$vl){
			                if ($vl == $del_id) $vl = ['old_id'=>$del_id];
                        }
                    }else{
                        $oldFileds[$name] = ['old_id'=>$del_id];
                    }
                }
			    // распаковываем мульти значение поля файла
                /*
                 * Ex:
                 * UF_PHOTO = [
                 *              'url' = [
                 *                      0 => /tmp/sdfsdfs.jpg,
                 *                      1 => /tmp/sdfsdfs.jpg
                 *              ]
                 * ..................
                 * ]
                 */
                // если поле не мульти, то оно сразу отправляется в базу без какой либо трпнсформации
				if($HL_FIELD_DATA["MULTIPLE"] == 'Y'){
					$newField = [];
					foreach($value as $key=>$v){
					    // $key = 'url'
                        // $key = 'name'
                        // $key = 'type'
                        // $key = 'tmp_name'
					    if (is_array($v)){
                            // или $v - это [0 => /tmp/sdfsdfs.jpg,1 => /tmp/sdfsdfs.jpg]
                            foreach($v as $k2 => $one){
                                $newField[$k2][$key] = $one;
                            }
					    }else{
                            // решаем $v - это /tmp/sdfsdfs.jpg
                            $newField[0][$key] = $v;
                        }
					}

					// если это уже существует update
					if ($oldFileds) {
                        // добаляем $oldFileds[$name] что бы удалить отмечанные old_id
                        $value = array_merge($newField, $oldFileds[$name]);
                    }else{
                        // есди это новый add
					    $value = $newField;
                    }
				}

                if(!empty($fields[$name.'_DOUBLE'])) {
                    $L = [];
                    foreach($fields[$name.'_DOUBLE'] as $id_double) {
                        $fileInfo = \CFile::GetFileArray($id_double);
                        if ($fileInfo) {
                            $L[] = \CFile::MakeFileArray($fileInfo['SRC']);
                        }
                    }

                    //$value = array_merge($value,$L);
                    if ($oldFileds) $value = array_merge($L, $oldFileds[$name]);
                    else  $value = $L;
                }

                if (!$value) unset($fields[$name]);
            }

			if (isset($fields[$name.'_DELETE'])) unset($fields[$name.'_DELETE']);
            if (isset($fields[$name.'_DOUBLE'])) unset($fields[$name.'_DOUBLE']);
        }

        return $fields;
    }

    //abstract public function getData(bool $clear);
    abstract public function add(array $fields);
    abstract public function update(array $fields);
    abstract public function delete(int $id);
}
