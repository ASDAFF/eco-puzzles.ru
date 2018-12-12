<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();

global $USER;

$OPTION_ADD_CART  = COption::GetOptionString("catalog", "default_can_buy_zero");
$OPTION_CURRENCY  = CCurrency::GetBaseCurrency();

if($OPTION_ADD_CART === "N" && $arResult["ELEMENT"]["CATALOG_QUANTITY"] <= 0){
	$arResult = array();
}

if(!empty($arResult)){

	$arResult["ELEMENT"]["ALL_PRICE"] = 0;
	$arResult["ELEMENT"]["ALL_PRICE_DISCOUNT"] = 0;

	if(!empty($arResult["ELEMENT"])){
		$rsElement = CIBlockElement::GetList(array(),array("ID" => $arResult["ID"], "ACTIVE" => "Y"), false, false, array("ID", "IBLOCK_ID"))->GetNextElement(); 
		if(!empty($rsElement)){
			$arResult["ELEMENT"]["PROPERTIES"] = $rsElement->GetProperties();
		}
	}

	if ($arResult["ELEMENT"]['DETAIL_PICTURE'] || $arResult["ELEMENT"]['PREVIEW_PICTURE'])
	{
		$arFileTmp = CFile::ResizeImageGet(
			$arResult["ELEMENT"]['DETAIL_PICTURE'] ? $arResult["ELEMENT"]['DETAIL_PICTURE'] : $arResult["ELEMENT"]['PREVIEW_PICTURE'],
			array("width" => "350", "height" => "300"),
			BX_RESIZE_IMAGE_PROPORTIONAL,
			true
		);
		$arResult["ELEMENT"]['DETAIL_PICTURE'] = $arFileTmp;
	}

	$arResult["ELEMENT"]["TMP_PRICE"] = CCatalogProduct::GetOptimalPrice($arResult["ELEMENT"]["ID"], 1, $USER->GetUserGroupArray());
    $arResult["ELEMENT"]["PRICE_DISCOUNT_VALUE"] = $arResult["ELEMENT"]["TMP_PRICE"]["DISCOUNT_PRICE"];
	$arResult["ELEMENT"]["PRICE_PRINT_DISCOUNT_VALUE"] = CurrencyFormat($arResult["ELEMENT"]["TMP_PRICE"]["DISCOUNT_PRICE"], $OPTION_CURRENCY);
	$arResult["ELEMENT"]["PRICE_DISCOUNT_DIFFERENCE_VALUE"] = $arResult["ELEMENT"]["TMP_PRICE"]["RESULT_PRICE"]["DISCOUNT"];
	$arResult["ELEMENT"]["PRICE_PRINT_VALUE"] = CurrencyFormat($arResult["ELEMENT"]["TMP_PRICE"]["RESULT_PRICE"]["BASE_PRICE"], $OPTION_CURRENCY);

	$arDefaultSetIDs = array($arResult["ELEMENT"]["ID"]);
	foreach (array("DEFAULT", "OTHER") as $nx => $type)
	{
		foreach ($arResult["SET_ITEMS"][$type] as $key => $arItem)
		{
			$rsElement = CIBlockElement::GetList(array(),array("ID" => $arItem["ID"]), false, false, array("ID", "IBLOCK_ID", "CATALOG_QUANTITY"))->GetNextElement();
			if(!empty($rsElement)){
				$arItem["ELEMENT_INFO"] = $rsElement->GetFields();
				$arItem["PROPERTIES"]   = $rsElement->GetProperties();
				if($nx == "DEFAULT"){
					$arResult["ELEMENT"]["ALL_PRICE"] += $arItem["PRICE_DISCOUNT_VALUE"];
					$arResult["ELEMENT"]["ALL_PRICE_DISCOUNT"] += $arItem["PRICE_DISCOUNT_DIFFERENCE_VALUE"];
				}
			}

			$arButtons = CIBlock::GetPanelButtons(
				$arItem["ELEMENT_INFO"]["IBLOCK_ID"],
				$arItem["ID"],
				$arItem["ID"],
				array("SECTION_BUTTONS" => false,
					  "SESSID" => false,
					  "CATALOG" => true
				)
			);

			$arElement = array(
				"ID"							  => $arItem["ID"],
				"NAME"             				  => $arItem["NAME"],
				"DETAIL_PAGE_URL"  				  => $arItem["DETAIL_PAGE_URL"],
				"DETAIL_PICTURE"   				  => $arItem["DETAIL_PICTURE"],
				"PREVIEW_PICTURE"  				  => $arItem["PREVIEW_PICTURE"],
				"PROPERTIES"      				  => $arItem["PROPERTIES"],
				"PRICE_CURRENCY"   				  => $arItem["PRICE_CURRENCY"],
				"PRICE_DISCOUNT_VALUE"			  => $arItem["PRICE_DISCOUNT_VALUE"],
				"PRICE_PRINT_DISCOUNT_VALUE" 	  => $arItem["PRICE_PRINT_DISCOUNT_VALUE"],
				"PRICE_VALUE"				 	  => $arItem["PRICE_VALUE"],
				"PRICE_PRINT_VALUE" 			  => $arItem["PRICE_PRINT_VALUE"],
				"PRICE_DISCOUNT_DIFFERENCE_VALUE" => $arItem["PRICE_DISCOUNT_DIFFERENCE_VALUE"],
				"PRICE_DISCOUNT_DIFFERENCE" 	  => $arItem["PRICE_DISCOUNT_DIFFERENCE"],
				"ENABLED"						  => $OPTION_ADD_CART === "Y" ? true : $arItem["ELEMENT_INFO"]["CATALOG_QUANTITY"] > 0
			);

			$arElement["EDIT_LINK"] = $arButtons["edit"]["edit_element"]["ACTION_URL"];
			$arElement["DELETE_LINK"] = $arButtons["edit"]["delete_element"]["ACTION_URL"];
			$arElement["TYPE"] = $type;

			if ($arItem["PRICE_CONVERT_DISCOUNT_VALUE"])
				$arElement["PRICE_CONVERT_DISCOUNT_VALUE"] = $arItem["PRICE_CONVERT_DISCOUNT_VALUE"];
			if ($arItem["PRICE_CONVERT_VALUE"])
				$arElement["PRICE_CONVERT_VALUE"] = $arItem["PRICE_CONVERT_VALUE"];
			if ($arItem["PRICE_CONVERT_DISCOUNT_DIFFERENCE_VALUE"])
				$arElement["PRICE_CONVERT_DISCOUNT_DIFFERENCE_VALUE"] = $arItem["PRICE_CONVERT_DISCOUNT_DIFFERENCE_VALUE"];

			if ($type == "DEFAULT")
				$arDefaultSetIDs[] = $arItem["ID"];
			if ($arItem['DETAIL_PICTURE'] || $arItem['PREVIEW_PICTURE'])
			{
				$arFileTmp = CFile::ResizeImageGet(
					$arItem['DETAIL_PICTURE'] ? $arItem['DETAIL_PICTURE'] : $arItem['PREVIEW_PICTURE'],
					array("width" => "350", "height" => "300"),
					BX_RESIZE_IMAGE_PROPORTIONAL,
					true
				);
				$arElement['DETAIL_PICTURE'] = $arFileTmp;
			}

			if($arElement["ENABLED"]){
				$arResult["SET_ITEMS"][$type][$key] = $arElement;
			}
		}
	}
		$arResult["ELEMENT"]["ALL_PRICE"] = $arResult["ELEMENT"]["ALL_PRICE"] + $arResult["ELEMENT"]["PRICE_DISCOUNT_VALUE"];
		$arResult["ELEMENT"]["ALL_PRICE_DISCOUNT"] = formatCurrency(($arResult["ELEMENT"]["ALL_PRICE"] + $arResult["ELEMENT"]["ALL_PRICE_DISCOUNT"] + $arResult["ELEMENT"]["PRICE_DISCOUNT_DIFFERENCE_VALUE"]), $arParams["CURRENCY_ID"]);
		$arResult["ELEMENT"]["ALL_PRICE"] = formatCurrency($arResult["ELEMENT"]["ALL_PRICE"], $arParams["CURRENCY_ID"]);

	$arResult["DEFAULT_SET_IDS"] = $arDefaultSetIDs;
}
?>

