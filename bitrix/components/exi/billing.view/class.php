<?
use Bitrix\Main,
    Bitrix\Main\Error,
    Bitrix\Main\Localization\Loc,
    Bitrix\Main\ErrorCollection,
    Bitrix\Main\Engine\ActionFilter,
    Bitrix\Main\SystemException,
    Bitrix\Main\Loader;

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) die();

Loc::loadMessages(__FILE__);

class BillingViewComponent extends \CBitrixComponent implements \Bitrix\Main\Engine\Contract\Controllerable, \Bitrix\Main\Errorable
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

    public function covertArray(array $source){

        if (!$source) return [];

        $ret = [];
        foreach($source as $item){
            $ret[$item['ID']] = $item;
        }

        return $ret;
    }

    public function prepareData(){
        global $DB,$CACHE_MANAGER;
		$arParams = $this->arParams;
        $arResult = &$this->arResult;


        $sL = \Bitrix\Main\DI\ServiceLocator::getInstance();
        $billing = $sL->get('Kabinet.Billing');
        $history = $sL->get('Kabinet.BilligHistory');
        $taskManager = $sL->get('Kabinet.Task');

        $total = $history->getData(false,
            $filter = $arParams["FILTER"],
            $offset=0,
            $limit=5000000
        );

        $arResult["TOTAL"] = count($total);

        $arResult["HISTORY_DATA"] = $history->getData(false,
            $filter = $arParams["FILTER"],
            $offset=$arParams["OFFSET"],
            $limit=$arParams["COUNT"]
        );

        $arResult["BILLING_DATA"] = $billing->getData();
        $arResult['EXPENSES_NEXT_MONTH'] = 0;
        $arResult['FUTURE_SPENDING'] = '';
		$arResult['RESERVED'] = 0;
        $arResult['ACTUAL_MONTH_EXPENSES'] = 0;
        $arResult['ACTUAL_MONTH_BUDGET'] = 0;
        $arResult['RECOMMEND_UP_BALANCE'] = 0;

        $user = (\KContainer::getInstance())->get('user');
        $user_id = $user->get('ID');

		$cacheId = '';
        $cacheId = SITE_ID."|".$cacheId;
        $cacheId .= "|".serialize($user_id);
        $cache = new \CPHPCache;
		
        // Clear cache "task_data"
        //$CACHE_MANAGER->ClearByTag("nextmonthexpenses_data");
        //$cache->clean($cacheId, "kabinet/nextmonthexpenses");

		// сколько времени кешировать
		$ttl = 300;	// 5 минут
        $cache = new \CPHPCache;
		
		if ($cache->StartDataCache($ttl, $cacheId, "kabinet/nextmonthexpenses"))
        {
            if (defined("BX_COMP_MANAGED_CACHE"))
            {
                $CACHE_MANAGER->StartTagCache("nextmonthexpenses_data");
                //\CIBlock::registerWithTagCache(self::SERVICES_IBLOCK);
            }

        // Расходы на ближайший месяц
        $arResult['EXPENSES_NEXT_MONTH'] = $billing->nextMonthExpenses();

        //'UF_STATUS'=>9, // Выполненые
        $arResult['ACTUAL_MONTH_EXPENSES'] = $billing->actualMonthExpenses();

        // все без статусов
        $arResult['ACTUAL_MONTH_BUDGET'] = $billing->actualMonthBudget();

        // Рекомендуем пополнить баланс на
        $arResult['RECOMMEND_UP_BALANCE'] = $arResult['ACTUAL_MONTH_BUDGET'] - $arResult["BILLING_DATA"]['UF_VALUE'];

        if ($arResult["BILLING_DATA"]['UF_VALUE']>0) {

            [$a, $epn_, $date_, $date_2, $ret] = $billing->findDate(
                1,                                                      // для выхода от зависания
                [],                                                         // предыдущие вычисления
                new \DateTime('first day of next month'),          // дата начала месяца для прогноза
                new \DateTime('last day of next month'),            // дата конца месяца для прогноза
                ''                                                      // искомая дата
            );

            //Средств хватит до
            if ($ret)
                $arResult['FUTURE_SPENDING'] = $ret->format("d.m.Y");
            else
                $arResult['FUTURE_SPENDING'] = '';
        }
		
		$arResult['RESERVED'] = $this->reserved();
		
		
            if (defined("BX_COMP_MANAGED_CACHE")) $CACHE_MANAGER->EndTagCache();
            $cache->EndDataCache(array(
					$arResult['EXPENSES_NEXT_MONTH'],
					$arResult['FUTURE_SPENDING'],
					$arResult['RESERVED'],
                    $arResult['ACTUAL_MONTH_EXPENSES'],
                    $arResult['ACTUAL_MONTH_BUDGET'],
                    $arResult['RECOMMEND_UP_BALANCE']
					));
        }
        else
        {
            $vars = $cache->GetVars();
            $arResult['EXPENSES_NEXT_MONTH'] = $vars[0];
			$arResult['FUTURE_SPENDING'] = $vars[1];
			$arResult['RESERVED'] = $vars[2];
            $arResult['ACTUAL_MONTH_EXPENSES'] = $vars[3];
            $arResult['ACTUAL_MONTH_BUDGET'] = $vars[4];
            $arResult['RECOMMEND_UP_BALANCE'] = $vars[5];
        }		
		
    }

	public function reserved(){
		$sL = \Bitrix\Main\DI\ServiceLocator::getInstance();       
        $taskManager = $sL->get('Kabinet.Task');
		
        $user = (\KContainer::getInstance())->get('user');
        $user_id = $user->get('ID');
				
		$l = \Bitrix\Kabinet\taskrunner\datamanager\FulfillmentTable::getlist([
			'select' => ['ID','UF_TASK_ID','UF_MONEY_RESERVE','TASK.*'],
			'filter'=>[
				'UF_ACTIVE'=>1,
				'UF_STATUS'=>0,
				'TASK.UF_ACTIVE'=>1,
				'TASK.UF_AUTHOR_ID'=>$user_id,
			],
			'runtime' => [
				'TASK' => [
					'data_type' => \Bitrix\Kabinet\task\datamanager\TaskTable::class,
					'reference' => ['=this.UF_TASK_ID' => 'ref.ID',],
					'join_type' => 'INNER'
				],
			]
		])->fetchAll();

		//echo \Bitrix\Main\Entity\Query::getLastQuery();		
		
		if (!$l) return 0;
		
		$sum = 0;
		foreach($l as $item){
			$task = [];
            foreach ($item as $fieldName => $value) {
                if (strpos($fieldName,'KABINET_TASKRUNNER_DATAMANAGER_FULFILLMENT_TASK_') !== false){
                    $n = str_replace('KABINET_TASKRUNNER_DATAMANAGER_FULFILLMENT_TASK_','',$fieldName);
                    $task[$n] = $value;
                }
            }		
			$PRODUCT = $taskManager->getProductByTask($task);			
			$sum += $PRODUCT['CATALOG_PRICE_1'];
		}
		
		return $sum;
	}	

    public function loadmoreAction()
    {
        $arParams = &$this->arParams;
        $post = $this->request->getPostList()->toArray();
        $arParams['COUNT'] = $post['countview'];
        $arParams['OFFSET'] = $post['OFFSET'];
        unset($post['countview'],$post['OFFSET']);
        $arParams['FILTER'] = $post;

        // Делаем запрос со сдвигом OFFSET
        $this->prepareData();


        if ($this->hasErrors())
        {
            $this->errorCollection[] = new Error('Ошибка в запросе loadmore!');
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