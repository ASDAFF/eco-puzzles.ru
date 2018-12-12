<?
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true) die();

//d7 namespace
use Bitrix\Main\Loader;
use Bitrix\Main\Web\Json;
use Bitrix\Iblock;
use Bitrix\Currency;

if(CModule::IncludeModule("iblock") && CModule::IncludeModule("sale") && CModule::IncludeModule("catalog")){

	$arPrice = CCatalogIBlockParameters::getPriceTypesList();
	$arComponentParameters = array(
		"GROUPS" => array(
			"BASKET_PICTURE" => array(
				"NAME" => GetMessage("BASKET_PICTURE"),
				"SORT" => "200"
			),
			"GIFT" => array(
				"NAME" => GetMessage("GIFT_PARAMS"),
				"SORT" => "200"
			),
		),
		"PARAMETERS" => array(
			"BASKET_PICTURE_WIDTH" => array(
		         "PARENT" => "BASKET_PICTURE",
		         "NAME" => GetMessage("BASKET_PICTURE_WIDTH"),
		         "TYPE" => "STRING"
			),
			"BASKET_PICTURE_HEIGHT" => array(
		         "PARENT" => "BASKET_PICTURE",
		         "NAME" => GetMessage("BASKET_PICTURE_HEIGHT"),
		         "TYPE" => "STRING"
			),
			"HIDE_MEASURES" => array(
				"PARENT" => "BASE",
				"NAME" => GetMessage("HIDE_MEASURES"),
				"TYPE" => "CHECKBOX",
				"REFRESH" => "Y"
			),
			"HIDE_NOT_AVAILABLE" => array(
				"PARENT" => "GIFT",
				"NAME" => GetMessage("HIDE_NOT_AVAILABLE"),
				"TYPE" => "CHECKBOX",
				"REFRESH" => "Y"
			),
			"PRODUCT_PRICE_CODE" => array(
				"PARENT" => "GIFT",
				"NAME" => GetMessage("IBLOCK_PRICE_CODE_GIFT"),
				"TYPE" => "LIST",
				"MULTIPLE" => "Y",
				"VALUES" => $arPrice,
			),
			"CACHE_TIME" => Array("DEFAULT" => "36000000"),
		)
	);

	$arComponentParameters["PARAMETERS"]["GIFT_CONVERT_CURRENCY"] = array(
		"PARENT" => "GIFT",
		"NAME" => GetMessage("GIFT_CONVERT_CURRENCY"),
		"TYPE" => "CHECKBOX",
		"DEFAULT" => "N",
		"REFRESH" => "Y",
	);

	if (isset($arCurrentValues["GIFT_CONVERT_CURRENCY"]) && $arCurrentValues["GIFT_CONVERT_CURRENCY"] === "Y"){
		$arComponentParameters["PARAMETERS"]["GIFT_CURRENCY_ID"] = array(
			"PARENT" => "GIFT",
			"NAME" => GetMessage("GIFT_CURRENCY_ID"),
			"TYPE" => "LIST",
			"VALUES" => Currency\CurrencyManager::getCurrencyList(),
			"DEFAULT" => Currency\CurrencyManager::getBaseCurrency(),
			"ADDITIONAL_VALUES" => "Y",
		);
	}

}
?>
