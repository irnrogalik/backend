<?php
require_once($_SERVER['DOCUMENT_ROOT'] . "/bitrix/modules/main/include/prolog_before.php");

$post = $_POST;

if ($_POST["my_address"] || $_POST["login"]) {
    LocalRedirect("/");
} elseif (!empty($post)) {
    $name = htmlspecialcharsex($post['name']);
    $product_name = htmlspecialcharsex($post['good']);
    $mess = htmlspecialcharsex($post['message']);
    $photo_way = htmlspecialcharsex($post['reviews_file_way']);
    $page = htmlspecialcharsex($post['page']);
    $product_id = htmlspecialcharsex($post['product_id']);

    if (CModule::IncludeModule("iblock")) {
        if ($product_id) {
            $arSelect = Array("ID", "NAME", "IBLOCK_ID");
            $res = CIBlockElement::GetList([], ["IBLOCK_ID" => 2, "ID" => $product_id], false, false, $arSelect);
            while ($ob = $res->GetNextElement()) {
                $arFields = $ob->GetFields();
                $name_product = $arFields["NAME"];              
            }
        }

        $el = new CIBlockElement;
        $PROP = array();

        $title = "Отзыв " . $product_name . ($name_product ? " (" . $name_product . "(" . $product_id . ")" . ")" : "");
        $PROP[280] = $name;
        $PROP[281] = $product_name;
        $PROP[282] = CFile::MakeFileArray($photo_way);
        $PROP[283] = $mess;
        $send_name = "LEGAL_FORM";
		
        $arLoadProductArray = Array(
            "MODIFIED_BY" => $USER->GetID(),
            "IBLOCK_SECTION_ID" => false,
            "IBLOCK_ID" => 18,
            "PROPERTY_VALUES" => $PROP,
            "NAME" => $title,
            "ACTIVE" => "N",
            "ACTIVE_FROM" => ConvertTimeStamp(time(), "FULL")
        );
        if ($PRODUCT_ID = $el->Add($arLoadProductArray)) {
            $arEventFields = array(
                "NAME" => $name,
                "PRODUCT_NAME" => $product_name,
                "MESSAGE" => $mess,
                "TITLE" => $title,
                "IMG" => $photo_way
            );
            CEvent::Send("REVIEWS", "s1", $arEventFields);
        }
    }
}