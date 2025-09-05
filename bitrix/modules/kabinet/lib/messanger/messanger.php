<?php
namespace Bitrix\Kabinet\messanger;

use \Bitrix\Main\SystemException,
    \Bitrix\Kabinet\exceptions\MessangerException,
    \Bitrix\Kabinet\exceptions\TestException;

class Messanger extends \Bitrix\Kabinet\container\Abstracthighloadmanager {

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
    protected $clientmanager;
    protected $user;

    public function __construct($user, $HLBCClass,$clientmanager,$config=[])
    {
        $this->config = $config;
        //"Kabinet.ClientMessanger"
        $this->clientmanager = $clientmanager;
        $this->user = $user;
        parent::__construct($HLBCClass);

        if (!\PHelp::isAdmin()) {
            AddEventHandler("", "\Lmessanger::OnBeforeUpdate", function ($id, $fields, $object, $oldData) {

                $UF_STATUS = $oldData->get('UF_STATUS');
                if ($UF_STATUS != Messanger::NEW_MASSAGE) throw new SystemException("Невозможно выполнить команду. Нехватает прав.");

            });
        }

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
            $taskData = \Bitrix\Kabinet\task\datamanager\TaskTable::getById($TASK_ID)->fetch();
            if (!$taskData) throw new MessangerException("При отправки сообщения не найдена задача с ID:".$TASK_ID);
            $fields['UF_TASK_ID'] = $taskData['ID'];
            $fields['UF_PROJECT_ID'] = $taskData['UF_PROJECT_ID'];
            $fields['UF_TARGET_USER_ID'] = $taskData['UF_AUTHOR_ID'];
        }

        if (!$TASK_ID && $PROJECT_ID){
            $projectData = \Bitrix\Kabinet\project\datamanager\ProjectsTable::getById($PROJECT_ID)->fetch();
            if (!$projectData) throw new MessangerException("При отправки сообщения не найден проект с ID:".$PROJECT_ID);
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

        $ClientManager = $this->clientmanager;
        $user = $this->user;
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
                        $adminUsers = \PHelp::usersGroup(MANAGER);
                        $admin_list = array_column($adminUsers,"ID");
                        $usersdata[$key]['IS_ADMIN'] = false;
                        if (in_array($usersdata[$key]['ID'],$admin_list)) $usersdata[$key]['IS_ADMIN'] = true;
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


    public function getData2($filter = [], $offset = 0, $limit = 5, $clear = false, $new_reset = 'y')
    {
        global $CACHE_MANAGER, $USER;

        $ClientManager = $this->clientmanager;
        $user = $this->user;
        $user_id = $user->get('ID');
        $isAdmin = \PHelp::isAdmin();

        $ttl = 14400;
        if ($filter) $ttl = 0;

        $finalFilter = $filter;
        if (!$isAdmin) {
            $finalFilter = array_merge([
                'LOGIC' => 'AND',
                [
                    'LOGIC' => 'OR',
                    'UF_AUTHOR_ID' => $user_id,
                    'UF_TARGET_USER_ID' => $user_id
                ]
            ], $filter);
        }

        $cacheId = SITE_ID . '|' . serialize($user_id) . '|' . serialize($finalFilter) . "|limit={$limit}";
        $cache = new \CPHPCache;

        if ($clear) {
            $cache->clean($cacheId, "kabinet/messangerdata");
        }

        $ttl = 0; // временно отключаем кеш для отладки, можно вернуть
        if ($cache->StartDataCache($ttl, $cacheId, "kabinet/messangerdata")) {
            if (defined("BX_COMP_MANAGED_CACHE")) {
                $CACHE_MANAGER->StartTagCache("messanger_data");
            }

            $messages = [];

            // === Шаг 1: Получаем все новые (непрочитанные) сообщения ===
            $newFilter = array_merge($finalFilter, ['UF_STATUS' => self::NEW_MASSAGE]);
            $newMessages = \Bitrix\Kabinet\messanger\datamanager\LmessangerTable::getList([
                'select' => ['*'],
                'filter' => $newFilter,
                'order' => ["UF_PUBLISH_DATE" => 'DESC'],
            ])->fetchAll();

            // Конвертируем новые сообщения
            foreach ($newMessages as $data) {
                $messages[] = $this->convertData($data, $this->getUserFields());
            }

            $alreadyLoadedIds = array_column($newMessages, 'ID');

            // === Шаг 2: Если новых меньше лимита — добавляем последние (включая прочитанные) ===
            $remainingCount = $limit - count($messages);
            if ($remainingCount > 0) {
                $additionalFilter = $finalFilter;
                if (!empty($alreadyLoadedIds)) {
                    $additionalFilter['!ID'] = $alreadyLoadedIds; // Исключаем уже загруженные
                }

                $moreMessages = \Bitrix\Kabinet\messanger\datamanager\LmessangerTable::getList([
                    'select' => ['*'],
                    'filter' => $additionalFilter,
                    'order' => ["UF_PUBLISH_DATE" => 'DESC'],
                    'limit' => $remainingCount,
                ])->fetchAll();

                foreach ($moreMessages as $data) {
                    $messages[] = $this->convertData($data, $this->getUserFields());
                }
            }

            // Сортируем все сообщения по дате (DESC), чтобы самые свежие были вверху
            usort($messages, function ($a, $b) {
                return strtotime($b['UF_PUBLISH_DATE']) - strtotime($a['UF_PUBLISH_DATE']);
            });

            // === Обогащаем данные авторов ===
            if ($messages) {
                $authorIds = array_column($messages, 'UF_AUTHOR_ID_ORIGINAL');
                $usersData = $ClientManager->getData([], ['ID' => array_unique($authorIds)]);

                foreach ($messages as $index => $message) {
                    $id = $message['UF_AUTHOR_ID_ORIGINAL'];
                    $key = array_search($id, array_column($usersData, 'ID'));
                    if ($key !== false) {
                        $messages[$index]['UF_AUTHOR_ID_ORIGINAL'] = $usersData[$key];
                    }
                }
            }

            if (defined("BX_COMP_MANAGED_CACHE")) {
                $CACHE_MANAGER->EndTagCache();
            }

            $cache->EndDataCache([$messages]);
        } else {
            $vars = $cache->GetVars();
            $messages = $vars[0] ?? [];
        }

        // === Помечаем новые как прочитанные, если new_reset == 'y' ===
        if ($new_reset === 'y') {
            $readMessages = [];
            foreach ($messages as $item) {
                if (
                    $item['UF_TARGET_USER_ID'] == $USER->GetID() &&
                    $item['UF_STATUS'] == self::NEW_MASSAGE
                ) {
                    $readMessages[] = $item['ID'];
                }
            }

            if (!empty($readMessages)) {
                foreach ($readMessages as $id) {
                    \Bitrix\Kabinet\messanger\datamanager\LmessangerTable::update($id, ['UF_STATUS' => self::READED_MASSAGE]);
                }
                // Очистка кеша после обновления статусов
                $cache->clean($cacheId, "kabinet/messangerdata");
            }
        }

        return $messages;
    }

    public function clearCache(){
        $this->getData([],0,5,$clear=true);
    }
}
