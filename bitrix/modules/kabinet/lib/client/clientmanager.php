<?php
namespace Bitrix\Kabinet\client;

use \Bitrix\Main\SystemException,
    \Bitrix\Kabinet\exceptions\ClientException,
    \Bitrix\Kabinet\exceptions\TestException,
    \Bitrix\Main\Entity;

class Clientmanager {

    protected $selectFields = [];
    public $clientList = [];
    public $updateFields = [
        'ID','TIMESTAMP_X','LOGIN','NAME','LAST_NAME','EMAIL','DATE_REGISTER','SECOND_NAME',
        'PERSONAL_PHOTO','PERSONAL_PHONE','PERSONAL_PROFESSION','PERSONAL_WWW','PASSWORD'
    ];
    public $updateUserFields = ['UF_EMAIL_NOTIFI','UF_TELEGRAM_NOTFI','UF_TELEGRAM_ID'];
    public $defFiltere = [];
    protected $user;

    public function __construct($user,$defFiltere,array $selectFields = [])
    {
        global $USER;
        $this->defFiltere = $defFiltere;
        $this->user = $user;
        if ($selectFields) $this->updateFields = $selectFields;
    }

    public function retrieveAdditionalsFields(array $fields){
        $ret = array();
        $updateFields = array_merge($this->updateFields,$this->updateUserFields);
        foreach($updateFields as $name){
			if (isset($fields[$name])) $ret[$name] = $fields[$name];
			
			// пароль нельзя сохранять пустым
			if (empty($ret['PASSWORD'])) unset($ret['PASSWORD']);
		}
        
        return $ret;
    }

    public function update($fields){

        $ID = $fields['ID'];
        unset($fields['ID'],$fields['TIMESTAMP_X'],$fields['DATE_REGISTER']);

        $editFields = $this->retrieveAdditionalsFields($fields);

        //throw new ClientException(print_R($editFields,true));

        $user = new \CUser;

        //$editFields['UF_TELEGRAM_NOTFI'] = 0;

        $user->Update($ID, $editFields);
        if ($user->LAST_ERROR) throw new ClientException($user->LAST_ERROR);

        return $ID;
    }

    public function getData($id=[],$filter=[],$runtime=[],$limit=20000000,$offset=0){
        global $USER_FIELD_MANAGER;

        if (!$filter){
            $user = $this->user;
            $filter = ['ID'=>$user->get('ID')];
        }

		$defFiltere = $this->defFiltere;
			
		$queryFilter = array_merge($defFiltere,$filter);
        $data = \Bitrix\Kabinet\UserTable::getListActive([
            'select'=>
                $this->updateFields
                ,
            'filter'=>$queryFilter,
            'runtime'=>$runtime,
            'limit'=>$limit,
            'offset'=>$offset,
            'order'=>['DATE_REGISTER'=>'desc']
        ])->fetchAll();

        // for debug
        //echo \Bitrix\Main\Entity\Query::getLastQuery();

        $listdata = [];

        foreach ($data as $index => $item){

            if (isset($item['TIMESTAMP_X'])) $item['TIMESTAMP_X'] = $item['TIMESTAMP_X']->format("d.m.Y");
            if (isset($item['DATE_REGISTER'])) $item['DATE_REGISTER'] = $item['DATE_REGISTER']->format("d.m.Y");
            if (isset($item['PASSWORD'])) $item['PASSWORD'] = '';
            $listdata[] = $item;
        }

        foreach ($listdata as $index => $fields){
            foreach ($fields as $fieldName => $value) {

                if(in_array($fieldName,['TIMESTAMP_X','DATE_REGISTER'])){
                    if ($value){
                        $value = [
                            'FORMAT1'=>$value,
                            'FORMAT2'=>(new \Bitrix\Main\Type\DateTime($value,"d.m.Y"))->format("Y-m-d"),
                            'FORMAT2'=>(new \Bitrix\Main\Type\DateTime($value,"d.m.Y"))->format("Y-m-d"),
                            'TIMESTAMP'=>(new \Bitrix\Main\Type\DateTime($value,"d.m.Y"))->getTimestamp(),
                            'MINDATE'=>(new \Bitrix\Main\Type\DateTime())->getTimestamp()
                        ];
                    }
                }

                if (in_array($fieldName,['PERSONAL_PHOTO'])){
                    if ($value) {
                        $fd = $fd2 = \CFile::GetFileArray($value);
                        unset($fd2['ID'], $fd2['SRC'], $fd2['FILE_SIZE']);
                        $value = array_diff_key($fd, $fd2);
                    }
                }


                $listdata[$index][$fieldName . '_ORIGINAL'] = $value;

                if(in_array($fieldName,['PERSONAL_PHOTO'])){
                    $listdata[$index][$fieldName.'_DELETE'] = [];
                    $listdata[$index][$fieldName] = [];
                }
            }

            if ($listdata[$index]['PERSONAL_PHOTO_ORIGINAL']){
                $fd = \CFile::ResizeImageGet($listdata[$index]['PERSONAL_PHOTO_ORIGINAL']['ID'], array('width'=>60, 'height'=>60), BX_RESIZE_IMAGE_EXACT, true);
                $fd['ID'] = $listdata[$index]['PERSONAL_PHOTO_ORIGINAL']['ID'];
                $listdata[$index]['PERSONAL_PHOTO_ORIGINAL_60x60'] = $fd;

                $fd = \CFile::ResizeImageGet($listdata[$index]['PERSONAL_PHOTO_ORIGINAL']['ID'], array('width'=>300, 'height'=>300), BX_RESIZE_IMAGE_EXACT, true);
                $fd['ID'] = $listdata[$index]['PERSONAL_PHOTO_ORIGINAL']['ID'];
                $listdata[$index]['PERSONAL_PHOTO_ORIGINAL_300x300'] = $fd;
            }else{
                $config = (\KContainer::getInstance())->get('config');
                $listdata[$index]['PERSONAL_PHOTO_ORIGINAL_60x60'] = ['src'=> $config['USER']['photo_default'],'width'=>60,'height'=>60];
                $listdata[$index]['PERSONAL_PHOTO_ORIGINAL_300x300'] = ['src'=> $config['USER']['photo_default'],'width'=>300,'height'=>300];
            }

            $listdata[$index]['PRINT_NAME'] = current(array_filter([
                trim(implode(" ", [$fields['LAST_NAME'], $fields['NAME'], $fields['SECOND_NAME']])),
                $fields['LOGIN']
            ]));

        }

        // Add User fields
        foreach ($listdata as $index => $fields){
            $arUF = $GLOBALS["USER_FIELD_MANAGER"]->GetUserFields("USER", $fields['ID']);
            foreach ($arUF as $name=>$params){
                $listdata[$index][$name] = $params["VALUE"]?? 0;
                if ($params["USER_TYPE_ID"] == 'enumeration'){
                    $userFieldEnum = new \CUserFieldEnum();
                    $vallist = $userFieldEnum->GetList([], ['USER_FIELD_ID' => $params['ID']]);
                    $value = [];
                    while($item = $vallist->Fetch())
                    {
                        $value[] = $item;
                    }

                    $listdata[$index][$name. '_ORIGINAL'] =  $value;
                }
                elseif ($params["USER_TYPE_ID"]=="boolean"){
                    if($params["VALUE"] == "1") $listdata[$index][$name] = true;
                    else $listdata[$index][$name] = false;
                }
                else{
                    $listdata[$index][$name. '_ORIGINAL'] =  $params["VALUE"]?? 0;
                }
            }
        }


        return $listdata;
    }
}