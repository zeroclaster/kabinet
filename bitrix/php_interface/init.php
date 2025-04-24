<?
//session_start();
require_once 'include/smtp/yandex.php';
function custom_mail($to, $subject, $message, $additional_headers, $additional_parameters)
{
	AddMessage2Log($to . '|' . $subject. '|' .$message. '|' .$additional_headers. '|' .$additional_parameters, "my_module_id");
				mail_cast($to, $subject, $message, $additional_headers, $additional_parameters);
	//return  @mail($to, $subject, $message, $additional_headers, $additional_parameters);
	return true;
}

define('VUEJS_DEBUG', true);

// BITRIX_SM_TESTRUN в корне файл akula.php
if ($_COOKIE["BITRIX_SM_TESTRUN"])
    define("AKULA", 1);
else
    define("AKULA", 0);

/*
* KABINET_SCRIPT - необходим для javascript data файлов
*/
if(CSite::InDir('/kabinet/') || AKULA || defined('KABINET_SCRIPT')) {
	CModule::IncludeModule('kabinet');
}

class Dbg{
	
		static function var_dump($var){
		$container = \KContainer::getInstance();
		ob_start();
		echo "<pre>".(debug_backtrace())[0]['file'].'<br>';
		var_dump($var);
		echo "</pre>";
		$out = trim(ob_get_contents());
		ob_end_clean();
		$out = str_replace("/var/www/kupi_otziv_r_usr/data/www/kupi-otziv.ru","",$out);
		$PR = $container->get("PRINT_R");
		if (!$PR) $PR = '';
		$PR = $PR . $out;
		$container->maked($PR,"PRINT_R");
	}	
	
		static function print_r($var){
		$container = \KContainer::getInstance();
		$out = "<pre>".(debug_backtrace())[0]['file'].'<br>'.print_r($var,true)."</pre>";
		$out = str_replace("/var/www/kupi_otziv_r_usr/data/www/kupi-otziv.ru","",$out);
		$PR = $container->get("PRINT_R");
		if (!$PR) $PR = '';
		$PR = $PR . $out;
		$container->maked($PR,"PRINT_R");
	}
	
	static function echo_($var){
		$container = \KContainer::getInstance();
		$out = str_replace("/var/www/kupi_otziv_r_usr/data/www/kupi-otziv.ru","",(debug_backtrace())[0]['file']);
		$out = $out.'<br>'.$var;
		$PR = $container->get("ECHO");
		if (!$PR) $PR = [];
		$PR[] = $out;
		$container->maked($PR,"ECHO");
	}	

	static function showDebug(){
		$container = \KContainer::getInstance();
		$PR = $container->get("PRINT_R");
		if ($PR) {
			echo "<div style='background-color:#FFFFFF;color:#000000!important;border:1px solid red;padding:20px;'>";
			echo $PR;
			echo "</div>";
		}
		
		$PR = $container->get("ECHO");
		if ($PR){
			foreach($PR as $itm){
				echo "<div style='background-color:#FFFFFF;color:#000000!important;border:1px solid red;padding:20px;'>";
				echo $itm;
				echo "</div>";			
			}
		}
	}	
}

AddEventHandler('main', 'OnEpilog', '_Check404Error', 1);
function _Check404Error()
{
	if (defined("ERROR_404") && ERROR_404 == "Y") {
		global $APPLICATION;
		$APPLICATION->RestartBuffer();

		require($_SERVER["DOCUMENT_ROOT"] . "/bitrix/templates/PAGE404/header.php");
		require($_SERVER["DOCUMENT_ROOT"] . "/404.php");
		require($_SERVER["DOCUMENT_ROOT"] . "/bitrix/templates/PAGE404/footer.php");
	}
}

// Site help utilities
class exiterra
{

	public static function showBlock($show_value)
	{
		global $USER;
		$ret = '';

		if (!defined('SITE_ADMIN_IP') || SITE_ADMIN_IP == 'N') return $ret;

		if (COption::GetOptionString("main", "component_cache_on", "Y") == "Y")  return $ret;

		if (
			(defined('SITE_ADMIN_IP') && $_SERVER["REMOTE_ADDR"] == SITE_ADMIN_IP) ||
			$USER->IsAdmin()
		) {
			$get_debug_info = debug_backtrace();
			$ret = '<!--' . $show_value . ' (выводится из ' . removeDocRoot($get_debug_info[0]['file']) . ' строка ' . $get_debug_info[0]['line'] . ') -->';
		}

		//var_dump(debug_backtrace());

		return $ret;
	}
}


class MyHtmlRedactorType extends CUserTypeString
{

	public static function GetUserTypeDescription()
	{
		global $APPLICATION;
		$APPLICATION->AddHeadScript('/bitrix/templates/main/js/colspan.js');
		return array(
			"USER_TYPE_ID" => "c_string",
			"CLASS_NAME" => "MyHtmlRedactorType",
			"DESCRIPTION" => "Строка в html редакторе",
			"BASE_TYPE" => "string",
			"WITH_DESCRIPTION" => "N"
		);
	}
	public function GetEditFormHTML($arUserField, $arHtmlControl)
	{
		ob_start();
		CFileMan::AddHTMLEditorFrame($arHtmlControl["NAME"], $arHtmlControl["VALUE"], "html", "html", 440, "N", 0, "", "", $arIBlock["LID"]);
		$b = ob_get_clean();
		$b .= '<div class="myc_string"></div>';
		return $b;
	}
}

AddEventHandler("main", "OnUserTypeBuildList", array("MyHtmlRedactorType", "GetUserTypeDescription"));



//------------------------------------------------------------------------------------------------------------------------------------------------------

AddEventHandler("iblock", "OnAfterIBlockElementDelete", array("MyClass", "OnAfterIBlockElementDeleteHandler"));
AddEventHandler("iblock", "OnAfterIBlockElementAdd", array("MyClass", "OnAfterIBlockElementAddHandler"));
AddEventHandler("iblock", "OnAfterIBlockElementUpdate", array("MyClass", "OnAfterIBlockElementUpdateHandler"));

class MyClass
{
	function createTagElement($tag_)
	{
		global $USER;

		$params = array(
			"max_len" => "100",
			"change_case" => "L",
			"replace_space" => "_",
			"replace_other" => "_",
			"delete_repeat_replace" => "true",
			"use_google" => "false",
		);

		$el = new CIBlockElement;
		$arLoadProductArray = array(
			"MODIFIED_BY"    => $USER->GetID(),
			"IBLOCK_SECTION_ID" => 18,     // Tags     http://tzskokna.test.exiterra.ru/bitrix/admin/iblock_element_admin.php?IBLOCK_ID=6&type=SERVICES&lang=ru&find_section_section=18&SECTION_ID=18&apply_filter=Y
			"IBLOCK_ID"      => 6,
			"NAME"           => trim($tag_),
			"ACTIVE"         => "Y",
			"CODE"		   => CUtil::translit(trim($tag_), "ru", $params) . '_tag',
		);

		if ($PRODUCT_ID = $el->Add($arLoadProductArray)) {
			//echo "New ID: ".$PRODUCT_ID;
			//AddMessage2Log( "New ID: ".$PRODUCT_ID, "my_module_id");
		} else {
			//echo "Error: ".$el->LAST_ERROR;
			// AddMessage2Log( "Error: ".$el->LAST_ERROR, "my_module_id");
		}
	}


	function createNewTags($arFields)
	{

		$tags = $arFields['TAGS'];
		if (!empty($tags)) {

			$tags_array = explode(',', $tags);

			//AddMessage2Log(print_r($tags_array,true), "my_module_id");

			foreach ($tags_array as $tag_) {

				$arElements = \Bitrix\Iblock\ElementTable::getList(
					array(
						"select" => ['ID'],
						"filter" => ['IBLOCK_ID' => 6, 'NAME' => trim($tag_)],
						"limit" => 1
					)
				);

				if ($row = $arElements->fetch()) {
					//AddMessage2Log( \Bitrix\Main\Entity\Query::getLastQuery(), "my_module_id");
				} else {

					MyClass::createTagElement($tag_);
				}
			}
		}
	}


	// создаем обработчик события "OnAfterIBlockElementUpdate"
	static function OnAfterIBlockElementUpdateHandler(&$arFields)
	{

		//Разделы сайта
		if ($arFields['IBLOCK_ID'] == 6) {

			//AddMessage2Log(print_r($arFields,true), "my_module_id");

			MyClass::createNewTags($arFields);
		}
	}

    static function OnAfterIBlockElementAddHandler(&$arFields)
	{
		//Разделы сайта
		if ($arFields['IBLOCK_ID'] == 6) {

			//AddMessage2Log(print_r($arFields,true), "my_module_id");
			MyClass::createNewTags($arFields);
		}
	}

    static function OnAfterIBlockElementDeleteHandler($arFields)
	{
		//Разделы сайта
		if ($arFields['IBLOCK_ID'] == 6) {
		}
	}
}


AddEventHandler("sale", "OnOrderNewSendEmail", "customOrderEmail");

function customOrderEmail($orderID, &$eventName, &$arFields)
{
	if ($eventName == "SALE_NEW_ORDER") {
		$order = \Bitrix\Sale\Order::load($orderID);
		$propertyCollection = $order->getPropertyCollection();
		$phonePropValue = $propertyCollection->getPhone();
		$phone = $phonePropValue->getValue();
		$arFields["PHONE"] = $phone;
		$arFields["COMMENT_USER"] = $order->getField('USER_DESCRIPTION');

		$ORDER_LIST_ARRAY = explode("<br/>", $arFields["ORDER_LIST"]);
		$arFields["ORDER_LIST"] = "";
		foreach ($ORDER_LIST_ARRAY as $ORDER_LIST_ELEM) {
			if($ORDER_LIST_ELEM!="") $arFields["ORDER_LIST"] = $arFields["ORDER_LIST"] . "<tr><td style='padding: 10px;'>" . str_replace(" x ", "</td><td style='text-align: center;'>", str_replace(" - ", "</td><td style='text-align: center; font-weight: bold;'>", $ORDER_LIST_ELEM)) . "</td></tr>";
		}
	}
}


AddEventHandler("sale", 'OnSaleOrderSaved', "OnSaleOrderSaved");



function OnSaleOrderSaved($order){
		
	\CModule::IncludeModule('sale');
	\Bitrix\Main\Loader::includeModule("catalog");
	\Bitrix\Main\Loader::includeModule("form");

	//$order = \Bitrix\Sale\Order::load(282);
	//$order = $event->getParameter("ENTITY");

	$price = $order->getPrice();
	$basket = $order->getBasket();

	$ord = '<ul><li>Суммма заказа: <b>'.$price.'</b></li>';
	foreach($basket->getBasketItems() as $item){

		$id = $item->getProductId();  // ID товара
		$Quantity = $item->getQuantity();

		/*
		$ar_res = CCatalogProduct::GetByID($id);
		echo "<br>Товар с кодом ".$id." имеет следующие параметры:<pre>";
		print_r($ar_res);
		echo "</pre>";
		*/
		
		$Element = \Bitrix\IBlock\ElementTable::getById($id)->fetch();
		$ar_res = \CPrice::GetBasePrice($id);
		
		
		//echo "<pre>";
		//print_r($Element['NAME'].' '.$ar_res['PRICE']);
		//echo "</pre>";	

		$ord .=  '<li>'.$Element['NAME'].' <b>'.$ar_res['PRICE']. "</b> x ".$Quantity. "</li>";
	}
	$ord .=  '</ul>';



	$propertyCollection = $order->getPropertyCollection();
	$emailPropValue = $propertyCollection->getUserEmail();
	$namePropValue  = $propertyCollection->getPayerName();
	$phonePropValue = $propertyCollection->getPhone();
	//$locPropValue   = $propertyCollection->getDeliveryLocation();
	//$taxLocPropValue = $propertyCollection->getTaxLocation();
	//$profNamePropVal = $propertyCollection->getProfileName();
	//$zipPropValue   = $propertyCollection->getDeliveryLocationZip();	
	//$addrPropValue  = $propertyCollection->getAddress();

	if ($namePropValue) $post['name'] = $namePropValue->getValue();
	if ($emailPropValue) $post['email'] = $emailPropValue->getValue();
	if ($phonePropValue) $post['phone'] = $phonePropValue->getValue();
	$post['message'] = $ord; 


	// Send form
	$FORM_ID = 5;		//ORDER	kupi-otziv.ru Заказы
	$REQ = [
	'form_text_13'		=>$post['name'],
	'form_text_14'		=>$post['phone'],
	'form_text_15'		=>$post['email'],
	'form_textarea_16'	=>$post['message'],
	];
	$error = \CForm::Check($FORM_ID, $REQ);
	// если метод не вернул текст ошибки, то
	if (strlen($error)>0) echo 'Check Error';;
	
	if($RESULT_ID = \CFormResult::Add($FORM_ID, $REQ))
	{
					// send email notifications
					\CFormCRM::onResultAdded($FORM_ID, $RESULT_ID);
					\CFormResult::SetEvent($RESULT_ID);
					\CFormResult::Mail($RESULT_ID);								
	}else{
		$error = $GLOBALS["strError"];
		
	}	
	
}





$eventManager = \Bitrix\Main\EventManager::getInstance();


\Bitrix\Main\Loader::registerAutoloadClasses(
    null,
    array(
        'CUserTypeRichText' => '/bitrix/modules/kabinet/lib/usertypes/usertyperichtext.php',
        'Richtype' => '/bitrix/modules/kabinet/lib/usertypes/types/richtype.php',

        //с допами к highload-блоков
        'CKabinetUserTypeHlblock' => '/bitrix/modules/kabinet/lib/usertypes/cusertypehlblock.php',
        'RangeType' => '/bitrix/modules/kabinet/lib/usertypes/types/rangetype.php',
        'CUserTypeRange' => '/bitrix/modules/kabinet/lib/usertypes/usertyperange.php',
        'CKabinetLightHTMLEditor' => '/bitrix/modules/kabinet/class/general/light_editor.php',
    )
);


//Вешаем обработчик на событие создания списка пользовательских свойств OnUserTypeBuildList
$eventManager->addEventHandler('main', 'OnUserTypeBuildList', ['CUserTypeRichText', 'getUserTypeDescription']);

//с допами к highload-блоков
$eventManager->addEventHandler('main', 'OnUserTypeBuildList', ['CKabinetUserTypeHlblock', 'getUserTypeDescription']);
$eventManager->addEventHandler('main', 'OnUserTypeBuildList', ['CUserTypeRange', 'getUserTypeDescription']);


/*
 *  Вариант с добавление товаров
 */
/*
$eventManager->addEventHandler('sale', 'OnSaleComponentOrderShowAjaxAnswer', function($result){
	if(AKULA){

		$sL = \Bitrix\Main\DI\ServiceLocator::getInstance();
		$projectManager = $sL->get('Kabinet.Project');
		
		$list = $projectManager->getData();
		if (!$list){
            \Bitrix\Main\Loader::includeModule("catalog");
            \Bitrix\Main\Loader::includeModule("sale");

            $order = \Bitrix\Sale\Order::load($result['order']['ID']); //по ID заказа
            $basket = $order->getBasket();
            $product = [];
            foreach ($basket as $basketItem) {
                $product[] = $basketItem->getProductId();
            }
			$Fields =  [
				'UF_NAME' => 'Новый проект',
				'UF_TOPICS_LIST' => [0 => '1'],
				//'UF_ORDER_ID' => $result['order']['ID'],
                'UF_PRODUKT_ID' => $product,
				'UF_PROJECT_GOAL' => 'Данное поле необходимо заполнить!',
			];		
			$res = $projectManager->add($Fields);
		}
	}
});
*/

$eventManager->addEventHandler('sale', 'OnSaleComponentOrderShowAjaxAnswer', function($result){
    if(AKULA){

        $sL = \Bitrix\Main\DI\ServiceLocator::getInstance();
        $projectManager = $sL->get('Kabinet.Project');

        $list = $projectManager->getData();
        if (!$list){
            \Bitrix\Main\Loader::includeModule("catalog");
            \Bitrix\Main\Loader::includeModule("sale");

            $Fields =  [
                'UF_NAME' => 'Новый проект',
                //'UF_TOPICS_LIST' => [0 => '1'],
                'UF_ORDER_ID' => $result['order']['ID'],
                //'UF_PROJECT_GOAL' => 'Данное поле необходимо заполнить!',
            ];
            $res = $projectManager->add($Fields);

            $orderData = $projectManager->orderData();
            foreach($orderData[$result['order']['ID']] as $item) {
                $taskManager = $sL->get('Kabinet.Task');
                $taskManager->add([
                    'UF_PROJECT_ID' => $res,
                    'UF_PRODUKT_ID' => $item['ID'],
                ]);

            }
        }
    }
});