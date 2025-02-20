<?
use Bitrix\Main,
    Bitrix\Main\Error,
    Bitrix\Main\Localization\Loc,
    Bitrix\Main\ErrorCollection,
    Bitrix\Main\Engine\ActionFilter,
    Bitrix\Main\Loader;

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) die();


Loc::loadMessages(__FILE__);

class pageHelpComponent extends \CBitrixComponent implements \Bitrix\Main\Engine\Contract\Controllerable, \Bitrix\Main\Errorable
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
        //$request = $this->request;
        //$post = $this->request->getPostList()->toArray();

        if (empty($params['CODE'])) $this->errorCollection[] = new Error('Поле CODE не задано!',self::ERROR_TEXT);

        return $params;
    }

    public function executeComponent()
    {
        $this->prepareData();

        if ($this->hasErrors())
        {
            return $this->processErrors();
        }

        $this->includeComponentTemplate($this->template);

        return true;
    }

    public function prepareData(){
        $arParams = $this->arParams;


        $HLBClass = (\KContainer::getInstance())->get('HELP_HL');
        $data = $HLBClass::getlist([
            'select'=>['*'],
            'filter'=>['UF_CODE'=>$arParams['CODE']]
        ])->fetch();

        if (!$data){
            $this->errorCollection[] = new Error('Текста помощи с кодом '.$arParams['CODE'].' для страницы не найдено!',self::ERROR_TEXT);
            return false;
        }

        $this->arResult['DATA'] = $data;
    }

    public function differentAction()
    {
        /*
        $output = [];

        $this->errorCollection[] = new Error('Нет данных по клиентам!');
        if ($this->hasErrors())
            return null;


        return $output;
        */
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
            'different' => [
                'prefilters' => [
                    new ActionFilter\Authentication(),
                    new ActionFilter\HttpMethod(
                        array(ActionFilter\HttpMethod::METHOD_GET, ActionFilter\HttpMethod::METHOD_POST)
                    ),
                    new ActionFilter\Csrf(),
                    //new \Bitrix\Kabinet\Engine\ActionFilter\Groupmanager()
                ]
            ],
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