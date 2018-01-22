<?php
if ($_FILES["file"]) {
    require_once($_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/main/include/prolog_before.php");
    require_once($_SERVER["DOCUMENT_ROOT"] . "/shared/functions/pricer/PHPExcel.php");
    include 'getArray.php';
    set_time_limit(0);
    
    $newfile = $_FILES["file"]['name'] . time() . ".xlsx";
    if (copy($_FILES["file"]['tmp_name'], $newfile)) {
        $new_prices = getArray($newfile);
        foreach($new_prices as $id_Price) {
            updP($id_Price[0], $id_Price[1]);
        } 
    }
}
?>
<form method="post" enctype="multipart/form-data">
    <input type="file" name="file">
    <input type="submit">
</form>