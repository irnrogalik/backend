<?
require_once($_SERVER['DOCUMENT_ROOT'] . "/bitrix/modules/main/include/prolog_before.php");
$post = $_POST;

$id = $post["id"];

if (!empty($post) && CModule::IncludeModule("iblock")) {

    $like_post = $post["value"][0];
    $dislike_post = $post["value"][1];
    $res = CIBlockElement::GetList(Array(), Array("IBLOCK_ID" => 17, "ID" => $id), false, false, array("ID", "IBLOCK_ID", "PROPERTY_LIKES", "PROPERTY_DISLIKES"));
    while ($ob = $res->GetNextElement()) {
        $arFields = $ob->GetFields();

        $like = $arFields["PROPERTY_LIKES_VALUE"];
        $dislike = $arFields["PROPERTY_DISLIKES_VALUE"];
    }
    $iblock_id = 17;
    echo "LIKE  " . ($like + $like_post) . "  DISLIKE   " . ($dislike + $dislike_post);

    CIBlockElement::SetPropertyValues($id, $iblock_id, ($like + $like_post), "LIKES");

    CIBlockElement::SetPropertyValues($id, $iblock_id, ($dislike + $dislike_post), "DISLIKES");
}
