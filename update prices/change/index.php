<?
require_once($_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/main/include/prolog_before.php");
if(filter_input(INPUT_POST, 'from') && filter_input(INPUT_POST, 'to')) {
    require_once($_SERVER["DOCUMENT_ROOT"] . "/shared/functions/pricer/PHPExcel.php");
    include 'getArray.php';
    $from = filter_input(INPUT_POST, 'from');
    $to = filter_input(INPUT_POST, 'to');
    $arr = getArray($from, $to);
            print_r($_POST);

    foreach($arr as $prod) {
        foreach($prod[$from] as $art => $price) {
            
            if($prod[$to][$art]) {
                
                updP($prod[$to][$art][0],$price[1]);
            }
        }
    }
} else {
    global $subDomens;
    ?>
<form method="POST">
    <select name='from'>
        <? foreach($subDomens as $region => $arr) {?>
            <option value="<?=$arr["ID"]?>"><?=$region?></option>
        <? } ?>
    </select>
    <select name='to'>
        <? foreach($subDomens as $region => $arr) {?>
            <option value="<?=$arr["ID"]?>"><?=$region?></option>
        <? } ?>
    </select>
    <input type="submit">
</form>    
    <?
}


