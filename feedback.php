<?php
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_before.php");
if(!empty($_POST["name"]) && !empty($_POST["email"]))
{
	
	$name = filter_input(INPUT_POST, 'name');
	$email = filter_input(INPUT_POST,'email');
	$mess = filter_input(INPUT_POST,'mess');

	if(!empty($_FILES["file"])) {
		$newfile = time().$_FILES["file"]["name"];
		if(copy($_FILES["file"]["tmp_name"], $_SERVER["DOCUMENT_ROOT"]."/upload/mail/".$newfile)) {
			$file = "//".$_SERVER["HTTP_HOST"]."/upload/mail/".$newfile;
		}
	}

	$arEventFields = array(
		"NAME"=>$name,
		"EMAIL"=>$email,
		"MESS" => $mess,
		"FILE" =>$file
	);

	CEvent::Send("KONTAKTY", "s1", $arEventFields);
	LocalRedirect("/thank/");
} else {
	LocalRedirect("/");
}