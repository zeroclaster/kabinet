<?
if (\PHelp::isAdmin()) {
    include $_SERVER["DOCUMENT_ROOT"].'/kabinet/admin/.top.menu.php';
}else
$aMenuLinks = Array(
	Array(
		"Заказы", 
		"/kabinet/", 
		Array(), 
		Array("ICON"=>"mdi-file-outline"), 
		"" 
	),
	Array(
		"Финансы", 
		"/kabinet/finance/", 
		Array(), 
		Array("ICON"=>"mdi-border-all"), 
		"" 
	),
	Array(
		"Проекты", 
		"/kabinet/projects/", 
		Array(), 
		Array("ICON"=>"mdi-calendar-text","NOLINK"=>"Y"),
		"" 
	),
);
?>