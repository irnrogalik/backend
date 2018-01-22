<?
require_once($_SERVER['DOCUMENT_ROOT'] . "/bitrix/modules/main/include/prolog_before.php");
?>
<link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.min.css" integrity="sha384-BVYiiSIFeK1dGmJRAkycuHAHRg32OmUcww7on3RYdg4Va+PmSTsz/K68vbdEjh4u" crossorigin="anonymous">
<?
if (CModule::IncludeModule("iblock")) {
	$arSelect = Array("ID", "IBLOCK_ID", "NAME", "DETAIL_PAGE_URL", "DATE_ACTIVE_FROM");
	$arFilter = Array("IBLOCK_ID"=>4, "DETAIL_TEXT" => "%<a %");
	$res = CIBlockElement::GetList(Array(), $arFilter, false, false, $arSelect);
	while($ob = $res->GetNextElement())
	{
		$arFields = $ob->GetFields();
		$result[] = ['NAME' => $arFields['NAME'], 'DETAIL_PAGE_URL' => $arFields['DETAIL_PAGE_URL']];
	}
}

?>
<div class="container">
<ul class="list-group">
<? foreach($result as $val) { ?>
	<li class="list-group-item"><a href="<?=$val['DETAIL_PAGE_URL']?>"><?=$val['NAME']?></a></li>
<? } ?>
</ul>
</div>