<?
use Bitrix\Main,
    Bitrix\Main\SystemException,
    Bitrix\Main\Error,
    Bitrix\Main\Localization\Loc,
    Bitrix\Main\ErrorCollection,
    Bitrix\Main\Engine\ActionFilter,
    Bitrix\Main\Loader;

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) die();

Loc::loadMessages(__FILE__);

class ReportsListComponent extends \CBitrixComponent implements \Bitrix\Main\Engine\Contract\Controllerable, \Bitrix\Main\Errorable
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

        //if (empty($params['TASK_ID'])) $this->errorCollection[] = new Error('Поле TASK_ID не задано!',self::ERROR_TEXT);

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

		if (!$params['TASK_ID']) $this->errorCollection[] = new Error("TASK_ID не найден.",self::ERROR_TEXT);
		else {
            $sL = \Bitrix\Main\DI\ServiceLocator::getInstance();
            $taskManager = $sL->get('Kabinet.Task');
            $taskdata = $taskManager->getData();

            $key = array_search($params['TASK_ID'], array_column($taskdata, 'ID'));
            if ($key === false) $this->errorCollection[] = new Error("Страница не найдена.",self::ERROR_404);
        }

        $params["FILTER"]['TASK_ID'] = $params['TASK_ID'];

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

        //отправка формы согласовать все
		$post = $this->request->getPostList()->toArray();
		if ($post['greeeverything'] == 'y') $this->greeeverything();

        if ($this->hasErrors())
        {
            return $this->processErrors();
        }
		
		$this->prepareData();
        $this->includeComponentTemplate($this->template);

        return true;
    }
	
	public function greeeverything(){
		$arParams = $this->arParams;
        $arResult = &$this->arResult;
		$TASK_ID = $arParams['TASK_ID'];
		
		$sL = \Bitrix\Main\DI\ServiceLocator::getInstance();
		$runnerManager = $sL->get('Kabinet.Runner');
		
		// Требуют внимания
		// [3,5,8]
		$alert_status_client = $runnerManager->config('ALERT');
		
		$runner = $runnerManager->getData(
		        $TASK_ID,
				$clear=true,
				$id=[],
				$filter=['UF_STATUS'=>$alert_status_client]
		);

        try {
            foreach ($runner as $item) {
                $state = $runnerManager->makeState($item);
                $state->runCommand("auto_walk");
            }
        }catch (SystemException $exception){
            $error = $exception->getMessage();
            $this->errorCollection[] = new Error($error,self::ERROR_TEXT);
        }

        $arResult['note'] = "Все исполнения успешно согласованы!";
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

        $this->arResult["RUNNER_DATA"] = [];

        $sL = \Bitrix\Main\DI\ServiceLocator::getInstance();
        $runnerManager = $sL->get('Kabinet.Runner');
        $messanger = $sL->get('Kabinet.Messanger');

        $HLBClassTask = \Bitrix\Main\DI\ServiceLocator::getInstance()->get('TASK_HL');
        $HLBClassFulf = \Bitrix\Main\DI\ServiceLocator::getInstance()->get('FULF_HL');

        $Query = $HLBClassFulf::query();
        $entity = $Query->getEntity();

        $Query->setSelect([
            'ID',
            'UF_AUTHOR_ID'=>'TASK.UF_AUTHOR_ID'
        ]);

        // Привязываем задачу
        $Query->registerRuntimeField('TASK',
            [
                'data_type' => $HLBClassTask,
                'reference' => ['=this.UF_TASK_ID' => 'ref.ID'],
                'join_type' => 'LEFT'
            ]
        );


		// $arParams["FILTER"]['TASK_ID'] передается из вызова компонента
        if ($arParams["FILTER"]['id']){
            $Query->addFilter('ID', $arParams["FILTER"]['id']);
        }else {
            $Query->addFilter('TASK.ID', $arParams["FILTER"]['TASK_ID']);
        }

        $Query->addFilter('TASK.UF_ACTIVE',1);

        // Дата публикации
        if(!empty($FILTER['fromdate1']))  $Query->addFilter('>UF_PLANNE_DATE',$FILTER['fromdate1']);
        if(!empty($FILTER['todate1'])) $Query->addFilter('<UF_PLANNE_DATE',$FILTER['todate1']);

        if(isset($FILTER['statusfind']) && is_numeric($FILTER['statusfind'])) $Query->addFilter('UF_STATUS',$FILTER['statusfind']);

        if(!empty($FILTER['queue_id'])) $Query->addFilter('ID',$FILTER['queue_id']);

		
        $Query->setOrder(["UF_CREATE_DATE"=>'desc',"TASK.UF_PUBLISH_DATE"=>'desc']);

        $resNoLimit = $Query->exec()->fetchAll();

        // for debug!
        //\Dbg::print_R(\Bitrix\Main\Entity\Query::getLastQuery());
        //echo "<pre>";
        //echo $Query->getQuery();
        //echo "</pre>";

        if (!$resNoLimit) return $this->arResult;
        $arResult["TOTAL"] = count($resNoLimit);

        $Query->setOffset($arParams['OFFSET']);
        $Query->setLimit($arParams['COUNT']);

        $res = $Query->exec()->fetchAll();

        // for debug
        //$this->arResult['SQL'] = \Bitrix\Main\Entity\Query::getLastQuery();

        unset($Query);

        if (!$res) return $this->arResult;

        $sqlfilter = array_column($res,'ID');
        $arResult["RUNNER_DATA"] = $runnerManager->getData([],true,$sqlfilter);

        foreach($arResult["RUNNER_DATA"] as $item){
            $arResult["MESSAGE_DATA"][$item['ID']] = $messanger->getData(['UF_QUEUE_ID'=>$item['ID']],0,$arParams['MESSAGE_COUNT']);
        }

    }

    public function loadmoreAction()
    {
        $arParams = &$this->arParams;
        $post = $this->request->getPostList()->toArray();
        $arParams['COUNT'] = $post['countview'];
        $arParams['OFFSET'] = $post['OFFSET'];
        $arParams['FILTER'] = $this->request->getPostList()->toArray();

        if ($this->hasErrors()) return null;

        // Делаем запрос со сдвигом OFFSET
        $this->prepareData();

        if ($this->hasErrors()) return null;

        return $this->arResult;
    }

    /* signed params*/
    protected function listKeysSignedParameters()
    {
        return [
            'TASK_ID'
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
                    //new \Bitrix\Kabinet\Engine\ActionFilter\Groupmanager()
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
                        \Bitrix\kabinet\Component\Tools::process404(
                            trim($this->arParams['MESSAGE_404']) ?: $error->getMessage(),
                            true,
                            $this->arParams['SET_STATUS_404'] === 'Y',
                            $this->arParams['SHOW_404'] === 'Y',
                            $this->arParams['FILE_404']
                        );
                    if ($this->arParams['REDIRECT_404'] === 'Y') LocalRedirect("/404.php");
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