<?php

function rechar($str) {
    return mb_convert_encoding($str, 'utf-8');
}

function getArray($from, $to) {
    
    $products = [];
    CModule::IncludeModule('iblock');
    global $DB;
    $arSelect = Array("ID", "IBLOCK_ID", "PROPERTY_CML2_LINK", "CATALOG_GROUP_1", "PROPERTY_DECOR_ARTICLE", "PROPERTY_CITY");
    $arFilter = Array("IBLOCK_ID" => 24, "ACTIVE" => "Y", "PROPERTY_CITY" => [$from, $to]);

    
    $res = CIBlockElement::GetList(Array(), $arFilter, false, false, $arSelect);
    while ($ob = $res->GetNextElement()) {
        $arFields = $ob->GetFields();
        if ($arFields["PROPERTY_CML2_LINK_VALUE"]) {
            $q = "SELECT * FROM `b_iblock_section_element` WHERE `IBLOCK_ELEMENT_ID` = {$arFields["PROPERTY_CML2_LINK_VALUE"]}"; // if(49) 
            $in_49 = false;
            $result = $DB->Query($q);
            while ($row = $result->Fetch()) {
                if ($row['IBLOCK_SECTION_ID'] == 49) {
                    $in_49 = true;
                    break;
                }
            }
            if ($in_49) {
               continue;
            }
          
            $products[$arFields['PROPERTY_CML2_LINK_VALUE']][$arFields['PROPERTY_CITY_VALUE']][$arFields['PROPERTY_DECOR_ARTICLE_VALUE']] = [$arFields["ID"],$arFields["CATALOG_PRICE_1"]];

        }
    }
    return $products;
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