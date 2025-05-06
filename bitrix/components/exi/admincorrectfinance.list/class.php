<?
use Bitrix\Main,
    Bitrix\Main\Error,
    Bitrix\Main\Localization\Loc,
    Bitrix\Main\ErrorCollection,
    Bitrix\Main\Engine\ActionFilter,
    Bitrix\Main\Loader;

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) die();

Loc::loadMessages(__FILE__);

class admincorrectfinanceListComponent extends \CBitrixComponent implements \Bitrix\Main\Engine\Contract\Controllerable, \Bitrix\Main\Errorable
{
    const ERROR_TEXT = 1;
    const ERROR_404 = 2;

    protected $errorCollection;

    /**
     * Base constructor.
     * @param \CBitrixComponent|null $component     Component object if exists.
     */
    public function __construct($component = null)
    {
        parent::__construct($component);
        $this->errorCollection = new ErrorCollection();
    }

    public function onPrepareComponentParams($params)
    {
        $request =$this->request;
		
		$arrFilter = [];
				
		if (!empty($params["FILTER_NAME"]) && preg_match("/^[A-Za-z_][A-Za-z01-9_]*$/", $params["FILTER_NAME"]))
		{
			$arrFilter = $GLOBALS[$params["FILTER_NAME"]] ?? [];
			if (!is_array($arrFilter))
			{
				$arrFilter = [];
			}
		}
		
		$params["FILTER"] = $arrFilter;

		if (empty($params['COUNT'])) $params['COUNT'] = 25;
        $params['OFFSET'] = 0;

        if(empty($params['MESSAGE_COUNT'])) $params['MESSAGE_COUNT'] = 5;

        return $params;
    }

    public function executeComponent()
    {
        if ($this->hasErrors())
        {
            return $this->processErrors();
        }
		
		$this->prepareData();
        $this->includeComponentTemplate($this->template);

        return true;
    }

    public function covertArray(array $source){

        if (!$source) return [];

        $ret = [];
        foreach($source as $item){
            $ret[$item['ID']] = $item;
        }

        return $ret;
    }

    public function prepareData(){
        global $DB;
		$arParams = $this->arParams;
        $arResult = &$this->arResult;
		$FILTER = $arParams['FILTER'];
        $this->arResult["CLIENT_DATA"] = [];
        $this->arResult["PROJECT_DATA"] = [];
        $this->arResult["TASK_DATA"] = [];
        $this->arResult["ORDER_DATA"] = [];
        $this->arResult["RUNNER_DATA"] = [];
		$this->arResult["MESSAGE_DATA"] = [];
        $this->arResult["TOTAL"] = 0;

        $sL = \Bitrix\Main\DI\ServiceLocator::getInstance();
		$ClientManager = $sL->get('Kabinet.Client');
		$projectManager = $sL->get('Kabinet.Project');
		$taskManager = $sL->get('Kabinet.Task');
		$runnerManager = $sL->get('Kabinet.Runner');
		$messanger = $sL->get('Kabinet.Messanger');

		$ClassClient = \Bitrix\Kabinet\UserTable::class;
        $ClassMessager = \Bitrix\Kabinet\messanger\datamanager\LmessangerTable::class;

        $HLBClassProject = \Bitrix\Main\DI\ServiceLocator::getInstance()->get('BRIEF_HL');
        $HLBClassTask = \Bitrix\Main\DI\ServiceLocator::getInstance()->get('TASK_HL');
        $HLBClassFulf = \Bitrix\Main\DI\ServiceLocator::getInstance()->get('FULF_HL');

        $Query = $HLBClassFulf::query();
        $entity = $Query->getEntity();
        $connection = $entity->getConnection();
        $Query->setSelect([
            'ID',
            'UF_AUTHOR_ID'=>'TASK.UF_AUTHOR_ID'
        ]);

        //Плановая дата публикации
        if(!empty($FILTER['planedaterangesearchfrom'])) $Query->addFilter('>UF_PLANNE_DATE',$FILTER['planedaterangesearchfrom']);
        if(!empty($FILTER['planedaterangesearchto'])) $Query->addFilter('<UF_PLANNE_DATE',$FILTER['planedaterangesearchto']);

        // Дата публикации
        if(!empty($FILTER['publicdatefromsearch']))  $Query->addFilter('>UF_ACTUAL_DATE',$FILTER['planedaterangesearchto']);
        if(!empty($FILTER['publicdatetosearch'])) $Query->addFilter('<UF_ACTUAL_DATE',$FILTER['planedaterangesearchto']);

        // Найти исполнение, id
        if(!empty($FILTER['executionidsearch'])) $Query->addFilter('ID',$FILTER['executionidsearch']);

        //Со статусом
        if(!empty($FILTER['statusexecutionsearch'])) $Query->addFilter('UF_STATUS',$FILTER['statusexecutionsearch']);


        if(
            !empty($FILTER["accountsearch"]) ||
            !empty($FILTER["loginsearch"]) ||
            !empty($FILTER["ipsearch"])
        ) {
            $carriagesFilter = \Bitrix\Main\ORM\Query\Query::filter()->logic('and');

            if (!empty($FILTER["accountsearch"])) $carriagesFilter->where('UF_SITE_SETUP','like', "%" . $FILTER["accountsearch"] . "%");
            if (!empty($FILTER["loginsearch"])) $carriagesFilter->where('UF_SITE_SETUP','like', "%" . $FILTER["loginsearch"] . "%");
            if (!empty($FILTER["ipsearch"])) $carriagesFilter->where('UF_SITE_SETUP','like', "%" . $FILTER["ipsearch"] . "%");

            $Query->where($carriagesFilter);
        }

        // Привязываем задачу
        $Query->registerRuntimeField('TASK',
            [
                'data_type' => $HLBClassTask,
                'reference' => ['=this.UF_TASK_ID' => 'ref.ID'],
                'join_type' => 'LEFT'
            ]
        );

        // Привязываем проект
        $Query->registerRuntimeField('PROJECT',
            [
                'data_type' => $HLBClassProject,
                'reference' => ['=this.TASK.UF_PROJECT_ID' => 'ref.ID'],
                'join_type' => 'LEFT'
            ]
        );

        $Query->registerRuntimeField('CLIENT',
            [
                'data_type' => $ClassClient,
                'reference' => ['=this.TASK.UF_AUTHOR_ID' => 'ref.ID'],
                'join_type' => 'LEFT'
            ]
        );
        $Query->addFilter('CLIENT.UF_GROUP_REF.GROUP_ID',REGISTRATED);
        $Query->addFilter('>TASK.ID',0);
        $Query->addFilter('TASK.UF_ACTIVE',1);

        // Требует внимания клинета
        if (!empty($FILTER["attention"]) && $FILTER["attention"] == 'clientattention'){
            $Query->addFilter('UF_STATUS',[
                3,  //3-Ожидается текст от клиента.
                5,  //5-На согласование (у клиента);
                8   //8-Отчет на проверке у клиента;
            ]);
        }

        // Требует внимания администратора
        /*
         * Фильтр выводит исполнения:
- с любым статусом с наступившими датами календаря, а статус не сменен (просроченные даты)
- с непрочитанными сообщениями в комментариях
         */
        if (!empty($FILTER["attention"]) && $FILTER["attention"] == 'adminattention'){
            $Query->registerRuntimeField('MESSAGE',
                [
                    'data_type' => $ClassMessager,
                    'reference' => ['=this.ID' => 'ref.UF_QUEUE_ID'],
                    'join_type' => 'INNER'
                ]
            );
            $carriagesFilter = \Bitrix\Main\ORM\Query\Query::filter()->logic('or');
            $carriagesFilter->where('MESSAGE.UF_STATUS',\Bitrix\Kabinet\messanger\Messanger::NEW_MASSAGE);
            $carriagesFilter->where('UF_HITCH',1);

            $Query->where($carriagesFilter);
        }

        // С просроченными стадиями
        if (!empty($FILTER["attention"]) && $FILTER["attention"] == 'hitchstade'){
            $Query->where('UF_HITCH',1);
        }


        if(!empty($FILTER["clientidsearch"])) {
            $Query->addFilter('CLIENT.ID',$FILTER["clientidsearch"]);
            $FILTER["clienttextsearch"] = '';
        }

        if(!empty($FILTER["clienttextsearch"])) {
            $Query->addFilter('%CLIENT.NAME',$FILTER["clienttextsearch"]);
        }

        if(!empty($FILTER["projectidsearch"])) {
            $Query->addFilter('PROJECT.ID',$FILTER["projectidsearch"]);
            $FILTER["projecttextsearch"] = '';
        }

        if(!empty($FILTER["projecttextsearch"])) {
            $Query->addFilter('%PROJECT.UF_NAME',$FILTER["projecttextsearch"]);
        }

        if(!empty($FILTER["taskidsearch"])) {
            $Query->addFilter('TASK.ID',$FILTER["taskidsearch"]);
            $FILTER["tasktextsearch"] = '';
        }

        if(!empty($FILTER["tasktextsearch"])) {
            $Query->addFilter('%TASK.UF_NAME',$FILTER["tasktextsearch"]);
            $Query->addFilter('TASK.UF_ACTIVE',1);
        }

        $resNoLimit = $Query->exec()->fetchAll();

        // for debug!
        //echo "<pre>";
       //echo $Query->getQuery();
        //echo "</pre>";

        if (!$resNoLimit) return $this->arResult;
        $arResult["TOTAL"] = count($resNoLimit);

        $Query->setOffset($arParams['OFFSET']);
        $Query->setLimit($arParams['COUNT']);

        // TODO AKULA убрать вариант сортировки для отладки
        //для отладки
        //$Query->setOrder(["UF_PLANNE_DATE"=>'desc',"TASK.UF_PUBLISH_DATE"=>'asc']);
        // правильный вариант
        //$Query->setOrder(["UF_PLANNE_DATE"=>'asc',"TASK.UF_PUBLISH_DATE"=>'asc']);
        $Query->setOrder(["UF_CREATE_DATE"=>'desc',"TASK.UF_PUBLISH_DATE"=>'desc']);

        $res = $Query->exec()->fetchAll();

        //\Dbg::print_r(\Bitrix\Main\Entity\Query::getLastQuery());

        // for debug!
        //echo "<pre>";
        //echo $Query->getQuery();
        //echo "</pre>";
		
        // for debug
        //$this->arResult['SQL'] = \Bitrix\Main\Entity\Query::getLastQuery();

        unset($Query);

        if (!$res) return $this->arResult;

        $sqlfilter = array_column($res,'ID');
        $select = $runnerManager->getSelectFields();
        $HLBClass = \Bitrix\Kabinet\taskrunner\datamanager\FulfillmentTable::class;
        $Queue = $HLBClass::getlist([
            'select'=>$select,
            'filter'=>['ID'=>$sqlfilter],
            //'order' => ['ID'=>'DESC'],
        ])->fetchAll();

        $arResult["RUNNER_DATA"] = $runnerManager->remakeFulfiData($Queue);
        $arrayTaskID = array_unique(array_column($this->arResult["RUNNER_DATA"],'UF_TASK_ID'));
		
		// for debug
		//\Dbg::print_r($arResult["RUNNER_DATA"]);

		// for debug
        //$this->arResult['sqlfilter'] =$sqlfilter;

        $clientsID = array_unique(array_column($res,'UF_AUTHOR_ID'));

        // переупаковываем массив, что бы было удобней обращаться
        $arResult["TASK_DATA"] = $this->covertArray($taskManager->getData(true,$clientsID));
        // переупаковываем массив, что бы было удобней обращаться
        $arResult["CLIENT_DATA"] = $this->covertArray($ClientManager->getData([], ['ID'=>$clientsID]));
        // переупаковываем массив, что бы было удобней обращаться
        $arResult["PROJECT_DATA"] = $this->covertArray($projectManager->getData(true,$clientsID));

		foreach($clientsID as $id){
            $arResult["ORDER_DATA"][$id] = $projectManager->orderData($id);
		}

		foreach($arResult["RUNNER_DATA"] as $item){
			$arResult["MESSAGE_DATA"][$item['ID']] = $messanger->getData(
			    $filter_ = [
			        'UF_QUEUE_ID'=>$item['ID'],
                    //'!UF_TYPE' => \Bitrix\Kabinet\messanger\Messanger::SYSTEM_MESSAGE
                ],
                $offset_ = 0,
                $limit_ = $arParams['MESSAGE_COUNT']
            );
		}

    }

    public function loadmoreAction()
    {
        $arParams = &$this->arParams;
        $post = $this->request->getPostList()->toArray();
        $arParams['COUNT'] = $post['countview'];
        $arParams['OFFSET'] = $post['OFFSET'];
        $arParams['FILTER'] = $this->request->getPostList()->toArray();

        // Делаем запрос со сдвигом OFFSET
        $this->prepareData();


        if ($this->hasErrors())
        {
            $this->errorCollection[] = new Error('Ошибка в запросе loadmore!',1);
            if ($this->hasErrors())
                return null;
        }

        return $this->arResult;
    }

    /* signed params*/
    protected function listKeysSignedParameters()
    {
        return [
            ''
        ];
    }

    public function configureActions()
    {
        //если действия не нужно конфигурировать, то пишем просто так. И будет конфиг по умолчанию
        return [
            'loadmore' => [
                'prefilters' => [
                    //new ActionFilter\Authentication(),
                    new ActionFilter\HttpMethod(
                        array(ActionFilter\HttpMethod::METHOD_GET, ActionFilter\HttpMethod::METHOD_POST)
                    ),
                    new ActionFilter\Csrf(),
                    new \Bitrix\Kabinet\Engine\ActionFilter\Groupmanager()
                ]
            ]

        ];
    }

    /**
     * Return true if errors exist.
     *
     * @return bool
     */
    protected function hasErrors()
    {
        return (bool)count($this->errorCollection);
    }

    /**
     * Errors processing depending on error codes.
     *
     * @return bool
     */
    protected function processErrors()
    {
        if (!empty($this->errorCollection))
        {
            /** @var Error $error */
            foreach ($this->errorCollection as $error)
            {
                $code = $error->getCode();

                if ($code == self::ERROR_404)
                {
                    if ($this->arParams['SHOWMESSAGE_404'] === 'Y')
                        Tools::process404(
                            trim($this->arParams['MESSAGE_404']) ?: $error->getMessage(),
                            true,
                            $this->arParams['SET_STATUS_404'] === 'Y',
                            $this->arParams['SHOW_404'] === 'Y',
                            $this->arParams['FILE_404']
                        );
                }
                elseif ($code == self::ERROR_TEXT)
                {
                    ShowError($error->getMessage());
                }
            }
        }

        return false;
    }

    /**
     * Getting array of errors.
     * @return Error[]
     */
    public function getErrors()
    {
        return $this->errorCollection->toArray();
    }

    /**
     * Getting once error with the necessary code.
     * @param string $code Code of error.
     * @return Error
     */
    public function getErrorByCode($code)
    {
        return $this->errorCollection->getErrorByCode($code);
    }
    
}