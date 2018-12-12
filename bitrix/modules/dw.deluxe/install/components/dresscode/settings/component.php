<?
	if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true)
		die();

    //load modules
	if( !\Bitrix\Main\Loader::includeModule("sale") ||
		!\Bitrix\Main\Loader::includeModule("catalog") ||
		!\Bitrix\Main\Loader::includeModule("iblock") ||
		!\Bitrix\Main\Loader::includeModule("dw.deluxe") ||
		!\Bitrix\Main\Loader::includeModule("highloadblock")){

		showError("dresscode:settings - check modules");
		return false;

	}

	//global vars
	global $USER;

	//check admin
	if(!$USER->IsAdmin()){
		return false;
	}

	//vars
	$arResult = array();

	//create object
	$dwSettings = DwSettings::getInstance();

	//get actual settings
	$arResult["CURRENT_SETTINGS"] = $dwSettings->getCurrentSettings();

	//scan template settings
	$arResult["TEMPLATES"]["SETTINGS"] = $dwSettings->scanTemplate($_SERVER["DOCUMENT_ROOT"].SITE_TEMPLATE_PATH);

	//scan headers
	$arResult["TEMPLATES"]["HEADERS"] = $dwSettings->scanHeaders($_SERVER["DOCUMENT_ROOT"].SITE_TEMPLATE_PATH."/headers/");

	//scan themes
	$arResult["TEMPLATES"]["THEMES"] = $dwSettings->scanThemes($_SERVER["DOCUMENT_ROOT"].SITE_TEMPLATE_PATH."/themes/");

	//get background variants (if exist)
	if(!empty($arResult["TEMPLATES"]["THEMES"])){
		$arResult["TEMPLATES"]["BACKGROUND_VARIANTS"] = $dwSettings->getBgVariantsByData($arResult["TEMPLATES"]["THEMES"]);
	}

	//add variants array
	if(empty($arResult["TEMPLATES"]["BACKGROUND_VARIANTS"])){
		$arResult["TEMPLATES"]["THEMES"] = array("VARIANTS" => $arResult["TEMPLATES"]["THEMES"]);
	}

	//get iblocks & properties
	$arResult["IBLOCKS"] = $dwSettings->getIblocksWithProperty();

	//get price codes
	$arResult["PRICE_CODES"] = $dwSettings->getPriceCodes();

	//product iblocks
	if(!empty($arResult["IBLOCKS"]["PRODUCT_IBLOCKS"])){
		$arResult["PRODUCT_IBLOCKS"] = $arResult["IBLOCKS"]["PRODUCT_IBLOCKS"];
	}

	//sku iblocks
	if(!empty($arResult["IBLOCKS"]["SKU_IBLOCKS"])){
		$arResult["SKU_IBLOCKS"] = $arResult["IBLOCKS"]["SKU_IBLOCKS"];
	}

	//clear cache keys
	$this->setResultCacheKeys(array());

	//include template
	$this->IncludeComponentTemplate();
	
?>