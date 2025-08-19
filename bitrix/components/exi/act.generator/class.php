<?
use Bitrix\Main,
    Bitrix\Main\Error,
    Bitrix\Main\Localization\Loc,
    Bitrix\Main\ErrorCollection,
    Bitrix\Main\Engine\ActionFilter,
    Bitrix\Main\Loader;

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) die();

Loc::loadMessages(__FILE__);


/*
 * ActGeneratorComponent - это компонент Bitrix, который:
Генерирует акты выполненных работ на основе данных из системы
Группирует работы и комиссионные сборы по месяцам
Исключает данные текущего месяца из результатов
Учитывает дату договора пользователя для корректной группировки
Формирует структурированные данные для последующего отображения
 */

class ActGeneratorComponent extends \CBitrixComponent implements \Bitrix\Main\Engine\Contract\Controllerable, \Bitrix\Main\Errorable
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

    /**
     * Подготовка параметров компонента
     * @param array $params Входные параметры
     * @return array Обработанные параметры
     */
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


        return $params;
    }

    /**
     * Основной метод выполнения компонента
     * @return bool Результат выполнения
     */
    public function executeComponent()
    {
        if ($this->hasErrors())
        {
            return $this->processErrors();
        }

        $this->prepareData();

        //$this->includeComponentTemplate(isMobileDevice() ? 'mobile' : '');

        $this->includeComponentTemplate($this->template);

        return true;
    }

    /**
     * Подготовка данных для компонента
     * Основной метод, который собирает все необходимые данные
     */
    public function prepareData() {
        $this->arResult['groupedFulfillments'] = [];

        // 1. Получение базовых данных
        $user = $this->getCurrentUser();
        $contractData = $this->getContractData($user);

        // 2. Получение данных проектов и заказов
        $projects = $this->getUserProjects($user);
        $orders = $this->getUserOrders($user);

        // 3. Получение и обработка исполнений
        $fulfillments = $this->getFulfillments($user);
        $commissionFees = $this->getCommissionFees($user);

        // 4. Группировка данных
        $groupedData = $this->groupFulfillments(
            $fulfillments,
            $commissionFees,
            $projects,
            $orders,
            $contractData
        );

        $this->arResult['groupedFulfillments'] = $groupedData;
    }

    /**
     * Получает текущего пользователя системы
     * @return array Данные пользователя
     */
    protected function getCurrentUser() {
        $user = \Bitrix\Main\DI\ServiceLocator::getInstance()->get('user');
        $dataArray = \Bitrix\Main\DI\ServiceLocator::getInstance()->get('Kabinet.Client')->getData();
        return $dataArray[0];
    }


    /**
     * Получает данные о договоре пользователя
     * @param array $user Данные пользователя
     * @return array Структура с данными договора (дата, месяц-год, день)
     */
    protected function getContractData($user) {
        $contractDate = $user["UF_DOGOVOR_DATE"] ?? null;
        $contractDateTime = $contractDate ? new DateTime($contractDate) : null;

        return [
            'date' => $contractDateTime,
            'monthYear' => $contractDateTime ? $contractDateTime->format('Y-m') : null,
            'day' => $contractDateTime ? $contractDateTime->format('d') : '01'
        ];
    }

    /**
     * Получает проекты пользователя
     * @param array $user Данные пользователя
     * @return array Список проектов
     */
    protected function getUserProjects($user) {
        return \Bitrix\Kabinet\project\datamanager\ProjectsTable::getlist([
            'select' => ['ID', 'UF_ORDER_ID'],
            'filter' => ['UF_AUTHOR_ID' => $user["ID"]]
        ])->fetchAll();
    }

    /**
     * Получает заказы пользователя
     * @param array $user Данные пользователя
     * @return array Список заказов
     */
    protected function getUserOrders($user) {
        $projectManager = \Bitrix\Main\DI\ServiceLocator::getInstance()->get('Kabinet.Project');
        return $projectManager->orderData($user["ID"]);
    }

    /**
     * Получает исполнения (выполненные работы) пользователя
     * @param array $user Данные пользователя
     * @return \Bitrix\Main\DB\Result Результат запроса исполнений
     */
    protected function getFulfillments($user) {
        return \Bitrix\Kabinet\taskrunner\datamanager\FulfillmentTable::getList([
            'select' => [
                'ID', 'UF_TASK_ID', 'UF_ACTUAL_DATE', 'UF_MONEY_RESERVE',
                'UF_NUMBER_STARTS', 'UF_ELEMENT_TYPE', 'UF_STATUS',
                'TASK_UF_PRODUKT_ID' => 'TASK.UF_PRODUKT_ID',
                'TASK_UF_PROJECT_ID' => 'TASK.UF_PROJECT_ID',
                'TASK_UF_NAME' => 'TASK.UF_NAME',
                'UF_DAY' => new \Bitrix\Main\Entity\ExpressionField('UF_DAY', 'DAY(%s)', ['UF_ACTUAL_DATE']),
                'UF_MONTH' => new \Bitrix\Main\Entity\ExpressionField('UF_MONTH', 'MONTH(%s)', ['UF_ACTUAL_DATE']),
                'UF_YEAR' => new \Bitrix\Main\Entity\ExpressionField('UF_YEAR', 'YEAR(%s)', ['UF_ACTUAL_DATE'])
            ],
            'filter' => [
                'TASK.UF_AUTHOR_ID' => $user["ID"],
                '>UF_STATUS' => 8,
                '!UF_ACTUAL_DATE' => null
            ],
            'order' => [
                'UF_YEAR' => 'DESC',
                'UF_MONTH' => 'DESC',
                'UF_DAY' => 'DESC'
            ]
        ]);
    }

    /**
     * Получает комиссионные сборы пользователя
     * @param array $user Данные пользователя
     * @return array Список комиссионных сборов
     */
    protected function getCommissionFees($user) {
        return \Bitrix\Kabinet\billing\datamanager\BillinghistoryTable::getList([
            'select' => [
                'ID', 'UF_VALUE', 'UF_PUBLISH_DATE',
                'UF_DAY' => new \Bitrix\Main\Entity\ExpressionField('UF_DAY', 'DAY(%s)', ['UF_PUBLISH_DATE']),
                'UF_MONTH' => new \Bitrix\Main\Entity\ExpressionField('UF_MONTH', 'MONTH(%s)', ['UF_PUBLISH_DATE']),
                'UF_YEAR' => new \Bitrix\Main\Entity\ExpressionField('UF_YEAR', 'YEAR(%s)', ['UF_PUBLISH_DATE'])
            ],
            'filter' => [
                'UF_AUTHOR_ID' => $user["ID"],
                'UF_OPERATION' => 'Комиссионный сбор',
                'UF_ACTIVE' => true
            ],
            'order' => [
                'UF_YEAR' => 'DESC',
                'UF_MONTH' => 'DESC',
                'UF_DAY' => 'DESC'
            ]
        ])->fetchAll();
    }

    /**
     * Группирует исполнения и комиссионные сборы по периодам
     * @param \Bitrix\Main\DB\Result $fulfillments Исполнения
     * @param array $commissionFees Комиссионные сборы
     * @param array $projects Проекты пользователя
     * @param array $orders Заказы пользователя
     * @param array $contractData Данные договора
     * @return array Сгруппированные данные
     */
    protected function groupFulfillments($fulfillments, $commissionFees, $projects, $orders, $contractData) {
        $grouped = [];
        $currentMonth = (new DateTime())->format('Y-m');

        // Обработка исполнений
        while ($item = $fulfillments->fetch()) {
            $this->processFulfillmentItem($item, $grouped, $projects, $orders, $contractData, $currentMonth);
        }

        // Обработка комиссионных сборов
        foreach ($commissionFees as $fee) {
            $this->processCommissionItem($fee, $grouped, $contractData, $currentMonth);
        }

        krsort($grouped);
        return $grouped;
    }

    /**
     * Обрабатывает одно исполнение (работу) для группировки
     * @param array $item Данные исполнения
     * @param array &$grouped Ссылка на массив с группированными данными
     * @param array $projects Проекты пользователя
     * @param array $orders Заказы пользователя
     * @param array $contractData Данные договора
     * @param string $currentMonth Текущий месяц (Y-m)
     */
    protected function processFulfillmentItem($item, &$grouped, $projects, $orders, $contractData, $currentMonth) {
        $date = new DateTime($item['UF_ACTUAL_DATE']);
        $monthYear = $date->format('Y-m');

        // Пропускаем текущий месяц
        if ($monthYear === $currentMonth) {
            return;
        }

        // Для месяца договора создаем две группы: с 1 числа и с даты договора
        if ($monthYear === $contractData['monthYear'] && $contractData['date']) {
            $day = $date->format('d');
            $contractDay = $contractData['date']->format('d');

            // Определяем период (до или после даты договора)
            if ($day < $contractDay) {
                // Период до даты договора (с 1 числа по день договора-1)
                $startDay = '01';
            } else {
                // Период после даты договора (с дня договора по конец месяца)
                $startDay = $contractDay;
            }

            // Формируем ключ группы
            $groupKey = sprintf('%04d-%02d-%02d', $item['UF_YEAR'], $item['UF_MONTH'], $startDay);
        } else {
            // Для всех остальных месяцев - одна группа с 1 числа
            $groupKey = sprintf('%04d-%02d-01', $item['UF_YEAR'], $item['UF_MONTH']);
        }

        // Находим данные продукта
        $productData = $this->getProductData($item, $projects, $orders);

        // Формируем запись
        $record = [
            'ID' => $item['ID'],
            'UF_TASK_ID' => $item['UF_TASK_ID'],
            'UF_ACTUAL_DATE' => $item['UF_ACTUAL_DATE'],
            'UF_MONEY_RESERVE' => $item['UF_MONEY_RESERVE'],
            'UF_NUMBER_STARTS' => $item['UF_ELEMENT_TYPE'] == 'multiple' ? $item['UF_NUMBER_STARTS'] : 1,
            'UF_ELEMENT_TYPE' => $item['UF_ELEMENT_TYPE'],
            'UF_STATUS' => $item['UF_STATUS'],
            'TASK_UF_PRODUKT_ID' => $item['TASK_UF_PRODUKT_ID'],
            'TASK_UF_PROJECT_ID' => $item['TASK_UF_PROJECT_ID'],
            'TASK_UF_NAME' => $item['TASK_UF_NAME'],
            'MEASURE_NAME' => $productData['measure'] ?? 'ед.',
            'CATALOG_PRICE_1' => $productData['price'] ?? 0,
            'UF_YEAR' => $item['UF_YEAR'],
            'UF_MONTH' => $item['UF_MONTH'],
            'UF_DAY' => $item['UF_DAY']
        ];

        $grouped[$groupKey][] = $record;
    }

    /**
     * Обрабатывает один комиссионный сбор для группировки
     * @param array $fee Данные комиссионного сбора
     * @param array &$grouped Ссылка на массив с группированными данными
     * @param array $contractData Данные договора
     * @param string $currentMonth Текущий месяц (Y-m)
     */
    protected function processCommissionItem($fee, &$grouped, $contractData, $currentMonth) {
        $date = new DateTime($fee['UF_PUBLISH_DATE']);
        $monthYear = $date->format('Y-m');

        // Пропускаем текущий месяц
        if ($monthYear === $currentMonth) {
            return;
        }

        // Для месяца договора создаем две группы: с 1 числа и с даты договора
        if ($monthYear === $contractData['monthYear'] && $contractData['date']) {
            $day = $date->format('d');
            $contractDay = $contractData['date']->format('d');

            // Определяем период (до или после даты договора)
            if ($day < $contractDay) {
                // Период до даты договора (с 1 числа по день договора-1)
                $startDay = '01';
            } else {
                // Период после даты договора (с дня договора по конец месяца)
                $startDay = $contractDay;
            }

            // Формируем ключ группы
            $groupKey = sprintf('%04d-%02d-%02d', $fee['UF_YEAR'], $fee['UF_MONTH'], $startDay);
        } else {
            // Для всех остальных месяцев - одна группа с 1 числа
            $groupKey = sprintf('%04d-%02d-01', $fee['UF_YEAR'], $fee['UF_MONTH']);
        }

        $grouped[$groupKey][] = [
            'ID' => 'commission_' . $fee['ID'],
            'UF_TASK_ID' => 0,
            'UF_ACTUAL_DATE' => $fee['UF_PUBLISH_DATE'],
            'UF_MONEY_RESERVE' => $fee['UF_VALUE'],
            'UF_NUMBER_STARTS' => 1,
            'UF_ELEMENT_TYPE' => 'commission',
            'UF_STATUS' => 9,
            'TASK_UF_PRODUKT_ID' => 0,
            'TASK_UF_PROJECT_ID' => 0,
            'TASK_UF_NAME' => 'Комиссионный сбор',
            'MEASURE_NAME' => 'ед.',
            'CATALOG_PRICE_1' => $fee['UF_VALUE'],
            'UF_YEAR' => $fee['UF_YEAR'],
            'UF_MONTH' => $fee['UF_MONTH'],
            'UF_DAY' => $fee['UF_DAY'],
            'IS_COMMISSION' => true
        ];
    }

    /**
     * Обрабатывает одно исполнение (работу) для группировки
     * @param array $item Данные исполнения
     * @param array &$grouped Ссылка на массив с группированными данными
     * @param array $projects Проекты пользователя
     * @param array $orders Заказы пользователя
     * @param array $contractData Данные договора
     * @param string $currentMonth Текущий месяц (Y-m)
     */
    protected function processFulfillmentItem_($item, &$grouped, $projects, $orders, $contractData, $currentMonth) {
        $date = new DateTime($item['UF_ACTUAL_DATE']);
        $monthYear = $date->format('Y-m');

        // Пропускаем текущий месяц
        if ($monthYear === $currentMonth) {
            return;
        }

        // Определяем день начала периода
        $isContractMonth = ($monthYear === $contractData['monthYear']);
        $startDay = $isContractMonth ? $contractData['day'] : '01';

        // Пропускаем записи до дня начала периода
        if ($isContractMonth && $date->format('d') < $startDay) {
            return;
        }

        // Формируем ключ группы
        $groupKey = sprintf('%04d-%02d-%02d', $item['UF_YEAR'], $item['UF_MONTH'], $startDay);

        // Находим данные продукта
        $productData = $this->getProductData($item, $projects, $orders);

        // Формируем запись
        $record = [
            'ID' => $item['ID'],
            'UF_TASK_ID' => $item['UF_TASK_ID'],
            'UF_ACTUAL_DATE' => $item['UF_ACTUAL_DATE'],
            'UF_MONEY_RESERVE' => $item['UF_MONEY_RESERVE'],
            'UF_NUMBER_STARTS' => $item['UF_ELEMENT_TYPE'] == 'multiple' ? $item['UF_NUMBER_STARTS'] : 1,
            'UF_ELEMENT_TYPE' => $item['UF_ELEMENT_TYPE'],
            'UF_STATUS' => $item['UF_STATUS'],
            'TASK_UF_PRODUKT_ID' => $item['TASK_UF_PRODUKT_ID'],
            'TASK_UF_PROJECT_ID' => $item['TASK_UF_PROJECT_ID'],
            'TASK_UF_NAME' => $item['TASK_UF_NAME'],
            'MEASURE_NAME' => $productData['measure'] ?? 'ед.',
            'CATALOG_PRICE_1' => $productData['price'] ?? 0,
            'UF_YEAR' => $item['UF_YEAR'],
            'UF_MONTH' => $item['UF_MONTH'],
            'UF_DAY' => $item['UF_DAY']
        ];

        $grouped[$groupKey][] = $record;
    }

    /**
     * Обрабатывает один комиссионный сбор для группировки
     * @param array $fee Данные комиссионного сбора
     * @param array &$grouped Ссылка на массив с группированными данными
     * @param array $contractData Данные договора
     * @param string $currentMonth Текущий месяц (Y-m)
     */
    protected function processCommissionItem_($fee, &$grouped, $contractData, $currentMonth) {
        $date = new DateTime($fee['UF_PUBLISH_DATE']);
        $monthYear = $date->format('Y-m');

        // Пропускаем текущий месяц
        if ($monthYear === $currentMonth) {
            return;
        }

        // Определяем день начала периода
        $isContractMonth = ($monthYear === $contractData['monthYear']);
        $startDay = $isContractMonth ? $contractData['day'] : '01';

        // Пропускаем записи до дня начала периода
        if ($isContractMonth && $date->format('d') < $startDay) {
            return;
        }

        // Формируем ключ группы
        $groupKey = sprintf('%04d-%02d-%02d', $fee['UF_YEAR'], $fee['UF_MONTH'], $startDay);

        $grouped[$groupKey][] = [
            'ID' => 'commission_' . $fee['ID'],
            'UF_TASK_ID' => 0,
            'UF_ACTUAL_DATE' => $fee['UF_PUBLISH_DATE'],
            'UF_MONEY_RESERVE' => $fee['UF_VALUE'],
            'UF_NUMBER_STARTS' => 1,
            'UF_ELEMENT_TYPE' => 'commission',
            'UF_STATUS' => 9,
            'TASK_UF_PRODUKT_ID' => 0,
            'TASK_UF_PROJECT_ID' => 0,
            'TASK_UF_NAME' => 'Комиссионный сбор',
            'MEASURE_NAME' => 'ед.',
            'CATALOG_PRICE_1' => $fee['UF_VALUE'],
            'UF_YEAR' => $fee['UF_YEAR'],
            'UF_MONTH' => $fee['UF_MONTH'],
            'UF_DAY' => $fee['UF_DAY'],
            'IS_COMMISSION' => true
        ];
    }

    /**
     * Получает данные продукта для исполнения
     * @param array $fulfillment Данные исполнения
     * @param array $projects Проекты пользователя
     * @param array $orders Заказы пользователя
     * @return array|null Данные продукта (мера измерения и цена) или null если не найдены
     */
    protected function getProductData($fulfillment, $projects, $orders) {
        $key = array_search($fulfillment['TASK_UF_PROJECT_ID'], array_column($projects, 'ID'));
        if ($key === false) {
            return null;
        }

        $project = $projects[$key];
        $productId = $fulfillment["TASK_UF_PRODUKT_ID"];

        if (!isset($orders[$project['UF_ORDER_ID']][$productId])) {
            return null;
        }

        $product = $orders[$project['UF_ORDER_ID']][$productId];

        return [
            'measure' => $product['MEASURE_NAME'] ?? 'ед.',
            'price' => $product['CATALOG_PRICE_1'] ?? 0
        ];
    }

    /**
     * Возвращает список подписанных параметров
     * @return array Пустой массив (не используется)
     */
    /* signed params*/
    protected function listKeysSignedParameters()
    {
        return [
        ];
    }

    /**
     * Конфигурация действий компонента
     * @return array Пустой массив (настройки по умолчанию)
     */
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