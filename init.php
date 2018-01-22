<?php
use Bitrix\Highloadblock as HL;

$data = GetHBlock(INFO, ["ID" => 1]);
foreach ($data[0] as $k => $el) {
    define($k, $el);
}
 
// highloadblock
function GetHBlock($hlblock_id, $arFilter = array(), $arSort = ["UF_SORT" => "ASC"], $arSelect = ["*"]) {
    CModule::IncludeModule("highloadblock");

    $hlblock = HL\HighloadBlockTable::getById($hlblock_id)->fetch();
    $entity = HL\HighloadBlockTable::compileEntity($hlblock);

    $entity_data_class = $entity->getDataClass();
    $entity_table_name = $hlblock['TABLE_NAME'];

    $sTableID = 'tbl_' . $entity_table_name;
    $rsData = $entity_data_class::getList(array(
                "select" => $arSelect,
                "filter" => $arFilter,
                "order" => $arSort
    ));
    $rsData = new CDBResult($rsData, $sTableID);
    while ($arRes = $rsData->Fetch()) {
        $Result[] = $arRes;
    }
    return $Result;
}

function addHBlock($hlblock_id, $data) {
    if (empty($data))
        return;
    CModule::IncludeModule("highloadblock");

    $hlblock = HL\HighloadBlockTable::getById($hlblock_id)->fetch();
    $entity = HL\HighloadBlockTable::compileEntity($hlblock);

    $entity_data_class = $entity->getDataClass();
    $entity_data_class::add($data);
}

function editHBlock($hlblock_id, $id, $data) {
    if (empty($data))
        return;
    CModule::IncludeModule("highloadblock");

    $hlblock = HL\HighloadBlockTable::getById($hlblock_id)->fetch();
    $entity = HL\HighloadBlockTable::compileEntity($hlblock);

    $entity_data_class = $entity->getDataClass();
    $entity_data_class::update($id, $data);
}

AddEventHandler("search", "BeforeIndex", array("SearchHandlers", "BeforeIndexHandler"));

class SearchHandlers {

    function BeforeIndexHandler($arFields) {
        $arrIblock = array(6); // ID iblock
        $arDelFields = array("DETAIL_TEXT", "PREVIEW_TEXT");
        if (CModule::IncludeModule('iblock') && $arFields["MODULE_ID"] == 'iblock' && in_array($arFields["PARAM2"], $arrIblock) && intval($arFields["ITEM_ID"]) > 0) {
            $dbElement = CIblockElement::GetByID($arFields["ITEM_ID"]);
            if ($arElement = $dbElement->Fetch()) {
                foreach ($arDelFields as $value) {
                    if (isset($arElement[$value]) && strlen($arElement[$value]) > 0) {
                        $arFields["BODY"] = str_replace(CSearch::KillTags($arElement[$value]), "", CSearch::KillTags($arFields["BODY"]));
                    }
                }
            }
        }
        $arFields["TITLE"] = $arFields["TITLE"] . " " . $arFields["BODY"];
        return $arFields;
    }

}

AddEventHandler("iblock", "OnAfterIBlockElementUpdate", "DoIBlockAfterSave");
AddEventHandler("iblock", "OnAfterIBlockElementAdd", "DoIBlockAfterSave");
AddEventHandler("catalog", "OnPriceAdd", "DoIBlockAfterSave");
AddEventHandler("catalog", "OnPriceUpdate", "DoIBlockAfterSave");

function DoIBlockAfterSave($arg1, $arg2 = false) {
    $ELEMENT_ID = false;
    $IBLOCK_ID = false;
    $OFFERS_IBLOCK_ID = false;
    $OFFERS_PROPERTY_ID = false;
    if (CModule::IncludeModule('currency'))
        $strDefaultCurrency = CCurrency::GetBaseCurrency();
    if (is_array($arg2) && $arg2["PRODUCT_ID"] > 0) {
        $rsPriceElement = CIBlockElement::GetList(
                        array(), array(
                    "ID" => $arg2["PRODUCT_ID"],
                        ), false, false, array("ID", "IBLOCK_ID")
        );
        if ($arPriceElement = $rsPriceElement->Fetch()) {
            $arCatalog = CCatalog::GetByID($arPriceElement["IBLOCK_ID"]);
            if (is_array($arCatalog)) {

                if ($arCatalog["OFFERS"] == "Y") {
                    $rsElement = CIBlockElement::GetProperty(
                                    $arPriceElement["IBLOCK_ID"], $arPriceElement["ID"], "sort", "asc", array("ID" => $arCatalog["SKU_PROPERTY_ID"])
                    );
                    $arElement = $rsElement->Fetch();
                    if ($arElement && $arElement["VALUE"] > 0) {
                        $ELEMENT_ID = $arElement["VALUE"];
                        $IBLOCK_ID = $arCatalog["PRODUCT_IBLOCK_ID"];
                        $OFFERS_IBLOCK_ID = $arCatalog["IBLOCK_ID"];
                        $OFFERS_PROPERTY_ID = $arCatalog["SKU_PROPERTY_ID"];
                    }
                } elseif ($arCatalog["OFFERS_IBLOCK_ID"] > 0) {
                    $ELEMENT_ID = $arPriceElement["ID"];
                    $IBLOCK_ID = $arPriceElement["IBLOCK_ID"];
                    $OFFERS_IBLOCK_ID = $arCatalog["OFFERS_IBLOCK_ID"];
                    $OFFERS_PROPERTY_ID = $arCatalog["OFFERS_PROPERTY_ID"];
                } else {
                    $ELEMENT_ID = $arPriceElement["ID"];
                    $IBLOCK_ID = $arPriceElement["IBLOCK_ID"];
                    $OFFERS_IBLOCK_ID = false;
                    $OFFERS_PROPERTY_ID = false;
                }
            }
        }
    } elseif (is_array($arg1) && $arg1["ID"] > 0 && $arg1["IBLOCK_ID"] > 0) {
        $arOffers = CIBlockPriceTools::GetOffersIBlock($arg1["IBLOCK_ID"]);
        if (is_array($arOffers)) {
            $ELEMENT_ID = $arg1["ID"];
            $IBLOCK_ID = $arg1["IBLOCK_ID"];
            $OFFERS_IBLOCK_ID = $arOffers["OFFERS_IBLOCK_ID"];
            $OFFERS_PROPERTY_ID = $arOffers["OFFERS_PROPERTY_ID"];
        }
    }

    if ($ELEMENT_ID) {
        static $arPropCache = array();
        if (!array_key_exists($IBLOCK_ID, $arPropCache)) {
            $rsProperty = CIBlockProperty::GetByID("MINIMUM_PRICE", $IBLOCK_ID);
            $arProperty = $rsProperty->Fetch();
            if ($arProperty)
                $arPropCache[$IBLOCK_ID] = $arProperty["ID"];
            else
                $arPropCache[$IBLOCK_ID] = false;
        }

        if ($arPropCache[$IBLOCK_ID]) {
            if ($OFFERS_IBLOCK_ID) {
                $rsOffers = CIBlockElement::GetList(
                                array(), array(
                            "ACTIVE" => "Y",
                            "IBLOCK_ID" => $OFFERS_IBLOCK_ID,
                            "PROPERTY_" . $OFFERS_PROPERTY_ID => $ELEMENT_ID,
                                ), false, false, array("ID")
                );
                while ($arOffer = $rsOffers->Fetch())
                    $arProductID[] = $arOffer["ID"];

                if (!is_array($arProductID))
                    $arProductID = array($ELEMENT_ID);
            } else
                $arProductID = array($ELEMENT_ID);

            $minPrice = false;
            $maxPrice = false;

            $rsPrices = CPrice::GetList(
                            array(), array(
                        "PRODUCT_ID" => $arProductID,
                            )
            );
            while ($arPrice = $rsPrices->Fetch()) {
                if (CModule::IncludeModule('currency') && $strDefaultCurrency != $arPrice['CURRENCY'])
                    $arPrice["PRICE"] = CCurrencyRates::ConvertCurrency($arPrice["PRICE"], $arPrice["CURRENCY"], $strDefaultCurrency);

                $PRICE = floatval($arPrice["PRICE"]);

                if (($minPrice === false && $PRICE > 0.0) || ($minPrice > $PRICE && $PRICE > 0.0))
                    $minPrice = $PRICE;

                if ($maxPrice === false || $maxPrice < $PRICE)
                    $maxPrice = $PRICE;
            }
            if ($minPrice !== false) {
                CIBlockElement::SetPropertyValuesEx(
                        $ELEMENT_ID, $IBLOCK_ID, array(
                    "MINIMUM_PRICE" => $minPrice,
                    "MAXIMUM_PRICE" => $maxPrice,
                        )
                );
            }
        }
    }
}

function forBlur($fid) {
    $file = CFile::ResizeImageGet($fid, array('width' => 24, 'height' => 49), BX_RESIZE_IMAGE_PROPORTIONAL);
    return $file['src'];
}

function is_main() {
    global $APPLICATION;
    return $APPLICATION->GetCurPage(false) === '/';
}

function minP($a, $b) {
    if ($a["MIN_PRICE"]["DISCOUNT_VALUE"] < $a["MIN_PRICE"]["VALUE"]) {
        $as = true;
    }
    if ($b["MIN_PRICE"]["DISCOUNT_VALUE"] < $b["MIN_PRICE"]["VALUE"]) {
        $bs = true;
    }
    if ($as !== $bs) {
        return $as === true ? -1 : 1;
    }
    if ($a["MIN_PRICE"]["DISCOUNT_VALUE"] == $b["MIN_PRICE"]["DISCOUNT_VALUE"]) {
        if ($a["SORT"] != $b["SORT"]) {
            if ($a["SORT"] < $b["SORT"]) {
                return -1;
            } elseif ($a["SORT"] > $b["SORT"]) {
                return 1;
            }
        } else {
            return ($a["ID"] < $b["ID"]) ? -1 : 1;
        }
    }
    return ($a["MIN_PRICE"]["DISCOUNT_VALUE"] < $b["MIN_PRICE"]["DISCOUNT_VALUE"]) ? -1 : 1;
}

/*
  function minP($a, $b) {
  if ($a["MIN_PRICE"]["DISCOUNT_VALUE"] == $b["MIN_PRICE"]["DISCOUNT_VALUE"]) {
  return 0;
  }
  return ($a["MIN_PRICE"]["DISCOUNT_VALUE"] < $b["MIN_PRICE"]["DISCOUNT_VALUE"]) ? -1 : 1;
  } */

function minSort($a, $b) {
    return ($a["SORT"] < $b["SORT"]) ? -1 : 1;
}

function getPriceWithDiscount($ID) {

    if (CModule::IncludeModule('sale')) {
        global $USER;
        $dbPrice = CPrice::GetList(
                        [], ["PRODUCT_ID" => $ID, "CATALOG_GROUP_ID" => 1], false, false, ["ID", "CATALOG_GROUP_ID", "PRODUCT_ID", "PRICE", "CURRENCY", "QUANTITY_FROM", "QUANTITY_TO"]
        );

        if ($arPrice = $dbPrice->fetch()) {

            $arDiscounts = CCatalogDiscount::GetDiscountByPrice(
                            $arPrice["ID"], $USER->GetUserGroupArray(), "N", SITE_ID
            );
            $discountPrice = CCatalogProduct::CountPriceWithDiscount(
                            $arPrice["PRICE"], $arPrice["CURRENCY"], $arDiscounts
            );
            $arPrice["DISCOUNT_PRICE"] = $discountPrice;
            return $arPrice;
        }
    }
}

function getOffersWithMinPrice($PRODUCT_ID, $all = false, $sort = false) {
    CModule::IncludeModule('iblock');
    $arSelect = ["ID", "IBLOCK_ID", "NAME", "SORT", "DETAIL_PICTURE"];
    $arFilter = ["PROPERTY_CML2_LINK" => $PRODUCT_ID, "ACTIVE" => "Y", "!IBLOCK_ID" => 26];
    if ($sort) {
        $arSort = ["SORT" => "asc"];
    } else {
        $arSort = [];
    }
    $res = CIBlockElement::GetList($arSort, $arFilter, false, false, $arSelect);
    while ($ob = $res->GetNextElement()) {
        $arFields = $ob->GetFields();
        $arProps = $ob->GetProperties();
        $offer["ID"] = $arFields["ID"];
        $offer["NAME"] = $arFields["NAME"];
        if ($arFields["DETAIL_PICTURE"]) {
            $offer["DETAIL_PICTURE_ID"] = $arFields["DETAIL_PICTURE"];
            $offer["DETAIL_PICTURE"] = CFile::GetPath($arFields["DETAIL_PICTURE"]);
        } else {
            $offer["DETAIL_PICTURE"] = EMPTY_PHOTO_SRC;
        }
        if ($arProps["DECOR_PHOTO_IN"]["VALUE"]) {
            $offer["DETAIL_PICTURE2_ID"] = $arProps["DECOR_PHOTO_IN"]["VALUE"];
            $offer["DETAIL_PICTURE_2"] = CFile::GetPath($arProps["DECOR_PHOTO_IN"]["VALUE"]);
        } else {
            $offer["DETAIL_PICTURE_2"] = EMPTY_PHOTO_SRC;
        }
        $MIN_PRICE = getPriceWithDiscount($offer["ID"]);
        $offer["MIN_PRICE"]["VALUE"] = round($MIN_PRICE["PRICE"]);
        $offer["MIN_PRICE"]["DISCOUNT_VALUE"] = round($MIN_PRICE["DISCOUNT_PRICE"]);
        $offers[] = $offer;
    }
    if (!$sort) {
        usort($offers, "minP");
    }
    if (!$all) {
        return $offers[0];
    } else {
        return $offers;
    }
}

function getProperty($iblock_id, $id, $code) {
    $db_props = CIBlockElement::GetProperty($iblock_id, $id, array("sort" => "asc"), Array("CODE" => $code));
    if ($ar_props = $db_props->Fetch()) {
        if ($ar_props["PROPERTY_TYPE"] == "L") {
            return $ar_props["VALUE_ENUM"];
        } else {
            return $ar_props["VALUE"];
        }
    }
    return false;
}

function GetDelivery() {
    $db_dtype = CSaleDelivery::GetList(
                    array(
                "SORT" => "ASC",
                "NAME" => "ASC"
                    ), array(
                "LID" => SITE_ID,
                "ACTIVE" => "Y"
                    ), false, false, array()
    );
    if ($ar_dtype = $db_dtype->Fetch()) {
        do {
            $Deliveries[] = $ar_dtype;
        } while ($ar_dtype = $db_dtype->Fetch());
    }
    return $Deliveries;
}

function GetMinPrice($a) {

    if ($a["OFFERS"]) {
        usort($a["OFFERS"], "minP");
        $price = $a["OFFERS"][0]["MIN_PRICE"]["DISCOUNT_VALUE"];
    } else {
        $price = $a["MIN_PRICE"]["DISCOUNT_VALUE"];
    }
    return $price;
}

class CIBlockPropertyElementListPlus {

    function GetUserTypeDescription() {
        return array(
            "PROPERTY_TYPE" => "E",
            "USER_TYPE" => "EListPlus",
            "DESCRIPTION" => "Привязка к элементам с указанием количества",
            "GetPropertyFieldHtml" => array("CIBlockPropertyElementListPlus", "GetPropertyFieldHtml"),
        );
    }

    function GetPropertyFieldHtml($arProperty, $value, $strHTMLControlName) {
        return (CIBlockPropertyXmlID::GetPropertyFieldHtml($arProperty, $value, $strHTMLControlName)) .
                "<input name='" . $strHTMLControlName["DESCRIPTION"] . "' value='" . $value['DESCRIPTION'] . "'/>";
    }

}

/* кастомное поле в инфоблоке: выбор элемента + поле описания */
AddEventHandler("iblock", "OnIBlockPropertyBuildList", array("CIBlockPropertyElementListPlus", "GetUserTypeDescription"));

function GetIdDiscount($flag) {
    CModule::IncludeModule("catalog");
    CModule::IncludeModule("iblock");
    global $DB;
    $arDiscountElementID = array();
    $dbProductDiscounts = CCatalogDiscount::GetList(
                    array("SORT" => "ASC"), array(
                "ACTIVE" => "Y",
                "!>ACTIVE_FROM" => $DB->FormatDate(date("Y-m-d H:i:s"), "YYYY-MM-DD HH:MI:SS", CSite::GetDateFormat("FULL")),
                "!<ACTIVE_TO" => $DB->FormatDate(date("Y-m-d H:i:s"), "YYYY-MM-DD HH:MI:SS", CSite::GetDateFormat("FULL")),
                    ), false, false, array(
                "*"
                    )
    );
    while ($arProductDiscounts = $dbProductDiscounts->Fetch()) {
        $ids[] = $arProductDiscounts["PRODUCT_ID"];
        $sections[] = $arProductDiscounts["SECTION_ID"];
    }
    $sections = array_unique($sections);
    if (!empty($sections)) {
        $arSelect = Array("ID", "NAME");
        $arFilter = Array("SECTION_ID" => $sections, "ACTIVE_DATE" => "Y", "ACTIVE" => "Y", "INCLUDE_SUBSECTIONS" => "Y");
        $res = CIBlockElement::GetList(Array(), $arFilter, false, false, $arSelect);
        while ($ob = $res->GetNextElement()) {
            $arFields = $ob->GetFields();
            $ids[] = $arFields["ID"];
        }
    }
    $ids = array_unique($ids);
    if ($flag == true) {
        unset($result);
        if (!empty($ids)) {
            $result = array_merge($ids, productDiscount(), productOfferDiscount());
        } else {
            $result = array_merge(productDiscount(), productOfferDiscount());
        }
        $result = array_unique($result);
    } else {
        $ress = CCatalogSKU::getOffersList(
                        $ids, false, false, false, false);

        foreach ($ress as $key => $resss) {
            foreach ($resss as $r) {
                $ids_off[] = $r["ID"];
            }
        }
        $result = array_merge($ids, $ids_off);
    }

    return $result;
}
