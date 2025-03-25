<?
$this->queryExecute("SET NAMES 'utf8'");
$this->queryExecute('SET collation_connection = "utf8_unicode_ci"');

$connection = Bitrix\Main\Application::getConnection();
$connection->queryExecute("SET LOCAL time_zone='".date('P')."'");
?>