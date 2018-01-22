<?php

require_once($_SERVER['DOCUMENT_ROOT'] . "/bitrix/modules/main/include/prolog_before.php");

if (CModule::IncludeModule("iblock")) {

    $el = new CIBlockElement;

    $PROP = array();
    $PROP[156] = $_POST["ID"];
    $PROP[157] = $USER->GetID();
    $PROP[158] = $_POST["RATING"];

    $arLoadProductArray = Array(
        "MODIFIED_BY" => $USER->GetID(), // элемент изменен текущим пользователем
        "IBLOCK_SECTION_ID" => false, // элемент лежит в корне раздела
        "IBLOCK_ID" => 11,
        "PROPERTY_VALUES" => $PROP,
        "NAME" => $_POST["NAME"],
        "ACTIVE" => "N", // активен
        "DETAIL_TEXT" => $_POST["MESSAGE"]
    );

  
    if ($PRODUCT_ID = $el->Add($arLoadProductArray)){
          print_r("OK");
    }    
};

