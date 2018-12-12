<?
//replace index.php to /
if(!empty($arResult)){
	foreach ($arResult as $key => $arItem){
	   $arResult[$key]["LINK"] = str_replace("index.php", "", $arItem["LINK"]);
	}
}
?>