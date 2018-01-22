<?php

if (!SUB_ID || SUB_ID == ALL_CITIES) {
    die("А вот и нет!)))");
}

function rechar($str) {
    return mb_convert_encoding($str, 'utf-8');
}

function getArray() {
    CModule::IncludeModule('iblock');
    global $DB;
    $arSelect = Array("ID", "IBLOCK_ID", "PROPERTY_CML2_LINK", "CATALOG_GROUP_1", "PROPERTY_DECOR_ARTICLE");
    $arFilter = Array("IBLOCK_ID" => 24, "ACTIVE" => "Y", "PROPERTY_CITY" => SUB_ID);

    $lines['HEADERS'] = ["id", rechar("Категория"), rechar("Товар"), rechar("Артикул"), rechar("Цена")];

    $res = CIBlockElement::GetList(Array("PROPERTY_DECOR_ARTICLE" => "ASC"), $arFilter, false, false, $arSelect);
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

            $parent = CIBlockElement::GetByID($arFields['PROPERTY_CML2_LINK_VALUE']);
            $arParent = $parent->fetch();
            if ($in_49) {
                $arParent["IBLOCK_SECTION_ID"] = 49;
            }
            if ($arParent["IBLOCK_SECTION_ID"]) {
                $sect = CIBlockSection::GetByID($arParent["IBLOCK_SECTION_ID"]);
                $arSection = $sect->fetch();
            }

            $lines['VALUES'][$arSection["NAME"]][] = [$arFields["ID"], rechar($arSection["NAME"]), rechar($arParent["NAME"]), rechar($arFields["PROPERTY_DECOR_ARTICLE_VALUE"]), $arFields["CATALOG_PRICE_1"]];
        }
    };
    return $lines;
}
