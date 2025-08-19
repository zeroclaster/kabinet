<?
//ini_set('error_reporting', E_ALL);
//ini_set('display_errors', 1);
//ini_set('display_startup_errors', 1);

/*
ini_set('error_reporting', 0);
ini_set('display_errors', 0);
ini_set('display_startup_errors', 0);
*/
require_once './vendor/TCPDF-main/tcpdf.php';
require_once './vendor/autoload.php';

use PhpOffice\PhpWord\Reader;

use PhpOffice\PhpWord\Writer\Pdf\Tcpdf;
use PhpOffice\PhpWord\Worksheet\Drawing;
use PhpOffice\PhpWord\Worksheet\PageSetup;
use PhpOffice\PhpWord\IOFactory;

$UR = false;
$FIZ = false;


$contracttype = [
    1=>"Физического лица",
    2=>"Индивидуального предпринимателя",
    3=>"Директора организации",
    4=>"Генерального директора организации",
];


if(in_array($_REQUEST['usertype'],[2,3,4])) $UR = true;
if(in_array($_REQUEST['usertype'],[1])) $FIZ = true;

$phpword = new \PhpOffice\PhpWord\TemplateProcessor('dogovor.docx');

$phpword->setValue('IDCLIENT',$_REQUEST['idclient']);

if ($UR) {
    $phpword->setValue('TEXT1', $_REQUEST['nazvanie_organizacii'].', ИНН: '.$_REQUEST['inn'].', именуемое(-ый) в дальнейшем «Заказчик», в лице '.$contracttype[$_REQUEST['usertype']]. ': '.$_REQUEST['fio'].', действующий  на основании '.$_REQUEST['act']);
}
if ($FIZ) {
    $phpword->setValue('TEXT1', $_REQUEST['fio'].', '.$_REQUEST['mail_addres'].', '.$_REQUEST['act'].', ');
}

$phpword->setValue('FIO', $_REQUEST['fio']);

$MES = array(
    "01" => "Января",
    "02" => "Февраля",
    "03" => "Марта",
    "04" => "Апреля",
    "05" => "Мая",
    "06" => "Июня",
    "07" => "Июля",
    "08" => "Августа",
    "09" => "Сентября",
    "10" => "Октября",
    "11" => "Ноября",
    "12" => "Декабря"
);
$curDate = new \DateTime();
$day = $curDate->format("d");
$year = $curDate->format("Y");
$m = $MES[$curDate->format("m")];

$date = $day. ' '.$m . ' '.$year;

$phpword->setValue('DATE', $date);

if ($_REQUEST["dowloaddate"])
	$phpword->setValue('DATE2', $_REQUEST["dowloaddate"]);
else
	$phpword->setValue('DATE2', $curDate->format("dm-y"));


// Банковские реквезиты
$phpword->setValue('BIK', $_REQUEST['bik']);
$phpword->setValue('KORRSCHOT', $_REQUEST['korr_schet']);
$phpword->setValue('PS', $_REQUEST['raschetnyj_schet']);
$phpword->setValue('BANKNAME', $_REQUEST['nazvanie_banka']);

if ($UR) {
    $phpword->setValue('REQ_ORGNAME', $_REQUEST['nazvanie_organizacii']);
    $phpword->setValue('INN_TITLE', 'ИНН:');
    $phpword->setValue('OGRN_TITLE', 'ОГРНИП:');
    $phpword->setValue('UF_ADDRES_TITLE', 'Юридический Адрес:');



}
if ($FIZ) {
    $phpword->setValue('REQ_ORGNAME', $_REQUEST['fio']);
    $phpword->setValue('INN_TITLE', '');
    $phpword->setValue('OGRN_TITLE', '');
    $phpword->setValue('UF_ADDRES_TITLE', '');
}


$phpword->setValue('INN', $_REQUEST['inn']);
$phpword->setValue('OGRN', $_REQUEST['ogrn']);
$phpword->setValue('MAILADDRES', $_REQUEST['mail_addres']);
$phpword->setValue('UR_ADDRES', $_REQUEST['ur_addres']. '.');
$phpword->setValue('PHONE', $_REQUEST['phoneclient']);
$phpword->setValue('EMAIL', $_REQUEST['emailclient']);


//$phpword->saveAs('edited.docx');

$filename = 'kupi-otziv.ru Договор №'.$curDate->format("dm-y").$_REQUEST['idclient'].'.docx';

header('Content-Type: application/msword');
header('Content-Disposition: attachment;filename="'.$filename.'"');
header('Cache-Control: max-age=0');
$phpword->saveAs('php://output');
