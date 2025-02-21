<?
if (\PHelp::isAdmin()) {
    include $_SERVER["DOCUMENT_ROOT"].'/kabinet/admin/.top.menu.php';
}else
$aMenuLinks = Array(
	Array(
		"Проекты",
		"/kabinet/", 
		Array(), 
		Array("ICON"=>"fa fa-tachometer"),
		"" 
	),
	Array(
		"Финансы", 
		"/kabinet/finance/", 
		Array(), 
		Array("ICON"=>"fa fa-credit-card-alt"),
		"" 
	),
	Array(
		"Планирование",
		"/kabinet/projects/", 
		Array(), 
		Array("ICON"=>"fa fa-calendar","NOLINK"=>"Y"),
		"" 
	),
);
?>