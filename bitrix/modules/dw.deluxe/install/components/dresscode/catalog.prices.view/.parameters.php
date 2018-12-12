<?
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true) die();
if(CModule::IncludeModule("iblock") && CModule::IncludeModule("sale") && CModule::IncludeModule("catalog")){
	$arPrice = CCatalogIBlockParameters::getPriceTypesList();
	$arComponentParameters = array(
		"PARAMETERS" => array(
			"CACHE_TIME" => Array("DEFAULT" => "1285912"),
			"PRODUCT_ID" => Array(
				"DEFAULT" => "",
				"NAME" => GetMessage("PRODUCT_ID_LABEL"),
				"PRODUCT_PRICE_CODE" => array(
					"PARENT" => "BASE",
					"NAME" => GetMessage("IBLOCK_PRICE_CODE"),
					"TYPE" => "LIST",
					"MULTIPLE" => "Y",
					"VALUES" => $arPrice,
				)
			)
		)
	);
}
?>