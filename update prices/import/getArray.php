<?
   set_time_limit(0);
function getArray($File) {
    $Excel = PHPExcel_IOFactory::load($File);

    $rows = [];
    foreach ($Excel->getAllSheets() as $sheet) {
		$range = "E1:E".$sheet->getHighestRow();
		$sheet->getStyle($range)->getNumberFormat()->setFormatCode( PHPExcel_Style_NumberFormat::FORMAT_TEXT );
        $rows = array_merge($rows, $sheet->toArray());
    }
    foreach ($rows as $row) {
		if($row[0] && $row[4]) {
			$response[] = [$row[0], round($row[4])];
		}

    }

    return $response;
}

function updP($PRODUCT_ID, $price) {
    if (!CModule::IncludeModule('catalog')) {
        return;
    }
    $PRICE_TYPE_ID = 1;

    $arFields = Array(
        "PRODUCT_ID" => $PRODUCT_ID,
        "CATALOG_GROUP_ID" => $PRICE_TYPE_ID,
        "PRICE" => $price,
        "CURRENCY" => "RUB"
    );

    $res = CPrice::GetList(
                    array(), array(
                "PRODUCT_ID" => $PRODUCT_ID,
                "CATALOG_GROUP_ID" => $PRICE_TYPE_ID
                    )
    );

    if ($arr = $res->Fetch()) {
        if ($arr['PRICE'] != $price) {
            CPrice::Update($arr["ID"], $arFields);
            echo $PRODUCT_ID . " => " . $price . "<br>\r\n";
        }
    } else {
        CPrice::Add($arFields);
    }
}
