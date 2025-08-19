<?
//ini_set('error_reporting', E_ALL);
//ini_set('display_errors', 1);
//ini_set('display_startup_errors', 1);

/*
ini_set('error_reporting', 0);
ini_set('display_errors', 0);
ini_set('display_startup_errors', 0);
*/
$DOCUMENT_ROOT = realpath(dirname(__FILE__)."/../..");

require_once $DOCUMENT_ROOT.'/ajax/dowload/helper.php';
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

// Получаем данные о работах
$works = [];
if (!empty($_REQUEST['works'])) {
    $works = json_decode($_REQUEST['works'], true);
}


$phpword = new \PhpOffice\PhpWord\TemplateProcessor('akt.docx');

$phpword->setValue('IDCLIENT',$_REQUEST['idclient']);

if ($UR) {
    $phpword->setValue('TEXT1', $_REQUEST['nazvanie_organizacii'].', ИНН: '.$_REQUEST['inn'].', именуемое(-ый) в дальнейшем «Заказчик», в лице '.$contracttype[$_REQUEST['usertype']]. ': '.$_REQUEST['fio'].', действующий  на основании '.$_REQUEST['act']);
}
if ($FIZ) {
    $phpword->setValue('TEXT1', $_REQUEST['fio'].', '.$_REQUEST['mail_addres'].', '.$_REQUEST['act'].', ');
}

$phpword->setValue('FIO', $_REQUEST['fio']);

$count_uslug = 0;

// Добавляем данные о работах в акт
if (!empty($works)) {
    $phpword->cloneRow('WORK_ITEM', count($works));

    $i = 1;
    $totalPrice = 0;
    foreach ($works as $work) {
        //$singlePrice = $work['ONE_PRICE'] ?? '0.00';
        $totalPrice += (float)$work['UF_MONEY_RESERVE'];

        $phpword->setValue("WORK_ITEM#$i", $i);
        $phpword->setValue("WORK_NAME#$i", $work['TASK_UF_NAME']);
        $phpword->setValue("WORK_MEASURE#$i", $work['MEASURE_NAME']);
        //$phpword->setValue("WORK_MONEY#$i", $singlePrice);
        $phpword->setValue("WORK_TOTALEMONEY#$i", $work['UF_MONEY_RESERVE']);
        $phpword->setValue("WORK_NUMBER#$i", $work['UF_NUMBER_STARTS']);
        $i++;
        $count_uslug++;
    }

    $totalPrice = number_format($totalPrice, 2, '.', '');
    $phpword->setValue("TOTAL_PRICE", $totalPrice);
    $phpword->setValue("TOTAL_PRICEPRINT", num2str($totalPrice));
}

$phpword->setValue('COUNT_USLUG', $count_uslug);

if ($_REQUEST['dogovordate']){
	$ID = $_REQUEST['dogovorid'];
	$DOGDATE = $_REQUEST['dogovordate'];
}else{
	$ID = $_REQUEST['regdateclient'];
	$DOGDATE = $_REQUEST['regdateclient'];
}


$Date1 = $_REQUEST['month'];
$actID = $Date1 ? (new DateTime($Date1))->format('dm-y') : "";
$actDate = $Date1 ? (new DateTime($Date1))->format('d.m.Y') : "";

//$actCreate = $Date1 ?(new DateTime($Date1))->modify('first day of next month')->format('d.m.Y'):"";
//$phpword->setValue('ACT_CREATE', $actCreate);

$endActDate = $_REQUEST['endactdate'] ?? null;
if ($endActDate) {
        $endDateObj = new DateTime($endActDate);
        $lastDayOfMonth = new DateTime($endDateObj->format('Y-m-t')); // Получаем последний день месяца
        
        if ($endDateObj->format('Y-m-d') === $lastDayOfMonth->format('Y-m-d')) {
            // Если endactdate - последний день месяца, то ACT_DATE - первое число следующего месяца
            $actDate_ = $endDateObj->modify('first day of next month')->format('d.m.Y');
        } else {
            // Иначе используем саму дату endactdate
            $actDate_ = $endDateObj->format('d.m.Y');
        }
		$phpword->setValue('ACT_CREATE', $actDate_);
}


$phpword->setValue('ACT_DATE', $actDate);
$phpword->setValue('ACT_ID', $actID.$_REQUEST['idclient']);


$phpword->setValue('ID', $ID);
$phpword->setValue('DOGDATE', $DOGDATE);

// Проверяем, есть ли дата договора и она больше или равна дате месяца акта
if (!empty($_REQUEST['dogovordate']) && !empty($_REQUEST['month'])) {
    $dogovorDate = new DateTime($_REQUEST['dogovordate']);
    $monthDate = new DateTime($_REQUEST['month']);

    if ($dogovorDate <= $monthDate) {
        $TEXT2 = "К Договору оказания услуг №".$_REQUEST['dogovorid']." от ".$_REQUEST['dogovordate']."г.";
    } else {
        $TEXT2 = "К Договору-оферты оказания услуг через программное средство, информационный продукт — сайт kupi-otziv.ru от ".$_REQUEST['regdateclient']."г.";
    }
} else {
    $TEXT2 = "К Договору-оферты оказания услуг через программное средство, информационный продукт — сайт kupi-otziv.ru от ".$_REQUEST['regdateclient']."г.";
}

$phpword->setValue('TEXT2', $TEXT2);

$endactdate = $_REQUEST['endactdate'];
$endactdate = $endactdate ? (new DateTime($endactdate))->format('d.m.Y') : "";
$phpword->setValue('ENDACT', $endactdate);

//$phpword->saveAs('edited.docx');
if ($_REQUEST['dogovordate'])
	$filename = 'kupi-otziv.ru Акт №'.$_REQUEST['dogovorid'].'.docx';
else
	$filename = 'kupi-otziv.ru Акт №'.$_REQUEST['regdateclient'].'.docx';

header('Content-Type: application/msword');
header('Content-Disposition: attachment;filename="'.$filename.'"');
header('Cache-Control: max-age=0');
$phpword->saveAs('php://output');
