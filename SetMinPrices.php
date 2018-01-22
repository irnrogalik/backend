<? 
require_once($_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/main/include/prolog_before.php"); 

CModule::IncludeModule('iblock');

$arSelect = ["ID", "IBLOCK_ID", "CATALOG_GROUP_1"];
$arFilter = ["IBLOCK_ID"=>4, "ACTIVE" => "Y"];
$res = CIBlockElement::GetList(Array(), $arFilter, false, false, $arSelect);
while($ob = $res->GetNextElement())
{
	$arFields = $ob->GetFields();
	$offers = getOffersWithMinPrice($arFields["ID"]);
	if(is_array($offers) && !empty($offers)) {
		$mp = $offers[0]['MIN_PRICE']['DISCOUNT_VALUE'];
	} else {
		$mp = $arFields["CATALOG_PRICE_1"];
	}

	CIBlockElement::SetPropertyValuesEx($arFields["ID"], 4, ["MINIMUM_PRICE" => $mp]);
}
