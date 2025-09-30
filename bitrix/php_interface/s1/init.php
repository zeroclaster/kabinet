<?
//session_start();
define("REGISTRATED", 10);
define("MANAGER", 12);
include_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/php_interface/functions/city.php");

include_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/php_interface/functions/registration.php");


AddEventHandler("main", "OnProlog", "GetCityName", 50);

function GetCityName()
{
	global $APPLICATION;

	session_start();

	CModule::IncludeModule('highloadblock');
	CModule::IncludeModule('sale');
	
	$request = \Bitrix\Main\Context::getCurrent()->getRequest();
	
	$basket = Bitrix\Sale\Basket::loadItemsForFUser(Bitrix\Sale\Fuser::getId(), Bitrix\Main\Context::getCurrent()->getSite());
	if ($basket->count() ){
		$request->modifyByQueryString('isbasket='.true);		
	}

	//print_r($_SERVER['REMOTE_ADDR']);

	//AddMessage2Log(print_r($get_city,true), "my_module_id");
	//print_r($get_city);	

	$hlblock   = \Bitrix\Highloadblock\HighloadBlockTable::getById(1)->fetch();
	$entity   = \Bitrix\Highloadblock\HighloadBlockTable::compileEntity( $hlblock );
	$entityClass = $entity->getDataClass();

	$cityid = $request->get('cityid');
	$city = $request->get('city');
	
	if (!$cityid && !$city && empty($_SESSION['cityid'])){
		$ip_geo_city = alxgeoip::GetCity();
					
			$is_object = $entityClass::getlist(['select'=>['*'],'filter'=>['UF_REGION'=>$ip_geo_city[0],'UF_ACTIVE'=>1,],'limit'=>1]);
			//echo \Bitrix\Main\Entity\Query::getLastQuery();
			$item_object = $is_object->fetch();
			if ($item_object){
				$request->modifyByQueryString('cityid='.$item_object["ID"]);
			}
	} 
	
	
	if (!empty($_SESSION['cityid'])) {				
		$request->modifyByQueryString('cityid='.$_SESSION['cityid']);
	}

	$cityid = $request->get('cityid');	

	if ($cityid || $city )
	{	
			//var_dump($cityid);			
			if (empty($item_object)){
				if (!empty($cityid)){
					$filter_m = ['ID'=>(int)$cityid,'UF_ACTIVE'=>1,];
				}else if (!empty($city)){
					$filter_m = ['UF_CODE'=>$city,'UF_ACTIVE'=>1,];
				}
						
				$is_object = $entityClass::getlist(['select'=>['*'],'filter'=>$filter_m,'limit'=>1]);
				//echo \Bitrix\Main\Entity\Query::getLastQuery();
				$item_object = $is_object->fetch();
			}
			
			if ($item_object)
			{
					//var_dump($item_object);
					$iblock_section_region = $item_object["UF_SECTIONREGION"];
					$GLOBALS['cityname_filter'] = array(
							'SECTION_ID' => $iblock_section_region,
					);					
					$APPLICATION->SetPageProperty("email", $item_object["UF_EMAIL"]);
					$APPLICATION->SetPageProperty("phone1", $item_object["UF_PHONE1"]);
					$APPLICATION->SetPageProperty("phone2", $item_object["UF_PHONE2"]);
					$APPLICATION->SetPageProperty("worktime", $item_object["UF_WORK"]);
					$APPLICATION->SetPageProperty("address1", $item_object["UF_ADDRESS1"]);
					$APPLICATION->SetPageProperty("citychangeid", $item_object["ID"]);
					$APPLICATION->SetPageProperty("citychangename", $item_object["UF_NAME"]);
					$APPLICATION->SetPageProperty("citychangecode", $item_object["UF_CODE"]);
					$APPLICATION->SetPageProperty("region", $item_object["UF_REGION"]);
					$APPLICATION->SetPageProperty("xmlid", $item_object["UF_XML_ID"]);


					$request->modifyByQueryString('cityid='.$item_object["ID"]);
					$_SESSION['cityid'] = $item_object["ID"];
			}
	}
}


// Site help utilities
class tzskokna{
	
	public static function showBlock($show_value){
			global $USER;
			$ret = '';

			if (!defined('SITE_ADMIN_IP') || SITE_ADMIN_IP == 'N') return $ret;
			
			if(COption::GetOptionString("main", "component_cache_on", "Y")=="Y")  return $ret;

			if (
				(defined('SITE_ADMIN_IP') && $_SERVER["REMOTE_ADDR"] == SITE_ADMIN_IP) || 
				$USER->IsAdmin()
			){
				$get_debug_info = debug_backtrace();
				$ret = '<!--'.$show_value.' (выводится из '.removeDocRoot($get_debug_info[0]['file']).' строка ' .$get_debug_info[0]['line']. ') -->';
			}
			
			//var_dump(debug_backtrace());

			return $ret;
	}
	
	public static function city_list(){
			CModule::IncludeModule('highloadblock');

			$rows = [];

			$request = \Bitrix\Main\Context::getCurrent()->getRequest();	
			
			

			//AddMessage2Log(print_r($get_city,true), "my_module_id");
			//print_r($get_city);	

			$hlblock   = \Bitrix\Highloadblock\HighloadBlockTable::getById(1)->fetch();
			$entity   = \Bitrix\Highloadblock\HighloadBlockTable::compileEntity( $hlblock );
			$entityClass = $entity->getDataClass();

			$cityid = $request->get('cityid');
					
			$is_object = $entityClass::getlist(['select'=>['*'],'filter'=>['UF_ACTIVE'=>1,]]);
			//echo \Bitrix\Main\Entity\Query::getLastQuery();
				
			$action = 'N';	
			while ($item_object = $is_object->fetch())
			{
				$action = 'N';	
				if ($item_object['ID'] == $cityid) $action = 'Y';
				$rows[] = ['item' => $item_object, 'action' => $action];
			}	

			return $rows;	
	}
	
	public static function interesovat($arParams,$arResult){
			global $USER_FIELD_MANAGER;

			$arResult['ITERESOVAT'] = [];

			//if (empty($arResult['SECTION']['UF_SHOWIN'])) return $arResult['ITERESOVAT'];

			$current_section = $arResult['SECTION']['ID'];
			
			
		$arFilter = array(
			"IBLOCK_ID"=>$arParams["IBLOCK_ID"],
			"GLOBAL_ACTIVE"=>"Y",
			"IBLOCK_ACTIVE"=>"Y",			
			"UF_SHOWIN2" => $current_section,
		);
		$arOrder = array(
			"sort"=>"asc",
		);

		$rsSections = CIBlockSection::GetList($arOrder, $arFilter, false);	
		while ($arSect = $rsSections->GetNext())
		{
			$arResult['ITERESOVAT'][] = $arSect;
		}
		
		return $arResult['ITERESOVAT'];
	}	
}


function my_onBeforeResultAdd($WEB_FORM_ID, &$arFields, &$arrVALUES)
{
  global $APPLICATION,$USER;

   session_start();

   CModule::IncludeModule('iblock'); 
 
  if ($WEB_FORM_ID == 4) 
  {
    $table = '<table>'; 

		$table = $table.'<tr>';
		$table = $table.'<td>Название</td><td>Цена</td><td>Куда</td><td>Стеклопакет</td><td>Фурнитура</td><td>Доставка</td><td>Подъем на этаж</td><td>Данные</td>';
		$table = $table.'</tr>';	
    
	$calc_data = $arrVALUES['form_textarea_12'];
	$js_ = json_decode($calc_data);
	foreach($js_->items as $one_item){
		$table = $table.'<tr>';
		$table = $table.'<td>'.$one_item->name.'</td><td>'.$one_item->priceparse.'</td><td>'.$one_item->kuda.'</td><td>'.$one_item->steclopaket.'</td><td>'.$one_item->fornitura.'</td><td>'.$one_item->dostavka.'</td><td>'.$one_item->podem.'</td><td>'.implode(',',$one_item->info).'</td>';
		$table = $table.'</tr>';
	}
	
	$table = $table.'</table>';
	
	$arrVALUES['form_textarea_12'] = $table;
	
  }
  
  if ($WEB_FORM_ID == 7) {
				$params = array(
					"max_len" => "100",
					"change_case" => "L",
					"replace_space" => "_",
					"replace_other" => "_",
					"delete_repeat_replace" => "true",
					"use_google" => "false",
				);
				
				$add_user_ = 0;
				$save_user_id = intval($APPLICATION->get_cookie("REVIEWUSERID"));
				
				$fio 		= trim($arrVALUES['form_text_22']);
				$phone 		= trim($arrVALUES['form_text_23']);
				$email 		= trim($arrVALUES['form_text_24']);
				$message 	= strip_tags($arrVALUES['form_textarea_25']);
				$rat        = trim($arrVALUES['rat_val']);

				echo "<script>console.log('".$rat."');</script>";
				
				$el = new CIBlockElement;
				$arLoadProductArray = Array(
				  //"MODIFIED_BY"    => $USER->GetID(), // элемент изменен текущим пользователем
				  //"IBLOCK_SECTION_ID" => false,          // элемент лежит в корне раздела
				  "IBLOCK_ID"      => 8,
				  "NAME"           => $fio,
				  "ACTIVE"         => "Y",
				  "CODE"			=> CUtil::translit($fio, "ru", $params),
				  'PROPERTY_VALUES' => [16=>'Нет'],
				);
				
				if (!empty($email)){
					$email_ = '<a class="mail" href="mailto:'.$email.'">'.$email.'</a>';
					$arLoadProductArray['PROPERTY_VALUES'][14] = $email_;
				}
				if (!empty($phone)){
					$phone_ = '<a class="phone" href="tel:'.$phone.'">'.$phone.'</a>';
					$arLoadProductArray['PROPERTY_VALUES'][13] = $phone_;
				}				

				if (empty($save_user_id)){
						if($USER_ID_REVIEWS = $el->Add($arLoadProductArray)){
							$add_user_ = $USER_ID_REVIEWS;
						}else{
							$APPLICATION->ThrowException('Ошибка при создании отзыва 8');
						}
				}else{
						$add_user_ = $save_user_id;
				}				

				if (!empty($add_user_)){
						$new_review_obj = new CIBlockElement;
						$arLoadReviewArray = Array(
						  "MODIFIED_BY"    => $USER->GetID(), // элемент изменен текущим пользователем
						  "IBLOCK_SECTION_ID" => 54,         
						  "IBLOCK_ID"      => 6,
						  "NAME"           => 'Отзыв от '.$fio. date("d.m.Y"),
						  "ACTIVE"         => "N",
						  "PREVIEW_TEXT"   => $message,
						  "DETAIL_TEXT"    => $message,
						  "CODE"			=> CUtil::translit('Отзыв от '.$fio. date("d.m.Y"), "ru", $params),
						  "PROPERTY_VALUES"	=> [12=>$add_user_, 42=>$rat],
						  "ACTIVE_FROM"	=> ConvertTimeStamp(time(), "FULL"),
						);
						if($ID_REVIEWS = $new_review_obj->Add($arLoadReviewArray)){}else{
							$APPLICATION->ThrowException('Ошибка при создании отзыва 6');
						}

						$APPLICATION->set_cookie("REVIEWUSERID", $add_user_, time()+60*60*24*30*12*2, "/");	
				}
	
	 
  }
}
AddEventHandler('form', 'onBeforeResultAdd', 'my_onBeforeResultAdd');


function onAfterResultAddUpdate($WEB_FORM_ID, $RESULT_ID)
{ 
  if ($WEB_FORM_ID == 4) 
  {
  	$today = date("Y-m-d H:i");
    // запишем в дополнительное поле 'user_ip' IP-адрес пользователя
     CFormResult::SetField($RESULT_ID, 'NUMBER', date("ym-d").'' . sprintf('%03d', $RESULT_ID));
   // CFormResult::SetField($RESULT_ID, 'new_user', $_SERVER["REMOTE_ADDR"]);	
	//CFormResult::SetField($RESULT_ID, 'date_time', $today);
  }  
}

AddEventHandler('form', 'onAfterResultAdd', 'onAfterResultAddUpdate');


AddEventHandler("main", "OnBeforeEventAdd", array("Changemail", "OnBeforeEventAddHandler"));
class Changemail
{
    static public function OnBeforeEventAddHandler(&$event, &$lid, &$arFields)
    {
    	global $APPLICATION;
    	if (!empty($email)) $arFields['SEND_EMAIL_RAW'] = $APPLICATION->GetPageProperty("email");
    }
}


//Подключаем класс ComponentHelper.php
require_once(dirname(__FILE__).'/classes/ComponentHelper.php');

//Функция, отвечающая за вывод "хлебных крошек" bitrix:breadcrumb
function ShowNavChain($template = '.default')
{
    global $APPLICATION;

    $APPLICATION->IncludeComponent("bitrix:breadcrumb", $template, Array(
        "START_FROM" => "0",
        "PATH" => "",
        "SITE_ID" => "s1"
	    ),
	    false
	);
}

AddEventHandler("main", "OnAfterUserRegister", "OnAfterUserRegisterHandler");
function OnAfterUserRegisterHandler(&$arFields)
{
    if ($arFields["RESULT_MESSAGE"] && $arFields["RESULT_MESSAGE"]["TYPE"] == "ERROR") return;

    $userObj = new CUser;
    $userObj->Update($arFields['USER_ID'], [
        'UF_TELEGRAM_NOTFI' => "1",
        'UF_EMAIL_NOTIFI'=>"37"   //Получать уведомления по email 3-5 раз, утром днем и вечером
    ]);

    if ($_POST['FROM_TELEGRAM'] == '1' && !empty($_POST['UF_TELEGRAM_ID'])) {
        $userObj->Update($arFields['USER_ID'], ['UF_TELEGRAM_ID' => $_POST['UF_TELEGRAM_ID']]);

        // Обработка фото, если нужно
        if (!empty($_SESSION['TELEGRAM_REGISTER_DATA']['photo_url'])) {
            $photoPath = $_SESSION['TELEGRAM_REGISTER_DATA']['photo_url'];

            $userObj->Update($arFields['USER_ID'], ['PERSONAL_PHOTO' => CFile::MakeFileArray($photoPath)]);
            unlink($photoPath);
        }

        unset($_SESSION['TELEGRAM_REGISTER_DATA']);
    }
}