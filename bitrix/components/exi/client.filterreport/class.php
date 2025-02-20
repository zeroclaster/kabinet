<?
use Bitrix\Main,
    Bitrix\Main\Error,
    Bitrix\Main\Localization\Loc,
    Bitrix\Main\ErrorCollection,
    Bitrix\Main\Engine\ActionFilter,
    Bitrix\Main\Loader;

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) die();

Loc::loadMessages(__FILE__);

class clientFilterReportComponent extends \CBitrixComponent implements \Bitrix\Main\Engine\Contract\Controllerable, \Bitrix\Main\Errorable
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

        //if ($request->get('ID') == NULL ) $this->errorCollection[] = new Error('id not found!');

        return $params;
    }

    public function executeComponent()
    {
		$sL = \Bitrix\Main\DI\ServiceLocator::getInstance();
		$runnerManager = $sL->get('Kabinet.Runner');
		
        $FILTER_NAME = $this->arParams['FILTER_NAME'];
		$this->arResult['SEARCH_RESULT'] = [];
		$SEARCH_RESULT = &$this->arResult['SEARCH_RESULT'];
		
		$task_id = $this->request->get('t');

		$post = $this->request->getPostList()->toArray();
        //\Dbg::var_dump($post);

        /*Make filter*/
        global ${$FILTER_NAME};
        if(!is_array(${$FILTER_NAME})) {
            ${$FILTER_NAME} = array();
            $post['alert'] = 'y';
        }
				
		// Требуют внимания
		// [3,5,8]
		$alert_status_client = $runnerManager->config('ALERT');
		
		$runner = $runnerManager->getData(
				$task_id,
				$clear=true,
				$id=[],
				$filter=['UF_STATUS'=>$alert_status_client]
		);
		
		${$FILTER_NAME}['alert'] = [];
		$this->arResult['count_alert'] = 0;
		// Подсчитываем количество исполнений требущие внимания
		foreach($runner as $item){
			 if(in_array($item['UF_STATUS'],$alert_status_client)) $this->arResult['count_alert']++;	 
			 
			 if(
					$post['alert'] &&
					in_array($item['UF_STATUS'],$alert_status_client)
			 ){
					$SEARCH_RESULT['alert'] = $post['alert'];
					${$FILTER_NAME}['statusfind'] = $alert_status_client;
			 }
		}
		
       
        if($post['fromdate1']){
            $SEARCH_RESULT['fromdate1'] = $post['fromdate1'];
            ${$FILTER_NAME}['fromdate1'] = $post['fromdate1'];
        }
        if($post['todate1']){
            $SEARCH_RESULT['todate1'] = $post['todate1'];
            ${$FILTER_NAME}['todate1'] = $post['todate1'];
        }
        if($post['statusfind']){
            $SEARCH_RESULT['statusfind'] = $post['statusfind'];
            ${$FILTER_NAME}['statusfind'] = $post['statusfind'];
        }

        if ($this->request->get('id') != NULL){
            $SEARCH_RESULT['id'] = $this->request->get('id');
            ${$FILTER_NAME}['id'] = $this->request->get('id');
        }

        if ($this->request->get('queue') != NULL){
            $SEARCH_RESULT['queue'] = $this->request->get('queue');
            ${$FILTER_NAME}['queue'] = $this->request->get('queue');
        }

        $this->includeComponentTemplate($this->template);

        return true;
    }

    public function prepareData(){

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