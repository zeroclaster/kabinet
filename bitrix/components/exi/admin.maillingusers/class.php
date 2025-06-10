<?
use Bitrix\Main,
    Bitrix\Main\Error,
    Bitrix\Main\Localization\Loc,
    Bitrix\Main\ErrorCollection,
    Bitrix\Main\Engine\ActionFilter,
    Bitrix\Main\Loader;
use Bitrix\Main\SystemException;

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) die();

Loc::loadMessages(__FILE__);

class maillingusersComponent extends \CBitrixComponent implements \Bitrix\Main\Engine\Contract\Controllerable, \Bitrix\Main\Errorable
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

        if (empty($params['COUNT'])) $params['COUNT'] = 10;
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
        $arResult = &$this->arResult;

        $FILTER = $arParams['FILTER'];

        $ClassClient = \Bitrix\Main\UserTable::class;
        $Query = $ClassClient::query();
        //$Query->addFilter('>UF_TELEGRAM_ID',0);


        if ($FILTER){
            foreach ($FILTER as $fieldName => $value){
               $Query->addFilter($fieldName,$value);
            }
        }


        $Query_ = clone $Query;
        $Query_->addSelect(new \Bitrix\Main\Entity\ExpressionField('CNT', 'COUNT(ID)'));

        /*
        $this->errorCollection[] = new Error(print_r($arParams,true),1);
        if ($this->hasErrors())
            return null;
        */

        $res = $Query_->exec()->fetch();

        $arResult["TOTAL"] = $res['CNT'];

        //echo \Bitrix\Main\Entity\Query::getLastQuery();

        // for debug!
        //echo "<pre>";
        //print_r($res);
        //echo "</pre>";

        $Query->setSelect([
            'ID',
            'TIMESTAMP_X',
            'LOGIN',
            'NAME',
            'LAST_NAME',
            'EMAIL',
            'DATE_REGISTER',
            'SECOND_NAME',
            'PERSONAL_PHOTO',
            'PERSONAL_PHONE',
            'UF_TELEGRAM_ID',
            'UF_TELEGRAM_NOTFI',
            'UF_TELEGRAM_CHAT_ID',
            'UF_EMAIL_NOTIFI'
        ]);
        $Query->setOrder(["ID"=>'desc']);
        $Query->setOffset($arParams['OFFSET']);
        $Query->setLimit($arParams['COUNT']);
        $res = $Query->exec()->fetchAll();

       // echo \Bitrix\Main\Entity\Query::getLastQuery();

        // for debug!
        //echo "<pre>";
       // print_r($res);
        //echo "</pre>";

        foreach ($res as $key => $data){
            $res[$key]["FIO"] = current(array_filter([
                trim(implode(" ", [$data['LAST_NAME'], $data['NAME'], $data['SECOND_NAME']])),
                $data['LOGIN']
            ]));

            $arUF = $GLOBALS["USER_FIELD_MANAGER"]->GetUserFields("USER", $data['ID']);

                $userFieldEnum = new \CUserFieldEnum();
                $vallist = $userFieldEnum->GetList([], ['USER_FIELD_ID' => $arUF['UF_EMAIL_NOTIFI']['ID']]);
                $value = [];
                while($item = $vallist->Fetch())
                {
                    $value[] = $item;
                }

                $res[$key]['USER_FIELD_ID_ORIGINAL'] =  $value;

        }

        $arResult["DATA"] = $res;

    }

    public function loadmoreAction()
    {
        $arParams = &$this->arParams;
        $post = $this->request->getPostList()->toArray();
        $arParams['COUNT'] = $post['countview'];
        $arParams['OFFSET'] = $post['OFFSET'];
        $arParams['FILTER'] = $this->request->getPostList()->toArray();
        unset($arParams['FILTER']['countview']);
        unset($arParams['FILTER']['OFFSET']);

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

    public function saveuserAction(){

        $request = $this->request;
        $post = $request->getPostList()->toArray();
        $files = $request->getFileList()->toArray();

        $clientManager = \Bitrix\Main\DI\ServiceLocator::getInstance()->get('Kabinet.Client');

        try {
            $upd_id = $clientManager->update(array_merge($post,$files));
        }catch (SystemException $exception){
            $this->errorCollection[] = new Error($exception->getMessage(),1);
            return null;
        }

        $userData = \Bitrix\Main\UserTable::getlist([
            'select'=>[
                'ID',
                'TIMESTAMP_X',
                'LOGIN',
                'NAME',
                'LAST_NAME',
                'EMAIL',
                'DATE_REGISTER',
                'SECOND_NAME',
                'PERSONAL_PHOTO',
                'PERSONAL_PHONE',
                'UF_TELEGRAM_ID',
                'UF_TELEGRAM_NOTFI',
                'UF_TELEGRAM_CHAT_ID',
                'UF_EMAIL_NOTIFI'
            ],
            'filter'=>['ID'=>$upd_id],
            'limit'=>1
        ])->fetch();

        $userData["FIO"] = current(array_filter([
            trim(implode(" ", [$userData['LAST_NAME'], $userData['NAME'], $userData['SECOND_NAME']])),
            $userData['LOGIN']
        ]));

        if($userData["UF_TELEGRAM_NOTFI"] == "1") $userData["UF_TELEGRAM_NOTFI"] = true;
        else $userData["UF_TELEGRAM_NOTFI"] = false;

        return [
            'id'=> $upd_id,
            'fields'=>$userData,
            'message'=>'Данные успешно обновлены!'
        ];
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
            ],
            'saveuser' => [
                'prefilters' => [
                    //new ActionFilter\Authentication(),
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