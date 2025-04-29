<?
use Bitrix\Main,
    Bitrix\Main\Error,
    Bitrix\Main\Localization\Loc,
    Bitrix\Main\ErrorCollection,
    Bitrix\Main\Engine\ActionFilter,
    Bitrix\Main\Loader;

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) die();

Loc::loadMessages(__FILE__);

class adminFilterclientComponent extends \CBitrixComponent implements \Bitrix\Main\Engine\Contract\Controllerable, \Bitrix\Main\Errorable
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
        $request = $this->request;
        $post = $this->request->getPostList()->toArray();

        //\Dbg::var_dump($post);

        //if ($request->get('ID') == NULL ) $this->errorCollection[] = new Error('id not found!',1);

        return $params;
    }

    public function executeComponent()
    {
        $FILTER_NAME = $this->arParams['FILTER_NAME'];

        /*Make filter*/
        global ${$FILTER_NAME};
        if(!is_array(${$FILTER_NAME}))
            ${$FILTER_NAME} = array();

        $post = $this->request->getPostList()->toArray();
        //\Dbg::var_dump($post);

        $this->arResult['SEARCH_RESULT'] = [];
        $SEARCH_RESULT = &$this->arResult['SEARCH_RESULT'];
        if($post['clientidsearch']) $SEARCH_RESULT['clientidsearch'] = ${$FILTER_NAME}['clientidsearch'] = $post['clientidsearch'];

        if($post['projectidsearch']){
            $SEARCH_RESULT['projectidsearch'] = ${$FILTER_NAME}['projectidsearch'] = $post['projectidsearch'];
            if (!$post['clientidsearch']){
                $data = \Bitrix\Kabinet\project\datamanager\ProjectsTable::getlist([
                    'select'=>['ID','UF_AUTHOR_ID'],
                    'filter'=>['ID'=>$post['projectidsearch']],
                    'order'=>['UF_NAME'=>'ASC']
                ])->fetch();

                if ($data) $SEARCH_RESULT['clientidsearch'] = ${$FILTER_NAME}['clientidsearch'] = $data['UF_AUTHOR_ID'];
            }
        }

        if($post['taskidsearch']){
            $SEARCH_RESULT['taskidsearch'] = ${$FILTER_NAME}['taskidsearch'] = $post['taskidsearch'];

            $data = \Bitrix\Kabinet\task\datamanager\TaskTable::getlist([
                'select'=>['ID','UF_AUTHOR_ID','UF_PROJECT_ID'],
                'filter'=>['ID'=>$post['taskidsearch']],
                'order'=>['UF_NAME'=>'ASC']
            ])->fetch();

            if ($data && !$post['clientidsearch'])
                $SEARCH_RESULT['clientidsearch'] = ${$FILTER_NAME}['clientidsearch'] = $data['UF_AUTHOR_ID'];

            if ($data && !$post['projectidsearch'])
                $SEARCH_RESULT['projectidsearch'] = ${$FILTER_NAME}['projectidsearch'] = $data['UF_PROJECT_ID'];
        }

        if($post['clienttextsearch'] && !$post['clientidsearch'])
            ${$FILTER_NAME}['clienttextsearch'] = $SEARCH_RESULT['clienttextsearch'] = $post['clienttextsearch'];

        if($post['projecttextsearch'] && !$post['projectidsearch'])
            ${$FILTER_NAME}['projecttextsearch'] = $SEARCH_RESULT['projecttextsearch'] = $post['projecttextsearch'];

        if($post['tasktextsearch'] && !$post['taskidsearch'])
            ${$FILTER_NAME}['tasktextsearch'] = $SEARCH_RESULT['tasktextsearch'] = $post['tasktextsearch'];

        if($post['executionidsearch'])
            ${$FILTER_NAME}['executionidsearch'] = $SEARCH_RESULT['executionidsearch'] = $post['executionidsearch'];

        if(isset($post['statusexecutionsearch']))
            ${$FILTER_NAME}['statusexecutionsearch'] = $SEARCH_RESULT['statusexecutionsearch'] =  $post['statusexecutionsearch'];

        if($post['planedaterangesearchfrom'])
            ${$FILTER_NAME}['planedaterangesearchfrom'] = $SEARCH_RESULT['planedaterangesearchfrom'] = $post['planedaterangesearchfrom'];

        if($post['planedaterangesearchto'])
            ${$FILTER_NAME}['planedaterangesearchto'] = $SEARCH_RESULT['planedaterangesearchto'] = $post['planedaterangesearchto'];

        if($post['publicdatefromsearch'])
            ${$FILTER_NAME}['publicdatefromsearch'] = $SEARCH_RESULT['publicdatefromsearch'] = $post['publicdatefromsearch'];

        if($post['publicdatetosearch'])
            ${$FILTER_NAME}['publicdatetosearch'] = $SEARCH_RESULT['publicdatetosearch'] = $post['publicdatetosearch'];

        if($post['accountsearch'])
            ${$FILTER_NAME}['accountsearch'] = $SEARCH_RESULT['accountsearch'] = $post['accountsearch'];

        if($post['loginsearch'])
            ${$FILTER_NAME}['loginsearch'] = $SEARCH_RESULT['loginsearch'] = $post['loginsearch'];

        if($post['ipsearch'])
            ${$FILTER_NAME}['ipsearch'] = $SEARCH_RESULT['ipsearch'] = $post['ipsearch'];

        // Требует внимания
        if($post['attention']) ${$FILTER_NAME}['attention'] = $SEARCH_RESULT['attention'] = $post['attention'];

        $this->includeComponentTemplate($this->template);
        return true;
    }

    public function prepareData(){
    }

    public function getclientsAction()
    {
        $output = [];

        $data = \Bitrix\Kabinet\UserTable::getlist([
            'select'=>['ID','LOGIN','NAME','LAST_NAME','SECOND_NAME','EMAIL'],
            'filter'=>[
                'ACTIVE'=>1,
                'UF_GROUP_REF.GROUP_ID'=>REGISTRATED,
                '>PROJECTS.ID'=>0,
                'PROJECTS.UF_ACTIVE'=>1
            ],
            'order'=>['NAME'=>'ASC','EMAIL'=>'ASC']
        ])->fetchAll();

        if (!$data) {
            $this->errorCollection[] = new Error('Нет данных по клиентам!',1);
            if ($this->hasErrors())
                return null;
        }

        foreach ($data as $item){
            $userName = current(array_filter([
                trim(implode(" ", [$item['LAST_NAME'], $item['NAME'], $item['SECOND_NAME']])),
                $item['LOGIN']
            ]));

            $output[] = [
                "value"=>$userName .' '. $item['EMAIL']. ' (ID'.$item['ID'].')',
                id=>$item['ID']
            ];
        }

        return $output;
    }

    public function getprojectAction($ID)
    {
        $output = [];

		$filter = ['UF_ACTIVE'=>1];		
		if ($ID>0) $filter['UF_AUTHOR_ID'] = $ID;

        $data = \Bitrix\Kabinet\project\datamanager\ProjectsTable::getlist([
            'select'=>['ID','UF_NAME','UF_EXT_KEY'],
            'filter'=>$filter,
            'order'=>['UF_NAME'=>'ASC']
        ])->fetchAll();

        if (!$data) {
            $this->errorCollection[] = new Error('Нет данных по проектам',1);
            if ($this->hasErrors())
                return null;
        }

        foreach ($data as $item){
            $output[] = [
                "value"=>$item['UF_NAME']. ' (#'.$item['UF_EXT_KEY'].')',
                id=>$item['ID']
            ];
        }

        return $output;
    }

    public function gettaskAction($ID)
    {
        $output = [];
		
		$filter = ['UF_ACTIVE'=>1];		
		if ($ID>0) $filter['UF_PROJECT_ID'] = $ID;

        $data = \Bitrix\Kabinet\task\datamanager\TaskTable::getlist([
            'select'=>['ID','UF_NAME','UF_EXT_KEY'],
            'filter'=>$filter,
            'order'=>['UF_NAME'=>'ASC']
        ])->fetchAll();

        if (!$data) {
                return [];
        }

        foreach ($data as $item){
            $output[] = [
                "value"=>$item['UF_NAME']. ' (#'.$item['UF_EXT_KEY'].')',
                id=>$item['ID']
            ];
        }

        //$this->errorCollection[] = new Error('You are so beautiful or so handsome',1);
        if ($this->hasErrors())
            return null;

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
            'getclients' => [
                'prefilters' => [
                    new ActionFilter\Authentication(),
                    new ActionFilter\HttpMethod(
                        array(ActionFilter\HttpMethod::METHOD_GET, ActionFilter\HttpMethod::METHOD_POST)
                    ),
                    new ActionFilter\Csrf(),
                    new \Bitrix\Kabinet\Engine\ActionFilter\Groupmanager()
                ]
            ],
            'getproject' => [
                'prefilters' => [
                    new ActionFilter\Authentication(),
                    new ActionFilter\HttpMethod(
                        array(ActionFilter\HttpMethod::METHOD_GET, ActionFilter\HttpMethod::METHOD_POST)
                    ),
                    new ActionFilter\Csrf(),
                    new \Bitrix\Kabinet\Engine\ActionFilter\Groupmanager()
                ]
            ],
            'gettask' => [
                'prefilters' => [
                    new ActionFilter\Authentication(),
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