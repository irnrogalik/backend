<?
require_once($_SERVER['DOCUMENT_ROOT'] . "/bitrix/modules/main/include/prolog_before.php");
$filess = $_FILES;
$post = $_POST;


if (!empty($filess)) {
    if (!empty($filess["bill_file"]["name"] || !empty($filess["reviews_file"]["name"]))) {
        if ($filess["bill_file"]["name"]) {
            $path_name = 'requisite';
            $file_name = "bill_file";
        } elseif ($filess["reviews_file"]["name"]) {
            $path_name = 'reviews';
            $file_name = "reviews_file";
        }

        $name = date("Y-m-d H:i:s") . "-" . $filess[$file_name]["name"];     
        $load_p = $filess[$file_name]["tmp_name"];
        $path = '/upload/forms/' . $path_name . '/' . $name;      
        $upload_p = $_SERVER['DOCUMENT_ROOT'] . $path;
        if (copy($load_p, $upload_p)) {
            echo $path;
        } else {
            echo "не удалось";
        }
    } else {
        $files = $filess[0];
        if ($files["tmp_name"]) {
            $name = date("Y-m-d H:i:s") . "-" . $files["name"];
            $load_p = $files["tmp_name"];
            $path = '/upload/forms/photos/' . $name;
            $upload_p = $_SERVER['DOCUMENT_ROOT'] . $path;

            if (copy($load_p, $upload_p)) {
                echo $path;
            } else {
                echo "не удалось";
            }
        }
    }
}

