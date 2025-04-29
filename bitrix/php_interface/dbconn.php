<?
define("BX_USE_MYSQLI", true);
define("DBPersistent", false);
$DBType = "mysql";
$DBHost = "localhost";
$DBLogin = "kupi_otziv_r";
$DBPassword = "Vfe1VnURNY1WDTMx";
$DBName = "kupi_otziv_r";
$DBDebug = true;
$DBDebugToFile = false;

define("DELAY_DB_CONNECT", true);
define("CACHED_b_file", 3600);
define("CACHED_b_file_bucket_size", 10);
define("CACHED_b_lang", 3600);
define("CACHED_b_option", 3600);
define("CACHED_b_lang_domain", 3600);
define("CACHED_b_site_template", 3600);
define("CACHED_b_event", 3600);
define("CACHED_b_agent", 3660);
define("CACHED_menu", 3600);

define("BX_UTF", true);
define("BX_FILE_PERMISSIONS", 0644);
define("BX_DIR_PERMISSIONS", 0755);
@umask(~(BX_FILE_PERMISSIONS|BX_DIR_PERMISSIONS)&0777);
@ini_set("memory_limit", "512M");
define("BX_DISABLE_INDEX_PAGE", true);
//define("BX_COMP_MANAGED_CACHE", true);

define("LOG_FILENAME", $_SERVER["DOCUMENT_ROOT"]."/log.txt");

//error_reporting(E_ALL);
//ini_set('display_errors', 1);

date_default_timezone_set("Etc/GMT-3");

@define("ERROR_404","N");
?>