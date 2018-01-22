<?
 
require_once($_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/main/include/prolog_before.php");
require_once($_SERVER["DOCUMENT_ROOT"] . "/shared/functions/pricer/PHPExcel.php");
include 'getArray.php';
$arr = getArray(); // ['HEADERS' => [], 'VALUES' => ['#SECTION_NAME#' => [],'#SECTION_NAME#' => [],...'#SECTION_NAME#' => []]];

$objPHPExcel = new PHPExcel();



$activeList = 0;

foreach ($arr['VALUES'] as $listName => $values) {
	if(!count($values)) continue;
    if($activeList) {
        $objPHPExcel->createSheet();
    }
    $objPHPExcel->setActiveSheetIndex($activeList++);
    $objPHPExcel->getActiveSheet()->setTitle(rechar($listName));
    
    $objPHPExcel->getActiveSheet()->getStyle('E')->getNumberFormat()->setFormatCode('0.00');
    $char = "A";
    $rowCount = 1;
    foreach ($arr['HEADERS'] as $id => $val) {
        $objPHPExcel->getActiveSheet()->SetCellValue($char . $rowCount, rechar($val));
        $char++;
    }
    $rowCount++;
    
    foreach ($values as $value) {
        $char = "A";
        foreach ($value as $cell) {
            $objPHPExcel->getActiveSheet()->SetCellValue($char++ . $rowCount, rechar($cell));
        }
        $rowCount++;
    }
}


$objWriter = new PHPExcel_Writer_Excel2007($objPHPExcel);
$objWriter->save('result.xlsx');

?>
<a href="result.xlsx">result.xlsx</a>
