<?php
namespace Bitrix\Kabinet\container;

use \Bitrix\Main\SystemException;

class Hlbase extends Base {
    protected $RESTRICT_TIME = 180; // sec.
    protected $selectFields = [];
    protected $config = [];

    public function __construct(int $id, $HLBCClass)
    {
        parent::__construct($id, $HLBCClass);

    }

    public function add($fields){

        $HLBClass = $this->getHLBClass();

        // просеиваем поля
        $addFields = $this->siftFields($fields);
        if (!$addFields) throw new SystemException("You can't create an object with empty fields");

        $addFields = $this->removeSystemFields([],$addFields);
        $addFields = $this->transformField([],$addFields);
        $checkResult = $this->checkFields($addFields);
        if(!$checkResult) {
            throw new SystemException("Не заполнено обязательно поле ".$this->requiredField);
        }

        //if(!$this->RestrictForm()) throw new SystemException("Слишком много одновременных запросов!");

        $addFields = $this->addDefault($addFields);
        $obResult = $HLBClass::add($addFields);
        if (!$obResult->isSuccess()){
            $err = $obResult->getErrors();
            $mess = $err[0]->getMessage();
            throw new SystemException($mess);
        }

        $ID = $obResult->getID();

        $this->clearCache();

        return $ID;
    }

    public function update($fields){
        $HLBClass = $this->getHLBClass();

        if (empty($fields['ID'])) throw new SystemException("You can't edit an object without ID");
        $id = $fields['ID'];
        unset($fields['ID']);

        $oldFileds = $HLBClass::getById($id)->fetch();
        if (!$oldFileds)  throw new SystemException("Object with ID ".$id." not found");

        // просеиваем поля
        $editFields = $this->siftFields($fields);
        if (!$editFields) throw new SystemException("You can't edit an object with empty fields");

        $editFields = $this->removeSystemFields($oldFileds,$editFields);
        $editFields = $this->transformField($oldFileds,$editFields);

        $defaultFields = $this->editDefault($editFields);
        $fullFields = array_merge($editFields,$defaultFields);
        // делаем валидацию
        $checkResult = $this->checkFields($fullFields);
        if(!$checkResult) {
            throw new SystemException("Не заполнено обязательно поле ".$this->requiredField);
        }

        if(!empty($oldFileds['UF_AUTHOR_ID']))
                if($this->isnotUserElement($id)) throw new SystemException("Объект не принадлежит пользователю");

        //AddMessage2Log([$fullFields], "my_module_id");

        $obResult = $HLBClass::update($id, $fullFields);
        if (!$obResult->isSuccess()){
            $err = $obResult->getErrors();
            $mess = $err[0]->getMessage();
            throw new SystemException($mess);
        }

        $this->clearCache();


		$ID = $obResult->getID();

        //AddMessage2Log([$fullFields], "my_module_id");

		return $ID;
    }

    public function delete(int $id){
        $HLBClass = $this->getHLBClass();
        if ($HLBClass::getEntity()->hasField('UF_AUTHOR_ID'))
            if($this->isnotUserElement($id)) throw new SystemException("Объект не принадлежит пользователю");

        $result = $HLBClass::delete($id);
        if (!$result->isSuccess()){
            $err = $result->getErrors();
            $mess = $err[0]->getMessage();
            throw new SystemException($mess);
        }

        $this->clearCache();
    }

    // для создания getData
    public function convertData($saveData,$HL_TASK){

            if (!$saveData) $saveData = [];
            foreach ($HL_TASK as $name => $HL_FIELD_DATA) {

                if (!isset($saveData[$name])) $saveData[$name] = '';
                $value = $saveData[$name];

                // for debug!!
                //echo "<pre>";
                //var_dump($HL_FIELD_DATA);
                //echo "</pre>";

                if (!$value) {
                    if($HL_FIELD_DATA['MULTIPLE'] == 'Y') $value = serialize([]);
                }

                if ($HL_FIELD_DATA["USER_TYPE_ID"] == 'datetime') {
                    if ($value)
                        // представление в виде timestamp
                        $value = $value->getTimestamp();
                    else
                        $value = '';
                }

                if (
                    $HL_FIELD_DATA["USER_TYPE_ID"] == "hlblock" ||
                    $HL_FIELD_DATA["USER_TYPE_ID"] == "kabinethlblock"
                ) {
                    /*
                    $HL_BLK = (\KContainer::getInstance())->get('HlBuilder')->get(
                        $HL_FIELD_DATA["SETTINGS"]["HLBLOCK_ID"]
                    );
                    */

                    $value = unserialize($value);
                    sort($value);

                    /*
                    array_walk($value, function (&$item, $key, $HL_BLK) {
                        $data = $HL_BLK::getById($item)->fetch();
                        $item = $data['UF_NAME'];
                    }, $HL_BLK);
                    */
                }elseif($HL_FIELD_DATA['MULTIPLE'] == 'Y'){
                    $value_ = [...unserialize($value)];
                    if ($HL_FIELD_DATA["USER_TYPE_ID"] != 'file'){
                        if (empty($value_)) $value_ = [['VALUE' => '']];
                        else {
                            $value_ = array_map(fn($v) => ['VALUE' => $v], $value_);

                        }
                    }
                    $value = $value_;
                }
                $saveData[$name] = $value;
            }


        // Create original
        $original = [];
            foreach ($saveData as $fieldName => $value) {

                if($HL_TASK[$fieldName]["USER_TYPE_ID"]=='file'){
                    if (is_array($value)){
                        $value = array_map(function($value){
                            $fd = $fd2= \CFile::GetFileArray($value);
                            unset($fd2['ID'],$fd2['SRC'],$fd2['FILE_SIZE']);
							$f = array_diff_key($fd,$fd2);
							$file = new \Bitrix\Main\IO\File(\Bitrix\Main\Application::getDocumentRoot().$f['SRC']);
							$f['MIME'] = $file->getContentType();	
                            return $f;
                        },$value);				
                    }elseif ($value){
                        $fd = $fd2= \CFile::GetFileArray($value);
                        unset($fd2['ID'],$fd2['SRC'],$fd2['FILE_SIZE']);
                        $value = array_diff_key($fd,$fd2);
                    }else{
                        $value = [];
                    }
                }

                if($HL_TASK[$fieldName]["USER_TYPE_ID"]=='datetime' && $value)
                        $value = [
                            'FORMAT1'=>\Bitrix\Main\Type\DateTime::createFromTimestamp($value)->format("d.m.Y"),
                            'FORMAT2'=>\Bitrix\Main\Type\DateTime::createFromTimestamp($value)->format("Y-m-d"),
                            'FORMAT3'=>\Bitrix\Main\Type\DateTime::createFromTimestamp($value)->format("d.m.Y H:i"),
                            'TIMESTAMP'=>$value,
                            'MINDATE'=>(new \Bitrix\Main\Type\DateTime())->getTimestamp()

                        ];


                if($HL_TASK[$fieldName]["USER_TYPE_ID"]=='enumeration'){
                    $userFieldEnum = new \CUserFieldEnum();
                    $vallist = $userFieldEnum->GetList([], ['USER_FIELD_ID' => $HL_TASK[$fieldName]['ID']]);
                    $value = [];
                    while($item = $vallist->Fetch())
                    {
                        $value[] = $item;
                    }

                    // Добавляем первый пустой элемент в select
                    //берем любой первый элемент
                    $arrShema = current($value);
                    // clear
                    // обнуляем все значения ключей массива
                    array_walk_recursive($arrShema, function(&$item, $key){
                        $item = '';
                    });
                    // добавляем вначло массива
                    array_unshift($value,$arrShema);
                }


                $original[$fieldName.'_ORIGINAL'] = $value;

                if($HL_TASK[$fieldName]["USER_TYPE_ID"]=='file'){
                    $saveData[$fieldName.'_DELETE'] = [];
                    $saveData[$fieldName.'_DOUBLE'] = [];
                    $saveData[$fieldName] = [];
                }

            }

            return array_merge($saveData, $original);
    }

    public function getEmptyData(){
        $project = [];

        foreach ($this->getUserFields() as $fieldName=> $params) {

            $project[$fieldName] = '';

            if (isset($params['SETTINGS']['DEFAULT_VALUE']) && $params['SETTINGS']['DEFAULT_VALUE'] != NULL) {
                if($params['USER_TYPE_ID'] != 'datetime')
                        $project[$fieldName] = $params['SETTINGS']['DEFAULT_VALUE'];
            }
        }

        $project = $this->convertData($project, $this->getUserFields());
        return $project;
    }

    public function getSelectFields(){
        return $this->selectFields;
    }

    public function clearCache(){
        $this->getData($clear=true);
    }

    public function config($value){
        $config = $this->config;
        if (!$config) return false;

        return \PHelp::array_keys_multi($config,$value);
    }
}
