<?
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true) die();
	use Bitrix\Main\Loader;
	use Bitrix\Iblock;
	use Bitrix\Currency;

	global $USER_FIELD_MANAGER;

	if (!Loader::includeModule('iblock'))
	return;
	
	$catalogIncluded = Loader::includeModule('catalog');
	$iblockExists = (!empty($arCurrentValues['IBLOCK_ID']) && (int)$arCurrentValues['IBLOCK_ID'] > 0);

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

	$res = CIBlockType::GetList();
	while($arRes = $res->Fetch()){
		$IBLOCK_TYPE[$arRes["ID"]] = $arRes["ID"];
	}

	$res = CIBlock::GetList(
	    Array(),
	    Array('TYPE' => $arCurrentValues["IBLOCK_TYPE"])
	);

	while($arRes = $res->Fetch()){
		$IBLOCKS[$arRes['ID']] = $arRes['NAME'];
	}

	$arPrice = array();
	$rsPrice = CCatalogGroup::GetList($v1 = "sort", $v2 = "asc");
	while($arr = $rsPrice->Fetch()){
		$arPrice[$arr["NAME"]] = "[".$arr["NAME"]."] ".$arr["NAME_LANG"];
	}

	$res = CIBlockProperty::GetList(Array("sort" => "asc", "name" => "asc"), Array("ACTIVE" => "Y", "IBLOCK_ID" => $arCurrentValues["IBLOCK_ID"]));
	while ($arRes = $res->GetNext()){
				
		if($arRes["PROPERTY_TYPE"] == "L"){
			$PROPERTIES[$arRes["CODE"]] = preg_replace("/\[.*\]/", "", $arRes["NAME"])." [".$arRes["CODE"]."]";
		}
	
	}

	$res = CIBlockPropertyEnum::GetList(Array("DEF"=>"DESC", "SORT"=>"ASC"), Array("IBLOCK_ID" => $arCurrentValues["IBLOCK_ID"], "CODE" => $arCurrentValues["PROP_NAME"]));
	while($arRes = $res->GetNext()){
		$PROP_VALUES[$arRes["ID"]] = $arRes["VALUE"];
	}

	$arProperty = array();

	if ($iblockExists){
		$propertyIterator = Iblock\PropertyTable::getList(array(
			'select' => array('ID', 'IBLOCK_ID', 'NAME', 'CODE', 'PROPERTY_TYPE', 'MULTIPLE', 'LINK_IBLOCK_ID', 'USER_TYPE'),
			'filter' => array('=IBLOCK_ID' => $arCurrentValues['IBLOCK_ID'], '=ACTIVE' => 'Y'),
			'order' => array('SORT' => 'ASC', 'NAME' => 'ASC')
		));
		while ($property = $propertyIterator->fetch()){
			$propertyCode = (string)$property['CODE'];

			if ($propertyCode == '')
				$propertyCode = $property['ID'];
			$propertyName = '['.$propertyCode.'] '.$property['NAME'];

			if ($property['PROPERTY_TYPE'] != Iblock\PropertyTable::TYPE_FILE){
				$arProperty[$propertyCode] = $propertyName;
			}
		}
		unset($propertyCode, $propertyName, $property, $propertyIterator);
	}

	$arComponentParameters = array(
		"GROUPS" =>	array ("PRICES" => array(
			"NAME" => GetMessage("IBLOCK_PRICES"),
			"SORT" => 200
		),
		"FILTER" => array(
				"NAME" => GetMessage("FILTER"),
				"SORT" => "180"
		)),
		"PARAMETERS" => array(
			"IBLOCK_TYPE" => array(
		         "PARENT" => "BASE",
		         "NAME" => GetMessage("IBLOCK_TYPE"),
		         "TYPE" => "LIST",
		          "VALUES" => $IBLOCK_TYPE,
		          "REFRESH" => "Y",
			),
			"IBLOCK_ID" => array(
		         "PARENT" => "BASE",
		         "NAME" => GetMessage("IBLOCK_ID"),
		         "TYPE" => "LIST",
		          "VALUES" => $IBLOCKS,
		          "REFRESH" => "Y",
			),
			"PRICE_CODE" => array(
		         "PARENT" => "PRICES",
		         "NAME" => GetMessage("PRICE_CODE"),
		         "TYPE" => "LIST",
		         "MULTIPLE" => "Y",
		          "VALUES" => $arPrice,
		          "REFRESH" => "Y",
			),
			"FILTER_PRICE_CODE" => array(
				"PARENT" => "FILTER",
				"NAME" => GetMessage("FILTER_PRICE_CODE"),
				"TYPE" => "LIST",
				"MULTIPLE" => "Y",
				"VALUES" => $arPrice,
			),
			"DISABLE_SELECT_CATEGORY" => array(
				"PARENT" => "BASE",
				"NAME" => GetMessage("DISABLE_SELECT_CATEGORY"),
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
			"PROP_VALUE" => array(
			     "PARENT" => "FILTER",
			     "NAME" => GetMessage("OFFERS_PROP_VALUE"),
		         "TYPE" => "LIST",
		         "MULTIPLE" => "N",
		         "VALUES" => $PROP_VALUES
			),
			"PROPERTY_CODE" => array(
				"PARENT" => "VISUAL",
				"NAME" => GetMessage("IBLOCK_PROPERTY"),
				"TYPE" => "LIST",
				"MULTIPLE" => "Y",
				"VALUES" => $arProperty,
				"ADDITIONAL_VALUES" => "Y",
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
			"CACHE_TIME" => Array("DEFAULT" => "3600000"),
		)
	);

	$arComponentParameters["PARAMETERS"]['CONVERT_CURRENCY'] = array(
		'PARENT' => 'PRICES',
		'NAME' => GetMessage('CP_BCS_CONVERT_CURRENCY'),
		'TYPE' => 'CHECKBOX',
		'DEFAULT' => 'N',
		'REFRESH' => 'Y',
	);

	if (isset($arCurrentValues['CONVERT_CURRENCY']) && $arCurrentValues['CONVERT_CURRENCY'] == 'Y'){
		$arComponentParameters['PARAMETERS']['CURRENCY_ID'] = array(
			'PARENT' => 'PRICES',
			'NAME' => GetMessage('CP_BCS_CURRENCY_ID'),
			'TYPE' => 'LIST',
			'VALUES' => Currency\CurrencyManager::getCurrencyList(),
			'DEFAULT' => Currency\CurrencyManager::getBaseCurrency(),
			"ADDITIONAL_VALUES" => "Y",
		);
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
