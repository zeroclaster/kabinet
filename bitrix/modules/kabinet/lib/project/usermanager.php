<?php
namespace Bitrix\Kabinet\project;

use \Bitrix\Main\SystemException;

class Usermanager extends \Bitrix\Kabinet\container\Base {
	protected $RESTRICT_TIME = 180; // sec.

    public function __construct(int $id, $HLBCClass)
    {
        parent::__construct($id, $HLBCClass);

    }

    public function findTable($HL_BLOCK,$id){
        $HLBClass = (\KContainer::getInstance())->get($HL_BLOCK);

        $findedId = $HLBClass::getlist([
            'select'=>['ID'],
            'filter'=>['UF_PROJECT_ID'=>$id],
            'limit'=>1
        ])->fetch();

        if (!$findedId) return false;

        return $findedId['ID'];
    }

    public function add($fields){

        $HLBClass = (\KContainer::getInstance())->get(BRIEF_HL);

        // просеиваем поля
        $addFields = $this->siftFields($fields,BRIEF);
        $checkResult = $this->checkFields($addFields,BRIEF);
        if(!$checkResult) throw new SystemException($this->requiredField);
        

		if(!$this->RestrictForm()) throw new SystemException("Слишком много одновременных запросов!");

        $addFields = $this->addDefault($addFields);

        $obResult = $HLBClass::add($addFields);
        if (!$obResult->isSuccess()){
            $err = $obResult->getErrors();
            $mess = $err[0]->getMessage();
            throw new SystemException($mess);
        }

        $ID = $obResult->getID();

        // for debugg!!
        //$ID = 4;

        $addFields = $this->siftFields($fields,PROJECTSINFO);
        $addFields = array_merge($addFields,['UF_PROJECT_ID'=>$ID]);
        $checkResult = $this->checkFields($addFields,PROJECTSINFO);
        if(!$checkResult) throw new SystemException($this->requiredField);

        $HLBClass = (\KContainer::getInstance())->get(PROJECTSINFO_HL);
        $obResult = $HLBClass::add($addFields);
        if (!$obResult->isSuccess()){
            $err = $obResult->getErrors();
            $mess = $err[0]->getMessage();
            $this->delete($ID);
            throw new SystemException($mess);
        }


        $addFields = $this->siftFields($fields,PROJECTSDETAILS);
        $addFields = array_merge($addFields,['UF_PROJECT_ID'=>$ID]);
        $checkResult = $this->checkFields($addFields,PROJECTSDETAILS);
        if(!$checkResult) throw new SystemException($this->requiredField);

        $HLBClass = (\KContainer::getInstance())->get(PROJECTSDETAILS_HL);
        $obResult = $HLBClass::add($addFields);
        if (!$obResult->isSuccess()){
            $err = $obResult->getErrors();
            $mess = $err[0]->getMessage();
            $this->delete($ID);
            throw new SystemException($mess);
        }

        $addFields = $this->siftFields($fields,TARGETAUDIENCE);
        $addFields = array_merge($addFields,['UF_PROJECT_ID'=>$ID]);
        $checkResult = $this->checkFields($addFields,TARGETAUDIENCE);
        if(!$checkResult) throw new SystemException($this->requiredField);

        $addFields = array_merge($addFields,['UF_PROJECT_ID'=>$ID]);
        $HLBClass = (\KContainer::getInstance())->get(TARGETAUDIENCE_HL);
        $obResult = $HLBClass::add($addFields);
        if (!$obResult->isSuccess()){
            $err = $obResult->getErrors();
            $mess = $err[0]->getMessage();
            $this->delete($ID);
            throw new SystemException($mess);
        }

        $this->clearCache();

        return $ID;
    }

    public function update($fields){

        if (!$fields['id']) {
            $this->addError(new Error('Could not find id', 1));
            return null;
        }

        $id = $fields['id'];
        unset($fields['id']);

        $updateDATA = [];

        // просеиваем поля
        $updateDATA[BRIEF_HL]['FIELDS'] = $this->siftFields($fields,BRIEF);
        // делаем валидацию
        $checkResult = $this->checkFields($updateDATA[BRIEF_HL]['FIELDS'],BRIEF);
        //  определям ID
        $updateDATA[BRIEF_HL]['ID'] = $id;
        if(!$checkResult || !$updateDATA[BRIEF_HL]['ID']){
            throw new SystemException($this->requiredField);
        }

        // просеиваем поля
        $updateDATA[PROJECTSINFO_HL]['FIELDS'] = $this->siftFields($fields,PROJECTSINFO);
        // делаем валидацию
        $checkResult = $this->checkFields($updateDATA[PROJECTSINFO_HL]['FIELDS'],PROJECTSINFO);
        //  определям ID
        $updateDATA[PROJECTSINFO_HL]['ID'] = $this->findTable(PROJECTSINFO_HL,$id);
        if(!$checkResult || !$updateDATA[PROJECTSINFO_HL]['ID']){
            throw new SystemException($this->requiredField);
        }

        // просеиваем поля
        $updateDATA[PROJECTSDETAILS_HL]['FIELDS'] = $this->siftFields($fields,PROJECTSDETAILS);
        // делаем валидацию
        $checkResult = $this->checkFields($updateDATA[PROJECTSDETAILS_HL]['FIELDS'],PROJECTSDETAILS);
        //  определям ID
        $updateDATA[PROJECTSDETAILS_HL]['ID'] = $this->findTable(PROJECTSINFO_HL,$id);
        if(!$checkResult || !$updateDATA[PROJECTSDETAILS_HL]['ID']){
            throw new SystemException($this->requiredField);
        }

        // просеиваем поля
        $updateDATA[TARGETAUDIENCE_HL]['FIELDS'] = $this->siftFields($fields,TARGETAUDIENCE);
        // делаем валидацию
        $checkResult = $this->checkFields($updateDATA[TARGETAUDIENCE_HL]['FIELDS'],TARGETAUDIENCE);
        //  определям ID
        $updateDATA[TARGETAUDIENCE_HL]['ID'] = $this->findTable(TARGETAUDIENCE_HL,$id);
        if(!$checkResult || !$updateDATA[TARGETAUDIENCE_HL]['ID']){
            throw new SystemException($this->requiredField);
        }

        foreach ($updateDATA as $HL_BLK => $DATA) {
            if ($HL_BLK == BRIEF) $DATA['FIELDS'] = $this->editDefault($DATA['FIELDS']);
            else $DATA['FIELDS'] = array_merge($DATA['FIELDS'],['UF_PROJECT_ID'=>$id]);

            $HLBClass = (\KContainer::getInstance())->get($HL_BLK);
            $obResult = $HLBClass::update($DATA['ID'],$DATA['FIELDS']);
            if (!$obResult->isSuccess()){
                $err = $obResult->getErrors();
                $mess = $err[0]->getMessage();
                throw new SystemException($mess);
            }
        }

        $this->clearCache();

        return $id;
    }

    public function delete(int $id){
        $HLBClass = (\KContainer::getInstance())->get(BRIEF_HL);
        $HLBClass::delete($id);

        $HLBClass = (\KContainer::getInstance())->get(PROJECTSINFO_HL);
        $findedId = $HLBClass::getlist([
            'select'=>['ID'],
            'filter'=>['UF_PROJECT_ID'=>$id],
            'limit'=>1
        ])->fetch();
        $HLBClass::delete($findedId);

        $HLBClass = (\KContainer::getInstance())->get(PROJECTSDETAILS_HL);
        $findedId = $HLBClass::getlist([
            'select'=>['ID'],
            'filter'=>['UF_PROJECT_ID'=>$id],
            'limit'=>1
        ])->fetch();
        $HLBClass::delete($findedId);

        $HLBClass = (\KContainer::getInstance())->get(TARGETAUDIENCE_HL);
        $findedId = $HLBClass::getlist([
            'select'=>['ID'],
            'filter'=>['UF_PROJECT_ID'=>$id],
            'limit'=>1
        ])->fetch();
        $HLBClass::delete($findedId);

        $this->clearCache();
    }

    public function getUserFields($ID = 0){
        $fields = $GLOBALS["USER_FIELD_MANAGER"]->getUserFields('HLBLOCK_'.$ID,null,LANGUAGE_ID);
        return $fields;
    }

    // TODO akula сделать кеш массив помомо битрексового
    public function getData($clear=false,$user_id = []){
        global $CACHE_MANAGER;

        $saveData = [];

		if (!$user_id){
			$user = (\KContainer::getInstance())->get('user');
			$user_id = $user->get('ID');
		}

        $requestURL = $user_id;
        $cacheSalt = md5($requestURL);
        $cacheId = $requestURL."|".SITE_ID."|".$cacheSalt;

        $cache = new \CPHPCache;
        // Clear cache "project_data"
		if ($clear) $cache->clean($cacheId, "kabinet/project");
        //$CACHE_MANAGER->ClearByTag("project_data");

	
        $cache->clean($cacheId, "kabinet/project");

		// сколько времени кешировать
		$ttl = 14400;
		
		// hack: $ttl = 0 то не кешировать
		if (is_array($user_id)) $ttl = 0;

        $cache = new \CPHPCache;

        if ($cache->StartDataCache($ttl, $cacheId, "kabinet/project"))
        {
            if (defined("BX_COMP_MANAGED_CACHE"))
            {
                $CACHE_MANAGER->StartTagCache("project_data");
                //\CIBlock::registerWithTagCache(self::SERVICES_IBLOCK);
            }

            $listdata = \Bitrix\Kabinet\project\datamanager\ProjectsTable::getListActive([
                'select'=>['*','INFO','DETAILS','TARGETAUDIENCE'],
                'filter'=>['UF_AUTHOR_ID'=>$user_id],
                'order'=>["UF_PUBLISH_DATE"=>'DESC']
            ])->fetchAll();

            foreach ($listdata as &$proj) {

                foreach ($proj as $fieldName => $value) {
                    $clear = str_replace('KABINET_PROJECT_DATAMANAGER_PROJECTS_', '', $fieldName);
                    if (in_array($clear, ['INFO_ID', 'DETAILS_ID', 'TARGETAUDIENCE_ID'])) continue;
                    $clear = str_replace(['INFO_', 'DETAILS_', 'TARGETAUDIENCE_'], '', $clear);
                    $saveData[$clear] = $value;
                }

                $HL_BRIEF = $this->getUserFields(BRIEF);
                $HL_PROJECTSINFO = $this->getUserFields(PROJECTSINFO);
                $HL_PROJECTSDETAILS = $this->getUserFields(PROJECTSDETAILS);
                $HL_TARGETAUDIENCE = $this->getUserFields(TARGETAUDIENCE);


                foreach ($saveData as $name => &$value) {
                    if (!$value) continue;
                    $HL_FIELD_DATA = [];
                    if (isset($HL_BRIEF[$name])) $HL_FIELD_DATA = $HL_BRIEF[$name];
                    if (isset($HL_PROJECTSINFO[$name])) $HL_FIELD_DATA = $HL_PROJECTSINFO[$name];
                    if (isset($HL_PROJECTSDETAILS[$name])) $HL_FIELD_DATA = $HL_PROJECTSDETAILS[$name];
                    if (isset($HL_TARGETAUDIENCE[$name])) $HL_FIELD_DATA = $HL_TARGETAUDIENCE[$name];

                    // for debug!!
                    //echo "<pre>";
                    //var_dump($HL_FIELD_DATA);
                    //echo "</pre>";

                    if ($HL_FIELD_DATA) {

                        if ($HL_FIELD_DATA["USER_TYPE_ID"] == 'datetime') {
                            $value = $value->getTimestamp();
                        }

                        if (
                            $HL_FIELD_DATA["USER_TYPE_ID"] == "hlblock" ||
                            $HL_FIELD_DATA["USER_TYPE_ID"] == "kabinethlblock"
                        ) {
                            $HL_BLK = (\KContainer::getInstance())->get('HlBuilder')->get(
                                $HL_FIELD_DATA["SETTINGS"]["HLBLOCK_ID"]
                            );

                            $value = unserialize($value);
                            array_walk($value, function (&$item, $key, $HL_BLK) {
                                $data = $HL_BLK::getById($item)->fetch();
                                $item = $data['UF_NAME'];
                            }, $HL_BLK);
                        }elseif($HL_FIELD_DATA['MULTIPLE'] == 'Y'){
                            $value = unserialize($value);
                        }
                    }

                }

                /*
                foreach ($saveData as $name => &$value) {
                    if ($name == 'UF_ORDER_ID' && $saveData['UF_ORDER_ID']) {
                        $value = $this->getOrderList($saveData['UF_ORDER_ID']);
                        break;
                    }
                }
                */

                $proj = $saveData;
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

    public function clearCache(){
        $this->getData($clear=true);
    }

    public function getOrderList(int $ID){
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
                    'PROPERTY_*'
                ]
            );
            $fields = $res->GetNext();
            $fields['BASKET_ID'] = $basket_id;
            $fields['ORDER_ID'] = $ID;
			$fields['FINALPRICE'] = $basketItem->getFinalPrice();
			$fields['QUANTITY'] = $basketItem->getQuantity();
			$fields['PRICE'] = $basketItem->getPrice();
			
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

    public function orderData($user_id = 0, $clear=false){
        global $CACHE_MANAGER;

        \Bitrix\Main\Loader::includeModule("catalog");
        \Bitrix\Main\Loader::includeModule("sale");

        if (!$user_id){
            $user = (\KContainer::getInstance())->get('user');
            $user_id = $user->get('ID');
        }

        $cacheId = '';
        $cacheId = SITE_ID."|".$cacheId;
        $cacheId .= "|".serialize($user_id);

        $cache = new \CPHPCache;
        // Clear cache "kabinet/userorder"
        if ($clear) $cache->clean($cacheId, "kabinet/userorder");


        // for debugg!
        //$cache->clean($cacheId, "kabinet/userorder");

        // сколько времени кешировать
        $ttl = 14400;
        // hack: $ttl = 0 то не кешировать

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

        if ($cache->StartDataCache($ttl, $cacheId, "kabinet/project"))
        {

			$data = \Bitrix\iblock\ElementTable::getlist([
			'select'=>['ID','NAME','CODE','PREVIEW_PICTURE'],
			'filter'=>['ACTIVE'=>1,'IBLOCK_ID'=>1],
			'order'=>['NAME'=>'asc']
			])->fetchAll();
			
			foreach($data as &$itm){			
				$ar_res = \CPrice::GetBasePrice($itm['ID'], false);
				$itm['PRICE'] = $ar_res['PRICE']." ".$ar_res["CURRENCY"];	
				$picture = $itm['PREVIEW_PICTURE'];
				$arFileTmp = \CFile::ResizeImageGet(
					$picture,
					array("width" => 250, "height" => 127),
					BX_RESIZE_IMAGE_PROPORTIONAL
				);
				$itm['PREVIEW_PICTURE_SRC'] = $arFileTmp['src'];
				$itm['VIEW'] = true;
                $itm['COUNT'] = 0;
				
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

        $user = (\KContainer::getInstance())->get('user');
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
            throw new SystemException($mess);
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
            throw new SystemException($mess);
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
            throw new SystemException($mess);
        }

        // Сбрассываем кеширование у пользователя
        $this->orderData(0, true);
    }
}
