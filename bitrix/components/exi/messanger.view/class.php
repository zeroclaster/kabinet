<?
use Bitrix\Main,
    Bitrix\Main\Error,
    Bitrix\Main\Localization\Loc,
    Bitrix\Main\ErrorCollection,
    Bitrix\Main\Engine\ActionFilter,
    Bitrix\Main\Loader;

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) die();

Loc::loadMessages(__FILE__);

class MessangerViewComponent extends \CBitrixComponent implements \Bitrix\Main\Engine\Contract\Controllerable, \Bitrix\Main\Errorable
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
        $user = (\KContainer::getInstance())->get('user');

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
		
		if (empty($params['COUNT'])) $params['COUNT'] = 5;
        $params['OFFSET'] = 0;

        if (empty($params['NEW_RESET']) || $params['NEW_RESET'] == 'Y') {
            $params['NEW_RESET'] = 'y';
        }else{
            $params['NEW_RESET'] = 'n';
        }


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
        $sL = \Bitrix\Main\DI\ServiceLocator::getInstance();
        $messanger = $sL->get('Kabinet.Messanger');

        $arResult["MESSAGE_DATA"] = $messanger->getData(
            $filter = $arParams["FILTER"],
            $offset=0,
            $limit=$arParams['COUNT'],
            $clear=true,
            $new_reset=$arParams['NEW_RESET']
        );
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