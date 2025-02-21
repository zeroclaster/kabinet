<?
if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();
global $APPLICATION; 


$sL = \Bitrix\Main\DI\ServiceLocator::getInstance();
$projectManager = $sL->get('Kabinet.Project');
$saveData = $projectManager->getData();

foreach($saveData as $item){
	$aMenuLinksExt[] = Array(
			$item['UF_NAME'], 
			"/kabinet/projects/planning/?p=".$item['ID'],
			Array(), 
			Array(), 
			"" 
		);	
}
$aMenuLinksExt[] = Array(
		"Новый проект",
		"/kabinet/projects/breif/",
		Array(), 
		Array("ICON"=>"mdi-plus"),
		"" 
	);


$aMenuLinks = array_merge($aMenuLinks, $aMenuLinksExt);
?>