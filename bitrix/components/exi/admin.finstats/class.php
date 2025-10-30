<?
use Bitrix\Main,
    Bitrix\Main\Error,
    Bitrix\Main\Localization\Loc,
    Bitrix\Main\ErrorCollection,
    Bitrix\Main\Engine\ActionFilter,
    Bitrix\Main\Loader;

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) die();

Loc::loadMessages(__FILE__);

class adminFinstatsComponent extends \CBitrixComponent implements \Bitrix\Main\Engine\Contract\Controllerable, \Bitrix\Main\Errorable
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

    /**
     * Получает все статусы из XML файла
     */
    protected function getAllStatusesFromXML()
    {
        $statuses = [];

        $xmlFile = $_SERVER['DOCUMENT_ROOT'] . '/path/to/your/states.xml'; // Укажите правильный путь к файлу

        if (!file_exists($xmlFile)) {
            // Если файл не найден, создаем базовый набор статусов
            return $this->getDefaultStatuses();
        }

        $xml = simplexml_load_file($xmlFile);

        if ($xml === false) {
            return $this->getDefaultStatuses();
        }

        // Проходим по всем продуктам и собираем статусы
        foreach ($xml->product as $product) {
            foreach ($product->states->state as $state) {
                $id = (int)$state['id'];
                $title = (string)$state->title;

                if (!isset($statuses[$id])) {
                    $statuses[$id] = $title;
                }
            }
        }

        ksort($statuses);

        return $statuses;
    }

    /**
     * Возвращает набор статусов по умолчанию, если XML недоступен
     */
    protected function getDefaultStatuses()
    {
        return [
            0 => 'Запланирован',
            1 => 'Взят в работу',
            2 => 'Пишется текст',
            3 => 'Ожидается текст от клиента',
            4 => 'В работе у специалиста',
            5 => 'На согласовании (у клиента)',
            6 => 'Публикация',
            7 => 'Готовится отчет',
            8 => 'Отчет на проверке у клиента',
            9 => 'Выполнена',
            10 => 'Отменена'
        ];
    }

    /**
     * Добавляет 11 часов 59 минут к дате
     */
    protected function addEndOfDay($dateString)
    {
        if (empty($dateString)) {
            return null;
        }

        try {
            // Создаем объект DateTime из строки
            $date = new \DateTime($dateString);
            // Добавляем 11 часов 59 минут
            $date->modify('+11 hours +59 minutes');



            return $date->format('d.m.Y H:i:s');
        } catch (\Exception $e) {
            // В случае ошибки возвращаем оригинальную дату
            return $dateString;
        }
    }

    public function prepareData(){
        global $DB;
        $arParams = $this->arParams;
        $arResult = &$this->arResult;
        $FILTER = $arParams['FILTER'];

        // Получаем все возможные статусы из XML
        $allStatuses = $this->getAllStatusesFromXML();

        // Инициализируем результат
        $this->arResult["STATS"] = [];
        $this->arResult["ALL_STATUSES"] = $allStatuses;
        $this->arResult["TOTAL_COUNT"] = 0;
        $this->arResult["TOTAL_SUM"] = 0;

        $HLBClassFulf = \Bitrix\Main\DI\ServiceLocator::getInstance()->get('FULF_HL');

        $Query = $HLBClassFulf::query();

        // Фильтр по дате публикации
        if(!empty($FILTER['publicdatefromsearch'])) {
            $Query->addFilter('>=UF_CREATE_DATE', $FILTER['publicdatefromsearch']);
        }
        if(!empty($FILTER['publicdatetosearch'])) {
            // Добавляем 11 часов 59 минут к конечной дате
            $endDate = $this->addEndOfDay($FILTER['publicdatetosearch']);
            $Query->addFilter('<=UF_CREATE_DATE', $endDate);
        }

        //$Query->addFilter('UF_STATUS',6);

        // Выбираем только нужные поля
        $Query->setSelect([
            'ID',
            'UF_STATUS',
            'UF_MONEY_RESERVE'
        ]);

        $res = $Query->exec()->fetchAll();

        //\Dbg::print_r(\Bitrix\Main\Entity\Query::getLastQuery());

        //\Dbg::print_r($res);

        // Инициализируем статистику для всех статусов
        $statsByStatus = [];
        foreach ($allStatuses as $statusId => $statusName) {
            $statsByStatus[$statusId] = [
                'count' => 0,
                'sum' => 0
            ];
        }

        if ($res) {
            // Собираем статистику по найденным записям
            foreach ($res as $item) {
                $status = $item['UF_STATUS'];
                $money = (float)$item['UF_MONEY_RESERVE'];

                if (isset($statsByStatus[$status])) {
                    $statsByStatus[$status]['count']++;
                    $statsByStatus[$status]['sum'] += $money;

                    $this->arResult["TOTAL_COUNT"]++;
                    $this->arResult["TOTAL_SUM"] += $money;
                }
            }
        }

        // Формируем итоговый массив статистики
        foreach ($statsByStatus as $status => $data) {
            $this->arResult["STATS"][] = [
                'STATUS_ID' => $status,
                'STATUS_NAME' => $allStatuses[$status],
                'COUNT' => $data['count'],
                'SUM' => $data['sum'],
                'SUM_FORMATTED' => number_format($data['sum'], 2, '.', ' ')
            ];
        }
    }

    public function loadmoreAction()
    {
        $arParams = &$this->arParams;
        $post = $this->request->getPostList()->toArray();

        // Обрабатываем JSON фильтр
        if (!empty($post['FILTER_JSON'])) {
            $filterJson = $post['FILTER_JSON'];
            $decodedFilter = json_decode($filterJson, true);

            if (json_last_error() === JSON_ERROR_NONE && is_array($decodedFilter)) {
                $arParams['FILTER'] = $decodedFilter;
            } else {
                $arParams['FILTER'] = $this->request->getPostList()->toArray();
            }
        } else {
            $arParams['FILTER'] = $this->request->getPostList()->toArray();
        }

        // Удаляем служебные поля из фильтра
        unset($arParams['FILTER']['signedParamsString']);

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
        return [
            'loadmore' => [
                'prefilters' => [
                    new ActionFilter\HttpMethod(
                        array(ActionFilter\HttpMethod::METHOD_GET, ActionFilter\HttpMethod::METHOD_POST)
                    ),
                    new ActionFilter\Csrf(),
                    new \Bitrix\Kabinet\Engine\ActionFilter\Groupmanager()
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