<?require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog.php");?>
<?
die();
set_time_limit(0);
CModule::IncludeModule('iblock');
function GetListFiles($folder,&$all_files){
    $fp=opendir($folder);
    while($cv_file=readdir($fp)) {
        if(is_file($folder."/".$cv_file)) {
            $all_files[]=$folder."/".$cv_file;
        }elseif($cv_file!="." && $cv_file!=".." && is_dir($folder."/".$cv_file)){
            GetListFiles($folder."/".$cv_file,$all_files);
        }
    }
    closedir($fp);
}
$all_files=array();
GetListFiles($_SERVER['DOCUMENT_ROOT']."/upload/iblock",$all_files);
foreach ($all_files as $k => $img) {
if($k < $_GET['k'] && $_GET['k']) continue;
   list($width, $height, $type, $attr) = getimagesize($img);
   if ($height>410) {
	$wtm = "water";
   }
   else
   {
	$wtm = "water_min";
   }
$wtm = "water";
      echo $img."<br>";
       $rif = CFile::ResizeImageFile( // уменьшение картинки для превью
       $sourceFile = $img,
       $destinationFile =  $_SERVER['DOCUMENT_ROOT']."/upload/1.png",
       $arSize = array('width'=>$width,'height'=>$height),
       $resizeType = BX_RESIZE_IMAGE_PROPORTIONAL,
       $arWaterMark = array(),
       $jpgQuality=false,
       $arFilters = Array( // нанесение водяного знака
          array("name" => "watermark", "position" => "center", "size"=>"real", "file"=>$_SERVER['DOCUMENT_ROOT']."/upload/".$wtm.".png")
      )
    );
      if ($rif) {
		  unlink($img);
		  rename($_SERVER['DOCUMENT_ROOT']."/upload/1.png", $img);
		}
	
   $next = $k+1;
   break;
}
if(count($all_files) <= $k+1)
die();

 echo '<META HTTP-EQUIV="REFRESH" CONTENT="0;URL=http://ds-steelline.by/request/water.php?k='.$next.'">';

?>