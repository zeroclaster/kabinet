<?php
namespace Bitrix\Kabinet\project;

use \Bitrix\Main\SystemException,
    \Bitrix\Kabinet\exceptions\ProjectException,
    \Bitrix\Kabinet\exceptions\TestException;

class Projectmanager extends \Bitrix\Kabinet\container\Abstracthighloadmanager {
    public $fieldsType___ = [
        "HLBLOCK_4_UF_NAME"=>1,
        "HLBLOCK_8_UF_TOPICS_LIST"=>1,
        "HLBLOCK_8_UF_PROJECT_GOAL"=>1,
        "HLBLOCK_8_UF_SITE"=>1,
        "HLBLOCK_8_UF_OFFICIAL_NAME"=>1,
        "HLBLOCK_8_UF_REVIEWS_NAME"=>1,
        "HLBLOCK_8_UF_CONTACTS_PUBLIC"=>1,
        "HLBLOCK_8_UF_COMP_PREVIEW_TEXT"=>1,
        "HLBLOCK_8_UF_COMP_DESCRIPTION_TEXT"=>1,
        "HLBLOCK_8_UF_COMP_LOGO"=>1,
        "HLBLOCK_8_UF_ORG_ADDRESS"=>1,
        "HLBLOCK_8_UF_WORKING_HOURS"=>1,
        "HLBLOCK_9_UF_ABOUT_REVIEW"=>1,
        "HLBLOCK_9_UF_POSITIVE_SIDES"=>1,
        "HLBLOCK_9_UF_MINUSES"=>1,
        "HLBLOCK_9_UF_MINUSES_USER"=>1,
        "HLBLOCK_9_UF_ORDER_PROCESS"=>1,
        "HLBLOCK_9_UF_ORDER_PROCESS_USER"=>1,
        "HLBLOCK_9_UF_EXAMPLES_REVIEWS"=>1,
        "HLBLOCK_9_UF_MENTION_REVIEWS"=>1,
        "HLBLOCK_9_UF_KEYWORDS"=>1,
        "HLBLOCK_12_UF_TARGET_AUDIENCE"=>1,
        "HLBLOCK_12_UF_COUNTRY"=>1,
        "HLBLOCK_12_UF_REGION"=>1,
        "HLBLOCK_12_UF_CITY"=>1,
        "HLBLOCK_12_UF_RATIO_GENDERS"=>1,
        "HLBLOCK_4_UF_ADDITIONAL_WISHES"=>1,
    ];

    public $fieldsType = [
        "UF_NAME"=>1,
        "UF_ADDITIONAL_WISHES"=>1,
    ];
    protected $user;

    public function __construct($user, $HLBCClass)
    {
        $this->user = $user;
        parent::__construct($HLBCClass);

        AddEventHandler("", "\Projects::OnBeforeAdd", [$this,"AutoIncrementAddHandler"]);
    }

    public function AutoIncrementAddHandler($fields,$object)
    {
        $HLBClass = \Bitrix\Main\DI\ServiceLocator::getInstance()->get('BRIEF_HL');
        $last = $HLBClass::getlist([
            'select'=>['UF_EXT_KEY'],
            'order'=>['ID'=>"DESC"],
            'limit' =>1
        ])->fetch();

        $UF_EXT_KEY = 100000;
        if ($last && $last['UF_EXT_KEY']>0) $UF_EXT_KEY = $last['UF_EXT_KEY'] + 1;

        $object->set('UF_EXT_KEY', $UF_EXT_KEY);
    }


    public function getData($clear=false,$user_id = [],$filter=[]){
        global $CACHE_MANAGER;

        if (!$user_id) {
            $user = $this->user;
            $user_id = $user->get('ID');
        }

        // сколько времени кешировать
        $ttl = 14400;
		
        // hack: $ttl = 0 то не кешировать
        // $ttl = 0 отменяем чтение из кеша
        // function initCache $ttl <= 0 return false;
        if ($filter) $ttl = 0;
        if (!$filter) $filter = ['UF_AUTHOR_ID'=>$user_id];

        // Кеш завязан только на пользователе
        // любой update вызывает clearCache() $this->getData($clear=true);
        $cacheId = '';
        $cacheId = SITE_ID."|".$cacheId;
        $cacheId .= "|".serialize(intval($user_id));

        $cache = new \CPHPCache;
        // Clear cache "project_data"
        if ($clear) $cache->clean($cacheId, "kabinet/project");
        //$CACHE_MANAGER->ClearByTag("project_data");

        // for debugg!
        //$cache->clean($cacheId, "kabinet/project");

        if ($cache->StartDataCache($ttl, $cacheId, "kabinet/project"))
        {
            if (defined("BX_COMP_MANAGED_CACHE"))
            {
                $CACHE_MANAGER->StartTagCache("project_data");
                //\CIBlock::registerWithTagCache(self::SERVICES_IBLOCK);
            }

            $projects = \Bitrix\Kabinet\project\datamanager\ProjectsTable::getlist([
                'select'=>['*'],
                'filter'=>$filter,
                'order'=>["UF_PUBLISH_DATE"=>'DESC']
            ])->fetchAll();

            foreach ($projects as $data) {
                $c = $this->convertData($data, $this->getUserFields());
                // используется в отоюражении календаря
                $listdata[] = $c;
            }

            if (defined("BX_COMP_MANAGED_CACHE")) $CACHE_MANAGER->EndTagCache();
            $cache->EndDataCache(array($listdata));
        }
        else
        {
            $vars = $cache->GetVars();
            $listdata = $vars[0];
        }

        return $listdata;
    }

    public function getOrderList_(int $ID){
        $Products = [];
        $fieldsElement = [];

        \Bitrix\Main\Loader::includeModule("catalog");
        \Bitrix\Main\Loader::includeModule("sale");
        $order = \Bitrix\Sale\Order::load($ID); //по ID заказа
        $basket = $order->getBasket();

        /*
        * Если вдруг понадобится
        */
        /*
        $price = $basket->getPrice(); // Цена с учетом скидок
        $fullPrice = $basket->getBasePrice(); // Цена без учета скидок
        */

        /*
        var_dump($basket->getListOfFormatText()); // возвращает корзину в читаемом виде:
        // array(2) { [11]=> string(101) "Тарелка [Цвет: Кофе с молоком] - 2 : 2 199 руб." [12]=> string(65) "Кружка - 1 : 1 899 руб." }
        var_dump($basket->getQuantityList()); // возвращает массив "количеств" товаров в корзине:
        // array(3) { [11]=> float(2) [12]=> float(1) }

        var_dump(array_sum($basket->getQuantityList())); // float(3) - количество товаров в корзине
        var_dump(count($basket->getQuantityList())); // int(2) - количество позиций в корзине
        */

        foreach ($basket as $basketItem) {
            $product_id = $basketItem->getProductId();
            $basket_id = $basketItem->getId();
            $res = \CIBlockElement::GetList(
                Array("SORT"=>"ASC"),
                [
                    "IBLOCK_ID"=>1,
                    "ID" => $product_id,

                ],
                false,
                false,
                [
                    'ID',
                    'NAME',
                    'CODE',
                    'IBLOCK_SECTION',
                    'PREVIEW_PICTURE',
                    'PROPERTY_*',
                    'CATALOG_PRICE_1',
                ]
            );
            $fields = $res->GetNext();
            $fields['BASKET_ID'] = $basket_id;
            $fields['ORDER_ID'] = $ID;
            $fields['FINALPRICE'] = $basketItem->getFinalPrice();
            $fields['QUANTITY'] = $basketItem->getQuantity();
            $fields['PRICE'] = $basketItem->getPrice();
            $fields['CATALOG_PRICE_1'] = round($fields['CATALOG_PRICE_1']);
            $fields['MEASURE_NAME'] = $basketItem->getField("MEASURE_NAME");

            /*
            $item->getPropertyCollection(); // Свойства товара в корзине, коллекция объектов Sale\BasketPropertyItem, см. ниже
            $item->getCollection();         // Корзина, в которой лежит товар
            $item->getField('NAME');// Любое поле товара в корзине
            */

            $Products[(int)$fields['ID']] =$fields;
            $fieldsElement[(int)$fields['ID']] =$fields;
            //echo $basketItem->getField('NAME') . ' - ' . $basketItem->getQuantity() . '<br />';
        }

        // Заказ был создан, но все продукты удалили
        if (!$Products) return $Products;

        \CIBlockElement::GetPropertyValuesArray($Products, 1,["IBLOCK_ID"=>1]);
        // clear
        foreach ($Products as $key => $item) {
            foreach ($item as $key2 => $item2) {
                foreach ($item2 as $key3 => $item3) {
                    if (!in_array($key3, ['NAME', 'ID', 'CODE', 'VALUE','MULTIPLE','~VALUE','PROPERTY_TYPE','VALUE_XML_ID']))
                        unset($Products[$key][$key2][$key3]);
                }
            }
        }

        foreach ($Products as $key=>$itm){
            $picture = $fieldsElement[$key]['PREVIEW_PICTURE'];
            $arFileTmp = \CFile::ResizeImageGet(
                $picture,
                array("width" => 250, "height" => 127),
                BX_RESIZE_IMAGE_PROPORTIONAL
            );
            $fieldsElement[$key]['PREVIEW_PICTURE_SRC'] = $arFileTmp['src'];
            $Products[$key] = array_merge($itm,$fieldsElement[$key]);
        }

        return $Products;
    }

    public function getOrderList(int $ID) {
        \Bitrix\Main\Loader::includeModule("catalog");
        \Bitrix\Main\Loader::includeModule("sale");

        $order = \Bitrix\Sale\Order::load($ID);
        $basket = $order->getBasket();

        $result = [];

        foreach ($basket as $basketItem) {
            $productId = $basketItem->getProductId();

            // Получаем данные о товаре с индивидуальным кешированием
            $productData = $this->getProductData($productId);

            if ($productData) {
                $productData['BASKET_ID'] = $basketItem->getId();
                $productData['ORDER_ID'] = $ID;
                $productData['FINALPRICE'] = $basketItem->getFinalPrice();
                $productData['QUANTITY'] = $basketItem->getQuantity();
                $productData['PRICE'] = $basketItem->getPrice();
                $productData['MEASURE_NAME'] = $basketItem->getField("MEASURE_NAME");

                $result[$productId] = $productData;
            }
        }

        return $result;
    }

    /**
     * Получает данные о конкретном товаре с кешированием
     *
     * @param int $productId ID товара
     * @return array Данные о товаре
     */
    public function getProductData(int $productId) {
        global $CACHE_MANAGER;

        $cache = new \CPHPCache;
        $cacheTime = 3600; // Время жизни кеша - 1 час
        //$cacheTime = 0;
        $cacheId = 'product_data_' . $productId;
        $cacheDir = '/kabinet/order_products/';

        if ($cache->InitCache($cacheTime, $cacheId, $cacheDir)) {
            return $cache->GetVars();
        }

        $cache->StartDataCache();

        // Получаем данные о товаре
        $res = \CIBlockElement::GetList(
            [],
            [
                "IBLOCK_ID" => 1,
                "ID" => $productId,
            ],
            false,
            false,
            [
                'ID',
                'NAME',
                'CODE',
                'IBLOCK_SECTION',
                'PREVIEW_PICTURE',
                'PROPERTY_*',
                'CATALOG_PRICE_1',
            ]
        );

        if ($fields = $res->GetNext()) {
            $fieldsElement[(int)$fields['ID']] = $fields;
            $product[(int)$fields['ID']] = $fields;
            // Получаем свойства товара
            \CIBlockElement::GetPropertyValuesArray($product, 1,["IBLOCK_ID"=>1]);

            foreach ($product as $key => $item) {
                foreach ($item as $key2 => $item2) {
                    foreach ($item2 as $key3 => $item3) {
                        if (!in_array($key3, ['NAME', 'ID', 'CODE', 'VALUE','MULTIPLE','~VALUE','PROPERTY_TYPE','VALUE_XML_ID']))
                            unset($product[$key][$key2][$key3]);
                    }
                }
            }


            $product_ = [];
            foreach ($product as $key=>$itm){
                $picture = $fieldsElement[$key]['PREVIEW_PICTURE'];
                $arFileTmp = \CFile::ResizeImageGet(
                    $picture,
                    array("width" => 250, "height" => 127),
                    BX_RESIZE_IMAGE_PROPORTIONAL
                );
                $fieldsElement[$key]['PREVIEW_PICTURE_SRC'] = $arFileTmp['src'];
                $product_ = array_merge($itm,$fieldsElement[$key]);
            }


            // Устанавливаем тег для управления кешем
            if (defined("BX_COMP_MANAGED_CACHE")) {
                $CACHE_MANAGER->StartTagCache($cacheDir);
                $CACHE_MANAGER->RegisterTag("iblock_id_1_product_" . $productId);
                $CACHE_MANAGER->EndTagCache();
            }

            $cache->EndDataCache($product_);
            return $product_;
        }

        $cache->AbortDataCache();
        return false;
    }


    public function orderData($user_id = 0, $clear=false){
        global $CACHE_MANAGER;

        \Bitrix\Main\Loader::includeModule("catalog");
        \Bitrix\Main\Loader::includeModule("sale");

        if (!$user_id){
            $user = $this->user;
            $user_id = $user->get('ID');
        }

        $cacheId = '';
        $cacheId = SITE_ID."|".$cacheId;
        $cacheId .= "|".serialize(intval($user_id));

        $cache = new \CPHPCache;
        // Clear cache "kabinet/userorder"
        if ($clear) $cache->clean($cacheId, "kabinet/userorder");


        // for debugg!
        //$cache->clean($cacheId, "kabinet/userorder");

        // сколько времени кешировать
        $ttl = 14400;
        // hack: $ttl = 0 то не кешировать
        //$ttl = 0;

        if ($cache->StartDataCache($ttl, $cacheId, "kabinet/userorder")) {

            $parameters = [
                'filter' => [
                    "USER_ID" => $user_id,
                ],
                'order' => ["DATE_INSERT" => "ASC"]
            ];
            $dbRes = \Bitrix\Sale\Order::getList($parameters);
            $data = [];
            // перебераем все заказы пользователя
            while ($order = $dbRes->fetch()) {
                // берем товары из заказов
                $data[$order['ID']] = $this->getOrderList($order['ID']);
            }

            $cache->EndDataCache(array($data));
        }
        else
        {
            $vars = $cache->GetVars();
            $data = $vars[0];
        }

        return $data;
    }

    public function catalogData(){
        global $CACHE_MANAGER;
        \Bitrix\Main\Loader::includeModule("catalog");


        $cacheSalt = md5("catalog");
        $cacheId = SITE_ID."|".$cacheSalt;

        //$cache = new \CPHPCache;
        // Clear cache "catalog_data"
        //$cache->clean($cacheId, "kabinet/catalog");
        //$CACHE_MANAGER->ClearByTag("catalog_data");
        // сколько времени кешировать
        $ttl = 14400;

        $cache = new \CPHPCache;

        if ($cache->StartDataCache($ttl, $cacheId, "kabinet/catalog"))
        {

            $data = \Bitrix\iblock\ElementTable::getlist([
                'select'=>['ID','NAME','CODE','PREVIEW_PICTURE','IBLOCK_SECTION_ID'],
                'filter'=>['ACTIVE'=>1,'IBLOCK_ID'=>1],
                'order'=>['NAME'=>'asc']
            ])->fetchAll();

            foreach($data as &$itm){
				
				//Минимальное количество для заказа
				$MINIMUM_QUANTITY_MONTH = 1;
				$db_props = \CIBlockElement::GetProperty($IBLOCK_ID = 1, $itm['ID'], array("sort" => "asc"), Array("CODE"=>"MINIMUM_QUANTITY_MONTH"));
				$ar_props = $db_props->Fetch();
				if ($ar_props["VALUE"]>0) $MINIMUM_QUANTITY_MONTH = $ar_props["VALUE"];
				
				//Максимальное количество в месяц
				$MAXIMUM_QUANTITY_MONTH = 0;
				$db_props = \CIBlockElement::GetProperty($IBLOCK_ID = 1, $itm['ID'], array("sort" => "asc"), Array("CODE"=>"MAXIMUM_QUANTITY_MONTH"));
				$ar_props = $db_props->Fetch();
				if ($ar_props["VALUE"]) $MAXIMUM_QUANTITY_MONTH = $ar_props["VALUE"];				
				
				$section = \Bitrix\iblock\SectionTable::getlist(['select'=>['ID','NAME','CODE'],'filter'=>['ID'=>$itm['IBLOCK_SECTION_ID']],'limit'=> 1])->fetch();						
                $ar_res = \CPrice::GetBasePrice($itm['ID'], false);
                $itm['PRICE'] = floor($ar_res['PRICE'])." ".$ar_res["CURRENCY"];
                $picture = $itm['PREVIEW_PICTURE'];
                $arFileTmp = \CFile::ResizeImageGet(
                    $picture,
                    array("width" => 250, "height" => 127),
                    BX_RESIZE_IMAGE_PROPORTIONAL
                );
                $itm['PREVIEW_PICTURE_SRC'] = $arFileTmp['src'];
                $itm['VIEW'] = true;
                $itm['COUNT'] = $MINIMUM_QUANTITY_MONTH;
				$itm['LINK'] = '/zakaz/'.$section['CODE'].'/'.$itm['CODE'].'/';
				$itm['MINIMUM_QUANTITY_MONTH'] = $MINIMUM_QUANTITY_MONTH;
				$itm['MAXIMUM_QUANTITY_MONTH'] = $MAXIMUM_QUANTITY_MONTH;
            }

            $cache->EndDataCache(array($data));
        }
        else
        {
            $vars = $cache->GetVars();
            $data = $vars[0];
        }


        return $data;
    }

    public function addproductNewOrder(int $product_id, int $quantity){
        \Bitrix\Main\Loader::includeModule("catalog");
        \Bitrix\Main\Loader::includeModule("sale");

        $user = $this->user;
        $user_id = $user->get('ID');

        $basket = \Bitrix\Sale\Basket::create('s1');
        $order = \Bitrix\Sale\Order::create('s1', $user_id);
        // 1 – ID типа плательщика
        $order->setPersonTypeId(1);
        $order->setBasket($basket);

        $item = $basket->createItem('catalog', $product_id);
        $item->setFields(array(
            'CURRENCY' => \Bitrix\Currency\CurrencyManager::getBaseCurrency(),
            'LID' => \Bitrix\Main\Context::getCurrent()->getSite(),
            'PRODUCT_PROVIDER_CLASS' => '\Bitrix\Catalog\Product\CatalogProvider',
            "QUANTITY"=> $quantity,
        ));
        $basket->refresh();

        //товары добавляются в первую попавшуюся НЕ СИСТЕМНУЮ отгрузку.
        $shipmentCollection = $order->getShipmentCollection();
        $shipment = $shipmentCollection->createItem(
            \Bitrix\Sale\Delivery\Services\Manager::getObjectById(1) // 1 – ID службы доставки
        );
        $shipmentItemCollection = $shipment->getShipmentItemCollection();
        foreach ($order->getBasket() as $newBasketItem) {
            $shipmentItem = $shipmentItemCollection->createItem($newBasketItem);
            $shipmentItem->setQuantity($newBasketItem->getQuantity());
        }

        $paymentCollection = $order->getPaymentCollection();
        $payment = $paymentCollection->createItem(
            \Bitrix\Sale\PaySystem\Manager::getObjectById(6) // 1 – ID платежной системы
        );
        $payment->setField("SUM", $order->getPrice());
        $payment->setField("CURRENCY", $order->getCurrency());


        $discount = $order->getDiscount();
        \Bitrix\Sale\DiscountCouponsManager::clearApply(true);
        \Bitrix\Sale\DiscountCouponsManager::useSavedCouponsForApply(true);
        $discount->setOrderRefresh(true);
        $discount->setApplyResult(array());

        /** @var \Bitrix\Sale\Basket $basket */
        if (!($basket = $order->getBasket())) {
            throw new \Bitrix\Main\ObjectNotFoundException('Entity "Basket" not found');
        }

        $basket->refreshData(array('PRICE', 'COUPONS'));
        $discount->calculate();
        $result = $order->save();
        if (!$result->isSuccess())
        {
            $err = $result->getErrors();
            $mess = $err[0]->getMessage();
            throw new ProjectException($mess);
        }

        // Сбрассываем кеширование у пользователя
        $this->orderData($user_id, true);


        $ID = $result->getID();
        return $ID;
    }

    public function addproductToOrder(int $order_id,int $product_id, int $quantity){
        \Bitrix\Main\Loader::includeModule("catalog");
        \Bitrix\Main\Loader::includeModule("sale");

        $order = \Bitrix\Sale\Order::load($order_id); //по ID заказа
        $basket = $order->getBasket();

        $item = $basket->createItem('catalog', $product_id);
        $item->setFields(array(
            'CURRENCY' => \Bitrix\Currency\CurrencyManager::getBaseCurrency(),
            'LID' => \Bitrix\Main\Context::getCurrent()->getSite(),
            'PRODUCT_PROVIDER_CLASS' => '\Bitrix\Catalog\Product\CatalogProvider',
            "QUANTITY"=> $quantity,
        ));
        $basket->refresh();

        //товары добавляются в первую попавшуюся НЕ СИСТЕМНУЮ отгрузку.
        $shipmentCollection = $order->getShipmentCollection();
        foreach ($shipmentCollection as $shipment) {
            if (!$shipment->isSystem()) {
                foreach ($order->getBasket() as $newBasketItem) {
                    /** @var \Bitrix\Sale\Shipment $shipment */
                    $shipmentItemCollection = $shipment->getShipmentItemCollection();
                    $shipmentItem = $shipmentItemCollection->createItem($newBasketItem);
                    $shipmentItem->setQuantity($newBasketItem->getQuantity());
                }
                break;
            }
        }

        $discount = $order->getDiscount();
        \Bitrix\Sale\DiscountCouponsManager::clearApply(true);
        \Bitrix\Sale\DiscountCouponsManager::useSavedCouponsForApply(true);
        $discount->setOrderRefresh(true);
        $discount->setApplyResult(array());

        /** @var \Bitrix\Sale\Basket $basket */
        if (!($basket = $order->getBasket())) {
            throw new \Bitrix\Main\ObjectNotFoundException('Entity "Basket" not found');
        }

        $basket->refreshData(array('PRICE', 'COUPONS'));
        $discount->calculate();
        $result = $order->save();
        if (!$result->isSuccess())
        {
            $err = $result->getErrors();
            $mess = $err[0]->getMessage();
            throw new ProjectException($mess);
        }

        // Сбрассываем кеширование у пользователя
        $this->orderData(0, true);
    }

    public function removeproductToOrder(int $order_id,int $basketItem_id){
        \Bitrix\Main\Loader::includeModule("catalog");
        \Bitrix\Main\Loader::includeModule("sale");

        $order = \Bitrix\Sale\Order::load($order_id);
        $basket = $order->getBasket();
        $basketItem = $basket->getItemById($basketItem_id);
        if ($basketItem)
        {
            $basketItem->delete();
        }

        $result = $order->save();
        if (!$result->isSuccess())
        {
            $err = $result->getErrors();
            $mess = $err[0]->getMessage();
            throw new ProjectException($mess);
        }

        // Сбрассываем кеширование у пользователя
        $this->orderData(0, true);
    }

}