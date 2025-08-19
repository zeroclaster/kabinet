<?php
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_before.php");

require_once $_SERVER["DOCUMENT_ROOT"]."/bitrix/php_interface/include/smtp/yandex.php";


ini_set('error_reporting', E_ALL);
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);

/*
ini_set('error_reporting', 0);
ini_set('display_errors', 0);
ini_set('display_startup_errors', 0);
*/

\Bitrix\Main\Loader::includeModule('kabinet');

$obResult = \Bitrix\Kabinet\billing\datamanager\TransactionTable::add([
	'SUM'=>$_REQUEST['summ'],
	'DATE_OPERATION'=> new \Bitrix\Main\Type\DateTime(),
	'USER_ID' => $_REQUEST['idclient'],
	'BILING_ID' => $_REQUEST['billing_id'],

]);
$orderID = $obResult->getID();


require_once './vendor/TCPDF-main/tcpdf.php';
require_once './vendor/autoload.php';
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Reader;
use PhpOffice\PhpSpreadsheet\Writer;
use PhpOffice\PhpSpreadsheet\Writer\Pdf\Tcpdf;
use PhpOffice\PhpSpreadsheet\Worksheet\Drawing;
use PhpOffice\PhpSpreadsheet\Worksheet\PageSetup;
use PhpOffice\PhpSpreadsheet\IOFactory;

/**

 * Возвращает сумму прописью

 * @author runcore

 * @uses morph(...)

 */

function num2str($num)
{
	$nul = 'ноль';
	$ten = array(
	array('', 'один', 'два', 'три', 'четыре', 'пять', 'шесть', 'семь', 'восемь', 'девять'),

		array('', 'одна', 'две', 'три', 'четыре', 'пять', 'шесть', 'семь', 'восемь', 'девять')

	);

	$a20 = array('десять', 'одиннадцать', 'двенадцать', 'тринадцать', 'четырнадцать', 'пятнадцать', 'шестнадцать', 'семнадцать', 'восемнадцать', 'девятнадцать');

	$tens = array(2 => 'двадцать', 'тридцать', 'сорок', 'пятьдесят', 'шестьдесят', 'семьдесят', 'восемьдесят', 'девяносто');

	$hundred = array('', 'сто', 'двести', 'триста', 'четыреста', 'пятьсот', 'шестьсот', 'семьсот', 'восемьсот', 'девятьсот');

	$unit = array(

		array('копейка' , 'копейки',   'копеек',     1),

		array('рубль',    'рубля',     'рублей',     0),

		array('тысяча',   'тысячи',    'тысяч',      1),

		array('миллион',  'миллиона',  'миллионов',  0),

		array('миллиард', 'миллиарда', 'миллиардов', 0),

	);

 

	list($rub, $kop) = explode('.', sprintf("%015.2f", floatval($num)));

	$out = array();

	if (intval($rub) > 0) {

		foreach (str_split($rub, 3) as $uk => $v) {

			if (!intval($v)) continue;

			$uk = sizeof($unit) - $uk - 1;

			$gender = $unit[$uk][3];

			list($i1, $i2, $i3) = array_map('intval', str_split($v, 1));

			// mega-logic

			$out[] = $hundred[$i1]; // 1xx-9xx

			if ($i2 > 1) $out[] = $tens[$i2] . ' ' . $ten[$gender][$i3]; // 20-99

			else $out[] = $i2 > 0 ? $a20[$i3] : $ten[$gender][$i3]; // 10-19 | 1-9

			// units without rub & kop

			if ($uk > 1) $out[] = morph($v, $unit[$uk][0], $unit[$uk][1], $unit[$uk][2]);

		}

	} else {

		$out[] = $nul;

	}

	$out[] = morph(intval($rub), $unit[1][0], $unit[1][1], $unit[1][2]); // rub

	$out[] = $kop . ' ' . morph($kop, $unit[0][0], $unit[0][1], $unit[0][2]); // kop

	return trim(preg_replace('/ {2,}/', ' ', join(' ', $out)));

}

 

/**

 * Склоняем словоформу

 * @author runcore

 */

function morph($n, $f1, $f2, $f5) 

{

	$n = abs(intval($n)) % 100;

	if ($n > 10 && $n < 20) return $f5;

	$n = $n % 10;

	if ($n > 1 && $n < 5) return $f2;

	if ($n == 1) return $f1;

	return $f5;

}

$UR = false;
$FIZ = false;

$curDate = new \DateTime();
$summ = str_replace(".",",",$_REQUEST['summ']);

if(in_array($_REQUEST['usertype'],[2,3,4])) $UR = true;
if(in_array($_REQUEST['usertype'],[1])) $FIZ = true;

$tpl = './schot.xlsx';
//$reader = new Reader\Xls();
$reader = new Reader\Xlsx();

$pExcel = $reader->load($tpl);

$pExcel->setActiveSheetIndex(0);
$aSheet = $pExcel->getActiveSheet();

$cell = $aSheet->getCell('A1');
$val = $cell->getValue();



$cell = $aSheet->getCell('B1');
$val = $cell->getValue();



$nomer = "№".$curDate->format("dm-y").$_REQUEST['idclient']."-".$orderID;


$aSheet->getCell('I9')->setValue($nomer." от ".$curDate->format("d.m.Y"));

if ($UR) $aSheet->getCell('E11')->setValue($_REQUEST['nazvanie_organizacii'].", ИНН: ".$_REQUEST['inn'].", КПП: ".$_REQUEST['kpp'].", ОГРН: ".$_REQUEST['ogrn'].", ".$_REQUEST['ur_addres']." ");

if ($FIZ ) $aSheet->getCell('E11')->setValue($_REQUEST['fio'].", ".$_REQUEST['mail_addres'].", ".$_REQUEST['act']." ");

$aSheet->getCell('C14')->setValue("Пополнение баланса личного кабинета №".$_REQUEST['idclient'].", оплата услуг сервиса \"Купи-Отзыв\" по Счету-оферте ".$nomer.".");

$aSheet->getCell('S14')->setValue($summ);
$aSheet->getCell('Y14')->setValue($summ);
$aSheet->getCell('Y15')->setValue($summ);
$aSheet->getCell('Y17')->setValue($summ);
$aSheet->getCell('K19')->setValue($summ);

$aSheet->getCell('B20')->setValue(num2str($_REQUEST['summ']));


$pictloc =__DIR__ .  '/images/schot_html_1cc9b250f66c1de0.png';
if(file_exists($pictloc)){
    $Drawing = new Drawing();
    $Drawing->setPath($pictloc);
    $Drawing->setCoordinates('P22');
    $Drawing->setOffsetX(0);
    $Drawing->setOffsetY(0);
    $Drawing->setWorksheet($aSheet);
    //$Drawing->setResizeProportional(true);
   // $Drawing->setWidthAndHeight(1000, 1003;
}


/*
// Лого-шапка
$pictloc =__DIR__ .  '/images/schot_html_743f1a64c509ffd6.png';
if(file_exists($pictloc)){
    $Drawing = new Drawing();
    $Drawing->setPath($pictloc);
    $Drawing->setCoordinates('B1');
    $Drawing->setOffsetX(0);
    $Drawing->setOffsetY(0);
    
    //$Drawing->setResizeProportional(true);
    $Drawing->setHeight(300);
    $Drawing->setWorksheet($aSheet);
    //$Drawing->setWidthAndHeight(481, 324);
}
*/


$filename = 'Счет №'.$curDate->format("dm-Y").$_REQUEST['idclient'].$_REQUEST['billing_id'].'.pdf';

//~ $aSheet->getPageSetup()->setOrientation(PageSetup::ORIENTATION_PORTRAIT);
$aSheet->getPageSetup()->setOrientation(PageSetup::ORIENTATION_DEFAULT);
$aSheet->getPageSetup()->setPaperSize(PageSetup::PAPERSIZE_A4_PLUS_PAPER);


/*
 $writer = IOFactory::createWriter($pExcel, "Xlsx");
$writer->save("image.xlsx");
exit;
*/

IOFactory::registerWriter('Pdf', Tcpdf::class);
$writer = IOFactory::createWriter($pExcel, 'Pdf');

if ($_REQUEST['sendemail']) {
	$filename = 'Счет №'.$curDate->format("dm-Y").$_REQUEST['idclient'].$_REQUEST['billing_id'].'.pdf';
    $res = [];
    $writer->save(__DIR__ .'/'.$filename);

    $additional_headers =
        'X-Priority: 3 (Normal)'.
        'Date: Tue, 18 Jun 2024 08:23:51 +0200'.
        'MIME-Version: 1.0'.
        'X-MID: 2014.30 (18.06.2024 08:23:51)'.
        'Content-Type: text/html; charset=UTF-8'.
        'Content-Transfer-Encoding: 8bit'
    ;
    $additional_parameters = '';

    mail_cast(
        $_REQUEST['emailclient'],
        "Оплата по счету",
        "Банковский перевод по квитанции",
        $additional_headers,
        $additional_parameters,
        [$filename=>__DIR__ .'/'.$filename]
    );

    $res['data'] = ['file'=>$filename];
    exit(json_encode($res));
}


//$writer->save(__DIR__ .'/'.$filename);
header('Content-Type: application/pdf');
header('Content-Disposition: inline;filename="'.$filename.'"');
header('Cache-Control: max-age=0');
$writer->save('php://output');
exit;
