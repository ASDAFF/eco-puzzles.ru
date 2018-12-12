<?
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true) die();

//d7 namespace
use Bitrix\Main\Loader;
use Bitrix\Main\Web\Json;
use Bitrix\Iblock;
use Bitrix\Currency;

if(CModule::IncludeModule("iblock") && CModule::IncludeModule("sale") && CModule::IncludeModule("catalog")){

	$IBLOCKS     = array();
	$IBLOCK_TYPE = array();

	$arDisplayProperties = array();

	$res = CIBlockType::GetList();
	while($arRes = $res->Fetch()){
		$IBLOCK_TYPE[$arRes["ID"]] = $arRes["ID"];
	}

	$res = CIBlock::GetList(
	    Array(),
	    Array('TYPE' => $arCurrentValues["IBLOCK_TYPE"])
	);

	while($arRes = $res->Fetch()){
		$IBLOCKS[$arRes["ID"]] = $arRes["NAME"];
	}

	$arPrice = CCatalogIBlockParameters::getPriceTypesList();

	if(!empty($arCurrentValues["IBLOCK_ID"])){
		$rsProperties = CIBlockProperty::GetList(Array("SORT" => "ASC", "NAME" => "ASC"), Array("ACTIVE" => "Y", "IBLOCK_ID" => $arCurrentValues["IBLOCK_ID"]));
		while ($arNextProperty = $rsProperties->GetNext()){
			$arDisplayProperties[$arNextProperty["CODE"]] = $arNextProperty["NAME"];
		}
	}


	$arComponentParameters = array(
		"GROUPS" => array(
			"PICTURE" => array(
				"NAME" => GetMessage("PICTURE"),
				"SORT" => "200"
			),
			"SEO" => array(
				"NAME" => GetMessage("SEO_PARAMS"),
				"SORT" => "200"
			),
			"PRICES" => array(
				"NAME" => GetMessage("PRICES_PARAMS"),
				"SORT" => "200"
			),
			"SECTION" => array(
				"NAME" => GetMessage("SECTION_PARAMS"),
				"SORT" => "200"
			),
			"CHAIN" => array(
				"NAME" => GetMessage("CHAIN_PARAMS"),
				"SORT" => "200"
			),
			"EXTRA" => array(
				"NAME" => GetMessage("EXTRA_PARAMS"),
				"SORT" => "200"
			),		
			"SORT" => array(
				"NAME" => GetMessage("SORT"),
				"SORT" => "190"
			),
		),
		"PARAMETERS" => array(
			"IBLOCK_TYPE" => array(
		         "PARENT" => "BASE",
		         "NAME" => GetMessage("IBLOCK_TYPE"),
		         "TYPE" => "LIST",
		          "VALUES" => $IBLOCK_TYPE,
		          "REFRESH" => "Y"
			),
			"IBLOCK_ID" => array(
				"PARENT" => "BASE",
				"NAME" => GetMessage("IBLOCK"),
				"TYPE" => "LIST",
				"VALUES" => $IBLOCKS,
				"REFRESH" => "Y"
			),
	
			"PRODUCT_ID" => array(
		         "PARENT" => "BASE",
		         "NAME" => GetMessage("PRODUCT_ID"),
		         "TYPE" => "STRING",
		         "DEFAULT" => "140"
			),
			"PRODUCT_DISPLAY_PROPERTIES" => array(
				"PARENT" => "BASE",
				"NAME" => GetMessage("PRODUCT_DISPLAY_PROPERTIES"),
				"TYPE" => "LIST",
				"MULTIPLE" => "Y",
				"VALUES" => $arDisplayProperties,
			),
			"PRODUCT_PRICE_CODE" => array(
				"PARENT" => "PRICES",
				"NAME" => GetMessage("IBLOCK_PRICE_CODE"),
				"TYPE" => "LIST",
				"MULTIPLE" => "Y",
				"VALUES" => $arPrice,
			),
			"HIDE_NOT_AVAILABLE" => array(
				"PARENT" => "BASE",
				"NAME" => GetMessage("HIDE_NOT_AVAILABLE"),
				"TYPE" => "CHECKBOX",
				"REFRESH" => "Y"
			),
			"HIDE_MEASURES" => array(
				"PARENT" => "BASE",
				"NAME" => GetMessage("HIDE_MEASURES"),
				"TYPE" => "CHECKBOX",
				"REFRESH" => "Y"
			),
			"GET_MORE_PICTURES" => array(
		         "PARENT" => "PICTURE",
		         "NAME" => GetMessage("GET_MORE_PICTURES"),
		         "TYPE" => "CHECKBOX",
		         "DEFAULT" => "200"
			),
			"PICTURE_WIDTH" => array(
		         "PARENT" => "PICTURE",
		         "NAME" => GetMessage("PICTURE_WIDTH"),
		         "TYPE" => "STRING",
		         "DEFAULT" => "200"
			),
			"PICTURE_HEIGHT" => array(
		         "PARENT" => "PICTURE",
		         "NAME" => GetMessage("PICTURE_HEIGHT"),
		         "TYPE" => "STRING",
		         "DEFAULT" => "140"
			),
			"SECTION_URL" => CIBlockParameters::GetPathTemplateParam(
				"SECTION",
				"SECTION_URL",
				GetMessage("SECTION_URL"),
				"",
				"URL_TEMPLATES"
			),
			"SECTION_CODE" => array(
		         "PARENT" => "SECTION",
		         "NAME" => GetMessage("SECTION_CODE"),
		         "TYPE" => "STRING",
		         "DEFAULT" => "140"
			),
			"SECTION_ID" => array(
		         "PARENT" => "SECTION",
		         "NAME" => GetMessage("SECTION_ID"),
		         "TYPE" => "STRING",
		         "DEFAULT" => "140"
			),
			"ADD_SECTIONS_CHAIN" => array(
		         "PARENT" => "CHAIN",
		         "NAME" => GetMessage("ADD_SECTIONS_CHAIN"),
		         "TYPE" => "CHECKBOX",
		         "DEFAULT" => "N"
			),
			"ADD_ELEMENT_CHAIN" => array(
		         "PARENT" => "CHAIN",
		         "NAME" => GetMessage("ADD_ELEMENT_CHAIN"),
		         "TYPE" => "CHECKBOX",
		         "DEFAULT" => "N"
			),
			"SET_TITLE" => array(
				"PARENT" => "SEO",
				"NAME" => GetMessage("SET_TITLE"),
				"TYPE" => "CHECKBOX",
				"DEFAULT" => "N",
				"REFRESH" => "Y"
			),
			"SET_BROWSER_TITLE" => array(
				"PARENT" => "SEO",
				"NAME" => GetMessage("SET_BROWSER_TITLE"),
				"TYPE" => "CHECKBOX",
				"DEFAULT" => "N",
				"REFRESH" => "Y"
			),
			"SET_META_KEYWORDS" => array(
				"PARENT" => "SEO",
				"NAME" => GetMessage("SET_META_KEYWORDS"),
				"TYPE" => "CHECKBOX",
				"DEFAULT" => "N",
				"REFRESH" => "Y"
			),
			"SET_META_DESCRIPTION" => array(
				"PARENT" => "SEO",
				"NAME" => GetMessage("SET_META_DESCRIPTION"),
				"TYPE" => "CHECKBOX",
				"DEFAULT" => "N",
				"REFRESH" => "Y"
			),
			"SET_LAST_MODIFIED" => array(
				"PARENT" => "EXTRA_PARAMS",
				"NAME" => GetMessage("SET_LAST_MODIFIED"),
				"TYPE" => "CHECKBOX",
				"DEFAULT" => "N",
				"REFRESH" => "Y"
			),
			"SET_VIEWED_IN_COMPONENT" => array(
				"PARENT" => "EXTRA_PARAMS",
				"NAME" => GetMessage("SET_VIEWED_IN_COMPONENT"),
				"TYPE" => "CHECKBOX",
				"DEFAULT" => "N",
				"REFRESH" => "Y"
			),
			"CACHE_TIME" => Array("DEFAULT" => "360000"),
		)
	);

	$arComponentParameters["PARAMETERS"]["CONVERT_CURRENCY"] = array(
		"PARENT" => "PRICES",
		"NAME" => GetMessage("CONVERT_CURRENCY"),
		"TYPE" => "CHECKBOX",
		"DEFAULT" => "N",
		"REFRESH" => "Y",
	);

	if (isset($arCurrentValues["CONVERT_CURRENCY"]) && $arCurrentValues["CONVERT_CURRENCY"] === "Y"){
		$arComponentParameters["PARAMETERS"]["CURRENCY_ID"] = array(
			"PARENT" => "PRICES",
			"NAME" => GetMessage("CURRENCY_ID"),
			"TYPE" => "LIST",
			"VALUES" => Currency\CurrencyManager::getCurrencyList(),
			"DEFAULT" => Currency\CurrencyManager::getBaseCurrency(),
			"ADDITIONAL_VALUES" => "Y",
		);
	}


}
?>