<?php

require_once($_SERVER['DOCUMENT_ROOT'] . "/bitrix/modules/main/include/prolog_before.php");

$ELEMENT_ID = false;
$IBLOCK_ID = false;
$OFFERS_IBLOCK_ID = false;
$OFFERS_PROPERTY_ID = false;
CModule::IncludeModule('iblock');
CModule::IncludeModule('currency');

updateMinPrice(2);
updateMinPrice(5);
updateMinPrice(6);
updateMinPrice(10);
updateMinPrice(11);

function updateMinPrice($INPUT_IBLOCK_ID) {

    $strDefaultCurrency = CCurrency::GetBaseCurrency();
    $rsPriceElement = CIBlockElement::GetList(
                    array(), array("IBLOCK_ID" => $INPUT_IBLOCK_ID), false, false, array("ID", "IBLOCK_ID")
    );
    while ($ob = $rsPriceElement->GetNextElement()) {
        $arPriceElement = $ob->GetFields();
        $arCatalog = CCatalog::GetByID($arPriceElement["IBLOCK_ID"]);
        if (is_array($arCatalog)) {
            if ($arCatalog["OFFERS"] == "Y") {
                $rsElement = CIBlockElement::GetProperty(
                                $arPriceElement["IBLOCK_ID"], $arPriceElement["ID"], "sort", "asc", array("ID" => $arCatalog["SKU_PROPERTY_ID"])
                );
                $arElement = $rsElement->Fetch();

                if ($arElement && $arElement["VALUE"] > 0) {
                    $ids[] = $arElement["VALUE"];
                    $IBLOCK_ID = $IBLOCK_ID ? $IBLOCK_ID : $arCatalog["PRODUCT_IBLOCK_ID"];
                    $OFFERS_IBLOCK_ID = $OFFERS_IBLOCK_ID ? $OFFERS_IBLOCK_ID : $arCatalog["IBLOCK_ID"];
                    $OFFERS_PROPERTY_ID = $OFFERS_PROPERTY_ID ? $OFFERS_PROPERTY_ID : $arCatalog["SKU_PROPERTY_ID"];
                }
            } elseif ($arCatalog["OFFERS_IBLOCK_ID"] > 0) {
                $ids[] = $arPriceElement["ID"];
                $IBLOCK_ID = $IBLOCK_ID ? $IBLOCK_ID : $arPriceElement["IBLOCK_ID"];
                $OFFERS_IBLOCK_ID = $OFFERS_IBLOCK_ID ? $OFFERS_IBLOCK_ID : $arCatalog["OFFERS_IBLOCK_ID"];
                $OFFERS_PROPERTY_ID = $OFFERS_PROPERTY_ID ? $OFFERS_PROPERTY_ID : $arCatalog["OFFERS_PROPERTY_ID"];
            } else {
                $ids[] = $arPriceElement["ID"];
                $IBLOCK_ID = $IBLOCK_ID ? $IBLOCK_ID : $arPriceElement["IBLOCK_ID"];
                $OFFERS_IBLOCK_ID = false;
                $OFFERS_PROPERTY_ID = false;
            }
        }
    }

    if (!empty($ids)) {
        static $arPropCache = array();
        if (!array_key_exists($IBLOCK_ID, $arPropCache)) {
            $rsProperty = CIBlockProperty::GetByID("MINIMUM_PRICE", $IBLOCK_ID);
            $arProperty = $rsProperty->Fetch();
            if ($arProperty)
                $arPropCache[$IBLOCK_ID] = $arProperty["ID"];
            else
                $arPropCache[$IBLOCK_ID] = false;
        }
        foreach ($ids as $ELEMENT_ID) {
            if ($arPropCache[$IBLOCK_ID]) {
                if ($OFFERS_IBLOCK_ID) {
                    $rsOffers = CIBlockElement::GetList(
                                    array(), array(
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
                $rsPrices = CPrice::GetList(
                                array(), array(
                            "PRODUCT_ID" => $arProductID,
                                )
                );
                while ($arPrice = $rsPrices->Fetch()) {
                    if (CModule::IncludeModule('currency') && $strDefaultCurrency != $arPrice['CURRENCY'])
                        $arPrice["PRICE"] = CCurrencyRates::ConvertCurrency($arPrice["PRICE"], $arPrice["CURRENCY"], $strDefaultCurrency);
                    $PRICE = $arPrice["PRICE"];
                    if ($minPrice === false || $minPrice > $PRICE)
                        $minPrice = $PRICE;
                }


                if ($minPrice !== false) {
                    CIBlockElement::SetPropertyValuesEx(
                            $ELEMENT_ID, $IBLOCK_ID, array(
                        "MINIMUM_PRICE" => $minPrice
                            )
                    );
                    unset($minPrice, $arPrice, $PRICE, $arProductID);
                }
            }
        }
    }
    echo "UPDATE IBLOCK " . $INPUT_IBLOCK_ID . "<br>";
}
