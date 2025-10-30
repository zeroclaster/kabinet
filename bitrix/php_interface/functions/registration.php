<?php

AddEventHandler("main", "OnBeforeUserLogin", function(&$arFields)
{
    global $APPLICATION;
    /*
    if($_SERVER["REMOTE_ADDR"]!='109.195.177.149'){
    $APPLICATION->throwException("Вход временно заблокирован!");
    return false;
    }
    */

    $filter = array("=EMAIL" => $arFields["LOGIN"],'GROUPS_ID'=>[REGISTRATED,MANAGER]);
    $filter2 = array("LOGIN" => $arFields["LOGIN"],'GROUPS_ID'=>[REGISTRATED,MANAGER]);
    $rsUsers = \CUser::GetList(($by="LAST_NAME"), ($order="asc"), $filter);
    $rsUsers2 = \CUser::GetList(($by="LAST_NAME"), ($order="asc"), $filter2);
    if($user = $rsUsers->GetNext()) {
        //$result_intersect = array_intersect([PARTNERS,ADVERTISER], \CUser::GetUserGroup($user['ID']));
        $arFields["LOGIN"] = $user["LOGIN"];
    }elseif($user = $rsUsers2->GetNext()){
        //$arFields["LOGIN"] = $user["LOGIN"];
    }else{

        $APPLICATION->throwException("Неверный логин или пароль!");
        return false;
    }
}
);



AddEventHandler("main", "OnAfterUserLogin", "OnAfterUserLoginHandler");
function OnAfterUserLoginHandler(&$fields)
{
    global $APPLICATION,$DB;
    $context = \Bitrix\Main\Application::getInstance()->getContext();
    $server = $context->getServer();
    $request = $context->getRequest();
    $register = $request->get('register');


    if($fields['USER_ID']>0 && $register == NULL)
    {
        //$APPLICATION->set_cookie(COption::GetOptionString("main", "cookie_name", "BITRIX_SM")."_EMAIL", $arFields["EMAIL"]);

        $arGroups = CUser::GetUserGroup($fields['USER_ID']);
        //AddMessage2Log(print_R(array_intersect([6], CUser::GetUserGroup($fields['USER_ID'])),true), "my_module_id");

        //$DB->Query("INSERT INTO b_portal_user_activity (DATE,USER,VALUE) VALUES(".$DB->CurrentTimeFunction().",".$fields['USER_ID'].",1)", false);

        if(!empty(array_intersect([REGISTRATED], CUser::GetUserGroup($fields['USER_ID'])))){
            //ничего не делаем!
            //LocalRedirect("/kabinet/");
            //exit();
        };

        if(!empty(array_intersect([MANAGER], CUser::GetUserGroup($fields['USER_ID'])))){
            LocalRedirect("/kabinet/admin/");
            exit();
        };
    }
}

