<?
require_once($_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/main/include/prolog_before.php");
global $DB;

$q = "SELECT ID FROM `b_iblock_element` WHERE IBLOCK_ID = 4"; 
$result = $DB->Query($q);

while ($row = $result->Fetch()) {
	$IDS[$row['ID']] = 0;
}

$q = "SELECT IBLOCK_ELEMENT_ID FROM `b_iblock_section_element`";
$result = $DB->Query($q);
while ($row = $result->Fetch()) {

	if($IDS[$row['IBLOCK_ELEMENT_ID']] !== null) {
		$IDS[$row['IBLOCK_ELEMENT_ID']]++;
	}
}
$cnt = 0;
foreach($IDS as $id) {
	if($id > 1) {
		$cnt++;
	}
}
print_r($cnt);
