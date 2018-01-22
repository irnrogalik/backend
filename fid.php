<?
set_time_limit(0);
ini_set("memory_limit", "512M");
header("Content-Type: text/plain; charset=cp1251");
header("Content-Disposition: attachment; filename=file.txt");
header("Cache-Control: no-cache, no-store, must-revalidate"); // HTTP 1.1
header("Pragma: no-cache"); // HTTP 1.0
header("Expires: 0"); // Proxies

require_once($_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/main/include/prolog_before.php"); 
$domain = $_SERVER['REQUEST_SCHEME'].'://'.$_SERVER['HTTP_HOST'];
CModule::IncludeModule('iblock');

$arSelect = ["ID", "IBLOCK_ID", "NAME", "PREVIEW_PICTURE", "DETAIL_PICTURE", "DETAIL_PAGE_URL",  "CATALOG_GROUP_1"];
$arFilter = ["IBLOCK_ID"=>4, "ACTIVE" => "Y", "PROPERTY_IN_STOCK" => "Y", "GLOBAL_ACTIVE" => "Y"];
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
	$mp = str_replace(",",".",$mp);
	$image_id = $arFields['PREVIEW_PICTURE']?$arFields['PREVIEW_PICTURE']:$arFields['DETAIL_PICTURE'];
	$Result[] = [
		"id" => $arFields['ID'],
		"name" => $arFields['NAME'],
		"url" => $domain.$arFields['DETAIL_PAGE_URL'],
		"image_filename" => $domain.CFile::GetPath($image_id),
		"price" => $mp,
	];
}



$fp = fopen("php://output", "w");

$line = "id,title,link,image link,price,item address";
$line = "Item title,ID,Final URL,Image URL,Price";
fputcsv($fp, split(',', $line),"	");

foreach($Result as $item){
	if(!$item["price"]) continue;
	if(!$item["name"]) continue;
	$line = array($item["name"], $item["id"], $item["url"], $item["image_filename"], $item["price"]." BYN");
	fputcsv($fp, $line,"	");
}

fclose($fp);


?>