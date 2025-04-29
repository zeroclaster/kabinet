<?php
namespace Bitrix\Kabinet\messanger;

use \Bitrix\Main\SystemException,
    \Bitrix\Kabinet\exceptions\MessangerException,
    \Bitrix\Kabinet\exceptions\TestException;

class Messanger extends \Bitrix\Kabinet\container\Hlbase {

    // статусы сообщений
    const NEW_MASSAGE = 5;  //Новое
    const READED_MASSAGE = 6;   //прочитанное
    const EDITED_MASSAGE = 7;   //исправленное
    const CANCELED_MASSAGE = 8; //удаленное

    const USER_MESSAGE = 3;
    const SYSTEM_MESSAGE = 4;


    // поля которые выводятся при выборе в селекте
    // например "UF_NAME"=>[1],
    public $fieldsType = [];

    public function __construct(int $id, $HLBCClass,$config=[])
    {
        global $USER;

        if (!$USER->IsAuthorized()) throw new MessangerException("Fatal error! Registered users only.");

        $this->config = $config;

        $sL = \Bitrix\Main\DI\ServiceLocator::getInstance();
        $sL->addInstanceLazy("Kabinet.ClientMessanger", ['constructor'=>
            static function(){
                $provider = \Bitrix\Kabinet\client\Providermessangerclient::getInstance();
                $project = $provider->build();
                return $project;
            }
        ]);

        parent::__construct($id, $HLBCClass);

        AddEventHandler("", "\Lmessanger::OnBeforeAdd", [$this,"convertMessage"]);
        AddEventHandler("", "\Lmessanger::OnBeforeAdd", [$this,"checkSendMessage"]);
        //AddEventHandler("", "\Lmessanger::OnBeforeUpdate", [$this,"OnBeforeUpdateHandler"]);
        //AddEventHandler("", "\Lmessanger::OnBeforeDelete", [$this,"OnBeforeDeleteHandler"]);
    }

    public function convertMessage($fields,$object)
    {
        $message = $fields['UF_MESSAGE_TEXT'];

        $message = str_replace([
            '#UF_PUBLISH_DATE#',
            '#UF_PROJECT_ID#',
            '#UF_TASK_ID#',
            '#UF_QUEUE_ID#',
            '#UF_TARGET_USER_ID#',
        ],[
            $fields['UF_PUBLISH_DATE']->format("d.m.Y H:i:s"),
            $fields['UF_PROJECT_ID'],
            $fields['UF_TASK_ID'],
            $fields['UF_QUEUE_ID'],
            $fields['UF_TARGET_USER_ID'],
        ],$message);

        $object->set('UF_MESSAGE_TEXT',$message);
    }

    public function checkSendMessage($fields,$object)
    {

        if (
        !$fields['UF_PROJECT_ID'] &&
        !$fields['UF_TASK_ID'] &&
        !$fields['UF_QUEUE_ID'] &&
        !$fields['UF_TARGET_USER_ID']
        ){
            throw new MessangerException("Ошибка при отправки сообщения. Системные поля пустые.");
        }

        if (!$fields['UF_MESSAGE_TEXT']) throw new MessangerException("Ошибка. Вы отправляете пустое сообщение.");

    }

    public function OnBeforeUpdateHandler($id,$primary,$fields,$object,$oldFields)
    {
    }

    public function OnBeforeDeleteHandler($id)
    {
    }


    protected function addDefault($fields){
        global $USER;

        $fields = parent::addDefault($fields);
        $fields['UF_AUTHOR_ID'] = $USER->GetID();

        return $fields;
    }

    public function sendSystemMessage($message,$QUEUE_ID=0,$TASK_ID=0,$PROJECT_ID=0,$TARGET_USER_ID=0){
        $sL = \Bitrix\Main\DI\ServiceLocator::getInstance();
        $taskManager = $sL->get('Kabinet.Task');
        $projectManager = $sL->get('Kabinet.Project');

        $fields = [
            'UF_TYPE' => \Bitrix\Kabinet\messanger\Messanger::SYSTEM_MESSAGE,
            'UF_UPLOADFILE' => [],
            'UF_PROJECT_ID'=>$PROJECT_ID,
            'UF_TASK_ID'=>$TASK_ID,
            'UF_QUEUE_ID'=>$QUEUE_ID,
            'UF_MESSAGE_TEXT'=>$message,
            'UF_TARGET_USER_ID'=>0,
        ];

        if ($TASK_ID && !$PROJECT_ID) {
            $taskData = $taskManager->getData($cache=true,$user_id = [],$filter=['ID'=>$TASK_ID]);
            if (!$taskData) throw new MessangerException("При отправки сообщения не найдена задача с ID:".$TASK_ID);
            $taskData = $taskData[0];

            $fields['UF_TASK_ID'] = $taskData['ID'];
            $fields['UF_PROJECT_ID'] = $taskData['UF_PROJECT_ID'];
            $fields['UF_TARGET_USER_ID'] = $taskData['UF_AUTHOR_ID'];
        }

        if (!$TASK_ID && $PROJECT_ID){
            $projectData = $projectManager->getData($cache=true,$user_id = [],$filter=['ID'=>$PROJECT_ID]);
            if (!$projectData) throw new MessangerException("При отправки сообщения не найден проект с ID:".$PROJECT_ID);
            $projectData = $projectData[0];

            $fields['UF_PROJECT_ID'] = $projectData['ID'];
            $fields['UF_TARGET_USER_ID'] = $projectData['UF_AUTHOR_ID'];
        }

        // системное сообщение только пользователю
        if (!$TASK_ID && !$PROJECT_ID && $TARGET_USER_ID) $fields['UF_TARGET_USER_ID'] = $TARGET_USER_ID;

        $upd_id = $this->add($fields);

        return $upd_id;
    }

    public function getData($filter = [],$offset=0,$limit=5,$clear=false,$new_reset='y'){
        global $CACHE_MANAGER, $USER;

        $sL = \Bitrix\Main\DI\ServiceLocator::getInstance();
        $ClientManager = $sL->get('Kabinet.ClientMessanger');
        $user = (\KContainer::getInstance())->get('user');
        $user_id = $user->get('ID');
        $isAdmin = !($user_id && 1);

        // сколько времени кешировать
        $ttl = 14400;
        // hack: $ttl = 0 то не кешировать

		// make filter....
        if ($filter)  $ttl = 0;
		//if (!$filter) $filter = ['UF_AUTHOR_ID'=>$user_id];

        if ($isAdmin) {
        }
		else $filter = array_merge(['LOGIC' => 'AND',['LOGIC' => 'OR','UF_AUTHOR_ID'=>$user_id,'UF_TARGET_USER_ID'=>$user_id]],$filter);

		$filter_default = $this->config('FILTER_DEFAULT');
        $filter = array_merge($filter_default,$filter);

        $cacheId = '';
        $cacheId = SITE_ID."|".$cacheId;
        $cacheId .= "|".serialize($user_id);

        $cache = new \CPHPCache;
        // Clear cache "bank_data"
        if ($clear) $cache->clean($cacheId, "kabinet/messangerdata");
        //$CACHE_MANAGER->ClearByTag("messanger_data");

        //$cache->clean($cacheId, "kabinet/messangerdata");

        $ttl = 0;
        if ($cache->StartDataCache($ttl, $cacheId, "kabinet/messangerdata"))
        {
            if (defined("BX_COMP_MANAGED_CACHE"))
            {
                $CACHE_MANAGER->StartTagCache("messanger_data");
                //\CIBlock::registerWithTagCache(self::SERVICES_IBLOCK);
            }

			//\Dbg::print_r($filter);

            $messageList = \Bitrix\Kabinet\messanger\datamanager\LmessangerTable::getListActive([
                'select'=>['*'],
                'filter'=>$filter,
                'order'=>["UF_PUBLISH_DATE"=>'DESC'],
				'limit'=>$limit,
				'offset'=>$offset
            ])->fetchAll();

            //\Dbg::print_r(\Bitrix\Main\Entity\Query::getLastQuery());

            $a = \Bitrix\Main\Entity\Query::getLastQuery();

            $messageList = array_reverse($messageList);
          
			$listdata = [];
			foreach ($messageList as $data) {
                $c = $this->convertData($data, $this->getUserFields());
                $listdata[] = $c;
            }

			if ($listdata) {
                $target_users = array_column($listdata, 'UF_AUTHOR_ID_ORIGINAL');
                $usersdata = $ClientManager->getData([], ['ID'=>$target_users]);
                foreach ($listdata as $index=>$message){
                    $ID = $message['UF_AUTHOR_ID_ORIGINAL'];
                    $key = array_search($ID, array_column($usersdata, 'ID'));
                    if ($key !== false){
                        $listdata[$index]['UF_AUTHOR_ID_ORIGINAL'] = $usersdata[$key];
                    }
                }
            }


            if (defined("BX_COMP_MANAGED_CACHE")) $CACHE_MANAGER->EndTagCache();
            $cache->EndDataCache(array($listdata));
        }
        else
        {
            $vars = $cache->GetVars();
            $listdata = $vars[0];
        }
		
		
		// Смотрим кто читает сообщение, если он было адресовано пользователю $USER->GetID(), то ставим как прочитано
		// сбрасываем кеш что бы при след запросе оно уже было как прочитано
		$isRead = false;

        if($new_reset == 'y'){
            foreach ($listdata as $item) {
                if (
                    $item['UF_TARGET_USER_ID'] == $USER->GetID() &&
                    $item['UF_STATUS'] == self::NEW_MASSAGE
                ) {
                    \Bitrix\Kabinet\messanger\datamanager\LmessangerTable::update($item['ID'], ['UF_STATUS' => self::READED_MASSAGE]);
                    $isRead = true;
                }
            }
		}

		if ($isRead){
			// Clear cache "bank_data"
			//$CACHE_MANAGER->ClearByTag("messanger_data");
			$cache->clean($cacheId, "kabinet/messangerdata");
		}

        return $listdata;
    }

    public function clearCache(){
        $this->getData([],0,5,$clear=true);
    }
}
