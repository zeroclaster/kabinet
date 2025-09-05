<?
use Bitrix\Main,
    Bitrix\Main\Error,
    Bitrix\Main\Localization\Loc,
    Bitrix\Main\ErrorCollection,
    Bitrix\Main\Engine\ActionFilter,
    Bitrix\Main\Loader;

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) die();

Loc::loadMessages(__FILE__);

class AdminclientListComponent extends \CBitrixComponent implements \Bitrix\Main\Engine\Contract\Controllerable, \Bitrix\Main\Errorable
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

		if (empty($params['COUNT'])) $params['COUNT'] = 2;
        $params['OFFSET'] = 0;

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

    public function prepareData(){
        global $DB;
		$arParams = $this->arParams;
		$FILTER = $arParams['FILTER'];
        $this->arResult["CLIENT_DATA"] = [];
        $this->arResult["PROJECT_DATA"] = [];
        $this->arResult["TASK_DATA"] = [];
        $this->arResult["ORDER_DATA"] = [];
        $this->arResult["RUNNER_DATA"] = [];
        $this->arResult["TOTAL"] = 0;

        $sL = \Bitrix\Main\DI\ServiceLocator::getInstance();
		$ClientManager = $sL->get('Kabinet.Client');
		$projectManager = $sL->get('Kabinet.Project');
		$taskManager = $sL->get('Kabinet.Task');
		$runnerManager = $sL->get('Kabinet.Runner');

		$ClassClient = \Bitrix\Kabinet\UserTable::class;
        $HLBClassProject = \Bitrix\Main\DI\ServiceLocator::getInstance()->get('BRIEF_HL');
        $HLBClassTask = \Bitrix\Main\DI\ServiceLocator::getInstance()->get('TASK_HL');


        $Query = $ClassClient::query();
        $entity = $Query->getEntity();
        $connection = $entity->getConnection();
        $Query->setSelect([
            'ID',
            //'TIMESTAMP_X',
            //'LOGIN',
            //'NAME',
            //'LAST_NAME',
            //'EMAIL',
            //'DATE_REGISTER',
            //'SECOND_NAME',
            //'PERSONAL_PHOTO',
            //'PERSONAL_PHONE',
            //'PROJECT.*',
            //'TASK.*',
        ]);
        // ИСПРАВЛЕНИЕ: получаем полное имя сущности вместо объекта
        $Query->registerRuntimeField('PROJECT',
            [
                'data_type' => $HLBClassProject::getEntity()->getFullName(),
                'reference' => ['=this.ID' => 'ref.UF_AUTHOR_ID'],
                'join_type' => 'LEFT'
            ]
        );

        // ИСПРАВЛЕНИЕ: получаем полное имя сущности вместо объекта
        $Query->registerRuntimeField('TASK',
            [
                'data_type' => $HLBClassTask::getEntity()->getFullName(),
                'reference' => ['=this.ID' => 'ref.UF_AUTHOR_ID'],
                'join_type' => 'LEFT'
            ]
        );
        $Query->addFilter('UF_GROUP_REF.GROUP_ID',REGISTRATED);
        $Query->addFilter('>PROJECT.ID',0);
        $Query->addFilter('PROJECT.UF_ACTIVE',1);

        $Query->setOrder(["TASK.UF_PUBLISH_DATE"=>'desc',"PROJECT.UF_PUBLISH_DATE"=>'desc']);

        if(!empty($FILTER["clientidsearch"])) {
            $Query->addFilter('ID',$FILTER["clientidsearch"]);
            $FILTER["clienttextsearch"] = '';
        }

        if(!empty($FILTER["clienttextsearch"])) {
            $Query->addFilter('%NAME',$FILTER["clienttextsearch"]);
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


        $sqlNoLimit = $Query->getQuery();
        $Query->setOffset($arParams['OFFSET']);
        $Query->setLimit($arParams['COUNT']);

        $sql = $Query->getQuery();

        // for debug!
        //echo "<pre>";
        //echo $sqlNoLimit;
        //echo "</pre>";

        $mutatQuery = str_replace(
            'ORDER BY',
            "GROUP BY `kabinet_user`.`ID` ORDER BY",
            $sql
        );

        // for debug!
        //echo "<pre>";
        //echo $mutatQuery;
        //echo "</pre>";

        $result = $connection->query($mutatQuery);
        $res = $result->fetchAll();

        $mutatQuery = str_replace(
            'ORDER BY',
            "GROUP BY `kabinet_user`.`ID` ORDER BY",
            $sqlNoLimit
        );

        $result = $connection->query($mutatQuery);
        $resNoLimit = $result->fetchAll();


        // old version, bad version
        //$Query->setGroup("ID");
        //$res = $Query->exec()->fetchAll();

        unset($Query);

        if (!$res) return $this->arResult;

        $sqlfilter = array_column($res,'ID');
        $this->arResult["TOTAL"] = count($resNoLimit);

        // for debug
        //$this->arResult['SQL'] = \Bitrix\Main\Entity\Query::getLastQuery();

		//\Dbg::var_dump($sqlfilter);

		// for debug
        //$this->arResult['sqlfilter'] =$sqlfilter;

        $this->arResult["CLIENT_DATA"] = $ClientManager->getData([], ['ID'=>$sqlfilter]);

		$arrayUserID = array_column($this->arResult["CLIENT_DATA"],'ID');

        $PR_DATA = $projectManager->getData(false,[],['UF_AUTHOR_ID'=>$arrayUserID]);
		foreach($PR_DATA as $project){
			$this->arResult["PROJECT_DATA"][$project["UF_AUTHOR_ID"]][] = $project;
		}


        $Tasklist = \Bitrix\Kabinet\task\datamanager\TaskTable::getListActive([
            'select'=>['*'],
            'filter'=>[
                '!UF_STATUS'=>[
                    0,  // не запланированные
                    9, //завершеннык
                    10 //отмененные
                ],
                'UF_AUTHOR_ID'=>$arrayUserID
            ],
            'order' => ['UF_RUN_DATE'=>'ASC'],
        ])->fetchAll();
        foreach ($Tasklist as $key => $data) {
            $Tasklist[$key]['UF_DATE_COMPLETION'] = $taskManager->getItem($data)->theorDateEnd($data);
        }

        $taskL = $taskManager->remakeData($Tasklist);

		foreach($taskL as $task){
			$this->arResult["TASK_DATA"][$task["UF_AUTHOR_ID"]][] = $task;
		}


    /*
        $PR_DATA = $taskManager->getData(false,$arrayUserID,['UF_AUTHOR_ID'=>$arrayUserID,'!UF_STATUS'=>[0,9,10]]);
        foreach($PR_DATA as $task){
            $this->arResult["TASK_DATA"][$task["UF_AUTHOR_ID"]][] = $task;
        }
    */

		foreach($arrayUserID as $id_){
			$this->arResult["ORDER_DATA"][$id_] = $projectManager->orderData($id_);
		}

		foreach($taskL as $task){
			$this->arResult["RUNNER_DATA"][$task['UF_AUTHOR_ID']][$task['ID']] = $runnerManager->getTaskFulfiData($task['ID']);
		}

		//\Dbg::var_dump($this->arResult["RUNNER_DATA"]);
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