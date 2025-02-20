<?
use Bitrix\Main,
    Bitrix\Main\Error,
    Bitrix\Main\Localization\Loc,
    Bitrix\Main\ErrorCollection,
    Bitrix\Main\Engine\ActionFilter,
    Bitrix\Main\Loader;

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) die();

Loc::loadMessages(__FILE__);

class TaskListComponent extends \CBitrixComponent implements \Bitrix\Main\Engine\Contract\Controllerable, \Bitrix\Main\Errorable
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
        // нужно только для отладки для измерения времени

        $sL = \Bitrix\Main\DI\ServiceLocator::getInstance();
        $taskManager = $sL->get('Kabinet.Task');
        $messanger = $sL->get('Kabinet.Messanger');
        $user = (\KContainer::getInstance())->get('user');
        $user_id = $user->get('ID');
        $saveData = $taskManager->getData();

        $arResult['TASK_ALERT'] = [];
        foreach ($saveData as $key => $item) {
            $userMessage = $messanger->getData(
                $filter = [
                    'UF_TASK_ID' => $item['ID'],
                    'UF_TYPE' => \Bitrix\Kabinet\messanger\Messanger::SYSTEM_MESSAGE,
                    'UF_STATUS' => \Bitrix\Kabinet\messanger\Messanger::NEW_MASSAGE,
                    'UF_TARGET_USER_ID' => $user_id,
                ],
                $offset = 0,
                $limit = 5000,
                $clear = false,
                $new_reset = 'n'
            );

            $arResult['TASK_ALERT'][$item['ID']] = count($userMessage);
        }
/*
        $this->arResult['ITEMS'] = $saveData;
*/
    }

    public function doitAction_()
    {
        //$name = $this->arParams['QUERY_VARIABLE'];
        $output = "";

        $user = (\KContainer::getInstance())->get('user');
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