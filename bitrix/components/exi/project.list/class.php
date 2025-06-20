<?
use Bitrix\Main,
    Bitrix\Main\Error,
    Bitrix\Main\Localization\Loc,
    Bitrix\Main\ErrorCollection,
    Bitrix\Main\Engine\ActionFilter,
    Bitrix\Main\Loader;

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) die();

Loc::loadMessages(__FILE__);

class ProjectListComponent extends \CBitrixComponent implements \Bitrix\Main\Engine\Contract\Controllerable, \Bitrix\Main\Errorable
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

        if(\PHelp::isAdmin() && empty($request['usr'])) {
            $this->errorCollection[] = new Error('Ошибка в адресе запроса!',self::ERROR_TEXT);
        }

        return $params;
    }

    public function executeComponent()
    {
        if ($this->hasErrors())
        {
            return $this->processErrors();
        }

        $start = memory_get_usage();
        $this->doitAction();
        $middle = memory_get_usage();
		//Dbg::echo_($middle - $start);


        $this->includeComponentTemplate($this->template);

        return true;
    }

    public function prepareData(){

    }

    public function doitAction()
    {
        global $CACHE_MANAGER;

        $arParams = $this->arParams;
        $arResult = &$this->arResult;

        $sL = \Bitrix\Main\DI\ServiceLocator::getInstance();
        $projectManager = $sL->get('Kabinet.Project');
        $messanger = $sL->get('Kabinet.Messanger');
        $billing = $sL->get('Kabinet.Billing');

        $user = \Bitrix\Main\DI\ServiceLocator::getInstance()->get('user');
        $user_id = $user->get('ID');

        $saveData = $projectManager->getData();

        $cacheId = count($saveData);
        $cacheId = SITE_ID."|".$cacheId;
        $cacheId .= "|".serialize($user_id);
        $cache = new \CPHPCache;

        // Clear cache "task_data"
        //$CACHE_MANAGER->ClearByTag("projectlist123_data");
        //$cache->clean($cacheId, "kabinet/projectlist123");

        // сколько времени кешировать
        $ttl = 0; //300;	// 5 минут
        $cache = new \CPHPCache;

        if ($cache->StartDataCache($ttl, $cacheId, "kabinet/projectlist123"))
        {
            if (defined("BX_COMP_MANAGED_CACHE"))
            {
                $CACHE_MANAGER->StartTagCache("projectlist123_data");
                //\CIBlock::registerWithTagCache(self::SERVICES_IBLOCK);
            }


        $BILLING_DATA = $billing->getData();
        if ($BILLING_DATA['UF_VALUE']>0) {

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


            // Инициализация сервисов и данных
            $projectIds = array_column($saveData, 'ID');
            $messageFilter = [
                'UF_PROJECT_ID' => $projectIds,
                'UF_TYPE' => \Bitrix\Kabinet\messanger\Messanger::SYSTEM_MESSAGE,
                'UF_STATUS' => \Bitrix\Kabinet\messanger\Messanger::NEW_MASSAGE,
                'UF_TARGET_USER_ID' => $user_id,
                '=UF_ACTIVE' => true
            ];

            // 1. Подсчет уведомлений по проектам
            $projectAlertsQuery = new \Bitrix\Main\Entity\Query(
                \Bitrix\Kabinet\messanger\datamanager\LmessangerTable::getEntity()
            );
            $projectAlertsQuery
                ->setSelect([
                    'UF_PROJECT_ID',
                    'CNT' => new \Bitrix\Main\Entity\ExpressionField('CNT', 'COUNT(*)')
                ])
                ->setFilter($messageFilter)
                ->setGroup(['UF_PROJECT_ID'])
                ->setCacheTtl(3600);

            $arResult['ALERT_PROJECT_COUNT'] = array_fill_keys($projectIds, 0);
            $projectAlertsResult = $projectAlertsQuery->exec();
            while ($row = $projectAlertsResult->fetch()) {
                $arResult['ALERT_PROJECT_COUNT'][$row['UF_PROJECT_ID']] = (int)$row['CNT'];
            }

            // 2. Подсчет уведомлений по задачам
            $taskAlertsQuery = new \Bitrix\Main\Entity\Query(
                \Bitrix\Kabinet\messanger\datamanager\LmessangerTable::getEntity()
            );
            $taskAlertsQuery
                ->setSelect([
                    'UF_TASK_ID',
                    'CNT' => new \Bitrix\Main\Entity\ExpressionField('CNT', 'COUNT(*)')
                ])
                ->setFilter($messageFilter)
                ->setGroup(['UF_TASK_ID'])
                ->setCacheTtl(3600);

            $arResult['TASK_ALERT'] = [];
            $taskAlertsResult = $taskAlertsQuery->exec();
            while ($row = $taskAlertsResult->fetch()) {
                $arResult['TASK_ALERT'][$row['UF_TASK_ID']] = (int)$row['CNT'];
            }

            // 3. Инициализация нулевых значений для задач
            foreach ($saveData as $item) {
                $arResult['TASK_ALERT'][$item['ID']] = $arResult['TASK_ALERT'][$item['ID']] ?? 0;
            }


        foreach ($saveData as $key => $item) {
            [$mouthStart,$mouthEnd] = \PHelp::nextMonth();
            $arResult['NEXT_MONTH_EXPENSES'][] = [
                'PROJECT_ID'=>$item['ID'],
                'VALUE'=>$billing->nextMonthExpenses($item['ID']),
                'MONTH_START' =>$mouthStart->format("d.m.Y"),
                'MONTH_END' =>$mouthEnd->format("d.m.Y"),
            ];

            [$mouthStart,$mouthEnd] = \PHelp::actualMonth();
            $arResult['ACTUAL_MONTH_EXPENSES'][] = [
                'PROJECT_ID'=>$item['ID'],
                'VALUE'=>$billing->actualMonthExpenses($item['ID']),
                'MONTH_START' =>$mouthStart->format("d.m.Y"),
                'MONTH_END' =>$mouthEnd->format("d.m.Y"),
                'MONTH'=> date(     \PHelp::monthName($mouthEnd->format("n")) . ' Y' ),
            ];
            $arResult['ACTUAL_MONTH_BUDGET'][] = [
                'PROJECT_ID'=>$item['ID'],
                'VALUE'=>$billing->actualMonthBudget($item['ID']),
                'MONTH_START' =>$mouthStart->format("d.m.Y"),
                'MONTH_END' =>$mouthEnd->format("d.m.Y"),
                'MONTH'=> date(     \PHelp::monthName($mouthEnd->format("n")) . ' Y' ),
            ];

            [$mouthStart,$mouthEnd] = \PHelp::lastMonth();
            $arResult['LAST_MONTH_EXPENSES'][] = [
                'PROJECT_ID'=>$item['ID'],
                'VALUE'=>$billing->lastMonthExpenses($item['ID']),
                'MONTH_START' =>$mouthStart->format("d.m.Y"),
                'MONTH_END' =>$mouthEnd->format("d.m.Y"),
                'MONTH'=> date(     \PHelp::monthName($mouthEnd->format("n")) . ' Y' ),
            ];
        }

        $arResult['MESSAGE_DATA'] = [];
        foreach ($saveData as $key => $item) {
            $arResult["MESSAGE_DATA"][$item['ID']] = $messanger->getData(
                $filter = [
                    'UF_PROJECT_ID' => $item['ID'],
                    '!UF_TYPE' => \Bitrix\Kabinet\messanger\Messanger::SYSTEM_MESSAGE,
                ],
                $offset = 0,
                $limit = 5,
                $clear = false,
                $new_reset = 'n'
            );
        }

        //\Dbg::print_r($arResult['MESSAGE_DATA']);

        $arResult['ITEMS'] = $saveData;

            if (defined("BX_COMP_MANAGED_CACHE")) $CACHE_MANAGER->EndTagCache();
            $cache->EndDataCache(array(
                $arResult['FUTURE_SPENDING'],
                $arResult['ALERT_PROJECT_COUNT'],
                $arResult['NEXT_MONTH_EXPENSES'],
                $arResult['ITEMS'],
                $arResult['TASK_ALERT'],
                $arResult['ACTUAL_MONTH_EXPENSES'],
                $arResult['LAST_MONTH_EXPENSES'],
                $arResult["MESSAGE_DATA"],
                $arResult['ACTUAL_MONTH_BUDGET'],
            ));
        }
        else
        {
            $vars = $cache->GetVars();
            $arResult['FUTURE_SPENDING'] = $vars[0];
            $arResult['ALERT_PROJECT_COUNT'] = $vars[1];
            $arResult['NEXT_MONTH_EXPENSES'] = $vars[2];
            $arResult['ITEMS'] = $vars[3];
            $arResult['TASK_ALERT'] = $vars[4];
            $arResult['ACTUAL_MONTH_EXPENSES'] = $vars[5];
            $arResult['LAST_MONTH_EXPENSES'] = $vars[6];
            $arResult["MESSAGE_DATA"]= $vars[7];
            $arResult['ACTUAL_MONTH_BUDGET']= $vars[8];
        }

    }

    public function doitAction_()
    {
        //$name = $this->arParams['QUERY_VARIABLE'];
        $output = "";

        $user = \Bitrix\Main\DI\ServiceLocator::getInstance()->get('user');
        $proj = \Bitrix\Kabinet\project\datamanager\ProjectsTable::getListActive([
            'select'=>['*','INFO','DETAILS','TARGETAUDIENCE'],
            'filter'=>['UF_AUTHOR_ID'=>$user->get('ID')],
            'order'=>["UF_PUBLISH_DATE"=>'DESC']
        ])->fetchCollection();
        //echo \Bitrix\Main\Entity\Query::getLastQuery();

        $this->arResult['ITEMS'] = $proj;

        //$this->errorCollection[] = new Error('You are so beautiful or so handsome');
        if ($this->hasErrors())
        return null;

        /*
        $errors = $result->getErrorMessages();
        $this->errorCollection[] = new Error($errors);
        return null;
        */

        return $output;
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
            'doit' => [
                'prefilters' => [
                    //new ActionFilter\Authentication(),
                    new ActionFilter\HttpMethod(
                        array(ActionFilter\HttpMethod::METHOD_GET, ActionFilter\HttpMethod::METHOD_POST)
                    ),
                    new ActionFilter\Csrf(),
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