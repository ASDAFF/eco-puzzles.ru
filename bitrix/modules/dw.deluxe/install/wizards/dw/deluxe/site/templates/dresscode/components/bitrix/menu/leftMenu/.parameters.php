<?

//d7 namespace
use Bitrix\Main\Loader;
use Bitrix\Main\Web\Json;
use Bitrix\Iblock;
use Bitrix\Currency;

if(CModule::IncludeModule("iblock") && CModule::IncludeModule("sale") && CModule::IncludeModule("catalog")){
	$arPrice = CCatalogIBlockParameters::getPriceTypesList();
	$arTemplateParameters["HIDE_MEASURES"] = array(
		"PARENT" => "BASE",
		"NAME" => GetMessage("HIDE_MEASURES"),
		"TYPE" => "CHECKBOX",
		"REFRESH" => "Y"
	);
	$arTemplateParameters["PRODUCT_PRICE_CODE"] = array(
		"PARENT" => "BASE",
		"NAME" => GetMessage("IBLOCK_PRICE_CODE"),
		"TYPE" => "LIST",
		"MULTIPLE" => "Y",
		"VALUES" => $arPrice,
	);

	$arTemplateParameters["CONVERT_CURRENCY"] = array(
		"PARENT" => "BASE",
		"NAME" => GetMessage("CONVERT_CURRENCY"),
		"TYPE" => "CHECKBOX",
		"DEFAULT" => "N",
		"REFRESH" => "Y",
	);

	if (isset($arCurrentValues["CONVERT_CURRENCY"]) && $arCurrentValues["CONVERT_CURRENCY"] === "Y"){
		$arTemplateParameters["CURRENCY_ID"] = array(
			"PARENT" => "BASE",
			"NAME" => GetMessage("CURRENCY_ID"),
			"TYPE" => "LIST",
			"VALUES" => Currency\CurrencyManager::getCurrencyList(),
			"DEFAULT" => Currency\CurrencyManager::getBaseCurrency(),
			"ADDITIONAL_VALUES" => "Y",
		);
	}

}
?>