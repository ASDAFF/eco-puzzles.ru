<?
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true) die();

//d7 namespace
use Bitrix\Main\Loader;
use Bitrix\Main\Web\Json;
use Bitrix\Iblock;
use Bitrix\Currency;

if(CModule::IncludeModule("iblock") && CModule::IncludeModule("sale") && CModule::IncludeModule("catalog")){

	//catalog item templates
	$catalogItemPath = $_SERVER["DOCUMENT_ROOT"]."/bitrix/components/dresscode/catalog.item/templates/";
	$catalogItemTemplates = array();
	
	if(is_dir($catalogItemPath)){
		$catalogItemTemplates = dirToArray($catalogItemPath);
	}

	$IBLOCKS     = array();
	$IBLOCK_TYPE = array();
	$PROPERTIES  = array();
	$PROP_VALUES = array();

	$SORT_VALUES = array(
		"SORT" => "SORT",
		"NAME" => "NAME",
		"timestamp_x" => "timestamp_x"
	);

	$dbPriceType = CCatalogGroup::GetList(
        array("SORT" => "ASC"),
        array()
    );

	while ($arPriceType = $dbPriceType->Fetch()){
	    $SORT_VALUES["CATALOG_PRICE_".$arPriceType["ID"]] = "PRICE - ".$arPriceType["NAME"];
	}

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

	$res = CIBlockProperty::GetList(Array("sort" => "asc", "name" => "asc"), Array("ACTIVE" => "Y", "IBLOCK_ID" => $arCurrentValues["IBLOCK_ID"]));
	while ($arRes = $res->GetNext()){

		$SORT_VALUES["PROPERTY_".$arRes["CODE"]] = $arRes["CODE"];

		if($arRes["PROPERTY_TYPE"] == "L"){
			$PROPERTIES[$arRes["CODE"]] = preg_replace("/\[.*\]/", "", $arRes["NAME"])." [".$arRes["CODE"]."]";
		}

	}

	$res = CIBlockPropertyEnum::GetList(Array("SORT" => "ASC"), Array("IBLOCK_ID" => $arCurrentValues["IBLOCK_ID"], "CODE" => $arCurrentValues["PROP_NAME"]));
	while($arRes = $res->GetNext()){
		$PROP_VALUES["_".$arRes["ID"]] = $arRes["VALUE"]; // disable bitrix standart component sort
	}

	$arPrice = CCatalogIBlockParameters::getPriceTypesList();

	$arComponentParameters = array(
		"GROUPS" => array(
			"PICTURE" => array(
				"NAME" => GetMessage("PICTURE"),
				"SORT" => "200"
			),
			"FILTER" => array(
				"NAME" => GetMessage("FILTER"),
				"SORT" => "180"
			),
			"PRICES" => array(
				"NAME" => GetMessage("PRICES_PARAMS"),
				"SORT" => "200"
			),			
			"SORT" => array(
				"NAME" => GetMessage("SORT"),
				"SORT" => "190"
			),
		),
		"PARAMETERS" => array(
			"CATALOG_ITEM_TEMPLATE" => array(
				"PARENT" => "BASE",
				"NAME" => GetMessage("CATALOG_ITEM_TEMPLATES"),
				"TYPE" => "LIST",
				"VALUES" => $catalogItemTemplates,
				"REFRESH" => "Y",
				"DEFAULT" => "special"
			),
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
			"PROP_NAME" => array(
			     "PARENT" => "FILTER",
			     "NAME" => GetMessage("OFFERS_PROP"),
		         "TYPE" => "LIST",
		         "VALUES" => $PROPERTIES,
		         "REFRESH" => "Y"
			),
			"ELEMENTS_COUNT" => array(
			     "PARENT" => "FILTER",
			     "NAME" => GetMessage("ELEMENTS_COUNT"),
		         "TYPE" => "STRING",
		         "DEFAULT" => "20"
			),
			"SORT_PROPERTY_NAME" => array(
				"PARENT" => "SORT",
				"NAME" => GetMessage("SORT_PROPERTY_NAME"),
				"TYPE" => "LIST",
				"VALUES" => $SORT_VALUES,
				"DEFAULT" => "timestamp_x",
				"ADDITIONAL_VALUES" => "Y"
			),
			"SORT_VALUE" => array(
			     "PARENT" => "SORT",
			     "NAME" => GetMessage("SORT_VALUE"),
		         "TYPE" => "LIST",
		         "VALUES" => array(
		         	"ASC" => GetMessage("ASC"),
		         	"DESC" => GetMessage("DESC")
		         ),
		         "DEFAULT" => "DESC"
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

function dirToArray($dir) { 
   
   //vars
   $arScanResult = array(); 

   //scan
   $cdir = scandir($dir); 
  
   foreach ($cdir as $key => $nextElement){ 
    
      if (!in_array($nextElement, array(".", ".."))){ 
        
         if(is_dir($dir . DIRECTORY_SEPARATOR . $nextElement)){ 
			$arScanResult[$nextElement] = $nextElement; 
         } 

      } 

   } 
   
   return $arScanResult; 
} 

?>