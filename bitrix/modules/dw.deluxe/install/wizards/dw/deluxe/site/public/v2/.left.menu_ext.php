<? 
if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();


//include module
\Bitrix\Main\Loader::includeModule("dw.deluxe");

//vars
$catalogIblockId = null;
$arPriceCodes = array();

//get template settings
$arTemplateSettings = DwSettings::getInstance()->getCurrentSettings();
if(!empty($arTemplateSettings)){
	$catalogIblockId = $arTemplateSettings["TEMPLATE_PRODUCT_IBLOCK_ID"];
	$arPriceCodes = explode(", ", $arTemplateSettings["TEMPLATE_PRICE_CODES"]);
}

//globals
global $APPLICATION;
$aMenuLinksExt = $APPLICATION->IncludeComponent(
	"dresscode:menu.sections", 
	"", 
	array(
		"IBLOCK_TYPE" => "#CATALOG_IBLOCK_TYPE#",
		"IBLOCK_ID" => $catalogIblockId,
		"DEPTH_LEVEL" => "3",
		"CACHE_TYPE" => "A",
		"CACHE_TIME" => "3600000",
		"IS_SEF" => "N",
		"ID" => $_REQUEST["ID"],
		"SECTION_URL" => ""
	),
	false
); 

//append menu items
$aMenuLinks = array_merge($aMenuLinks, $aMenuLinksExt); 
?>