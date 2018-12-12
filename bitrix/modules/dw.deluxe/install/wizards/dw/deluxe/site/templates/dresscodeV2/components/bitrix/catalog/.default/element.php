<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();
$this->setFrameMode(true);?>
<?
	$this->SetViewTarget("menuRollClass");?> menuRolled<?$this->EndViewTarget();
	$this->SetViewTarget("hiddenZoneClass");?>hiddenZone<?$this->EndViewTarget();
?>
<?
	$arCache = array(
		"SITE_ID" => SITE_ID,
		"CACHE_INDEX" => "DETAIL",
		"ID" => $arResult["VARIABLES"]["ELEMENT_CODE"],
		"SECTION_CODE" => $arResult["VARIABLES"]["SECTION_CODE"],
		"SECTION_CODE_PATH" => $arResult["VARIABLES"]["SECTION_CODE_PATH"]
	);

	$obCache = new CPHPCache();
	
	if($obCache->InitCache($arParams["CACHE_TIME"], serialize($arCache), "/")){
	   $arResult["EXTRA"] = $obCache->GetVars();
	}

	elseif($obCache->StartDataCache()){
		
		if(!empty($arResult["VARIABLES"]["ELEMENT_CODE"]) && CModule::IncludeModule("iblock")){
			
			$arSelect = Array("ID", "IBLOCK_ID", "NAME");
			$arFilter = Array("ACTIVE" => "Y");
			$arFilter["CODE"] = $arResult["VARIABLES"]["ELEMENT_CODE"];
			$arFilter["IBLOCK_ID"] = $arParams["IBLOCK_ID"];
			// $arFilter["INCLUDE_SUBSECTIONS"] = "Y";

			//show deactivated products
			if(!empty($arParams["SHOW_DEACTIVATED"]) && $arParams["SHOW_DEACTIVATED"] == "Y"){
				$arFilter["ACTIVE"] = "";
			}

			//check for 404 error
			if(!empty($arResult["VARIABLES"]["SECTION_CODE_PATH"])){
				$arSectionPath = explode("/", $arResult["VARIABLES"]["SECTION_CODE_PATH"]);
				$arFilter["=SECTION_CODE"] = $arSectionPath;
			}elseif(!empty($arResult["VARIABLES"]["SECTION_CODE"])){
				$arFilter["=SECTION_CODE"] = $arResult["VARIABLES"]["SECTION_CODE"];				
			}

			$res = CIBlockElement::GetList(Array(), $arFilter, false, false, $arSelect);
			if($ob = $res->GetNextElement()){
				//save
				$arResult["EXTRA"] = $ob->getFields();
			}

			else{
				//abort cache
				$obCache->AbortDataCache();
			}

		}

		$obCache->EndDataCache($arResult["EXTRA"]);

	}

?>
<?$this->SetViewTarget("no_main_container");?>
<?$APPLICATION->IncludeComponent(
	"dresscode:catalog.item", 
	"detail", 
	array(
		"PRODUCT_ID" => (!empty($arResult["VARIABLES"]["ELEMENT_ID"]) ? $arResult["VARIABLES"]["ELEMENT_ID"] : (!empty($arResult["EXTRA"]["ID"]) ? $arResult["EXTRA"]["ID"] : "-")),
		"ADD_ELEMENT_CHAIN" => (isset($arParams["ADD_ELEMENT_CHAIN"]) ? $arParams["ADD_ELEMENT_CHAIN"] : ""),
		"OFFERS_TABLE_DISPLAY_PICTURE_COLUMN" => $arParams["OFFERS_TABLE_DISPLAY_PICTURE_COLUMN"],
		"SECTION_URL" => $arResult["FOLDER"].$arResult["URL_TEMPLATES"]["section"],
		"DETAIL_URL" => $arResult["FOLDER"].$arResult["URL_TEMPLATES"]["element"],
		"OFFERS_TABLE_PAGER_COUNT" => $arParams["OFFERS_TABLE_PAGER_COUNT"],
		"SECTION_CODE_PATH" => $arResult["VARIABLES"]["SECTION_CODE_PATH"],
		"PRODUCT_DISPLAY_PROPERTIES" => $arParams["PRODUCT_PROPERTIES"],
		"SET_CANONICAL_URL" => $arParams["DETAIL_SET_CANONICAL_URL"],
		"DISPLAY_OFFERS_TABLE" => $arParams["DISPLAY_OFFERS_TABLE"],
		"META_DESCRIPTION" => $arParams["DETAIL_META_DESCRIPTION"],
		"SERVICES_IBLOCK_TYPE" => $arParams["SERVICES_IBLOCK_TYPE"],
		"SECTION_CODE" => $arResult["VARIABLES"]["SECTION_CODE"],
		"SERVICES_IBLOCK_ID" => $arParams["SERVICES_IBLOCK_ID"],
		"HIDE_AVAILABLE_TAB" => $arParams["HIDE_AVAILABLE_TAB"],
		"HIDE_NOT_AVAILABLE" => $arParams["HIDE_NOT_AVAILABLE"],
		"REVIEW_IBLOCK_TYPE" => $arParams["REVIEW_IBLOCK_TYPE"],
		"HIDE_DELIVERY_CALC" => $arParams["HIDE_DELIVERY_CALC"],
		"SET_LAST_MODIFIED" => $arParams["SET_LAST_MODIFIED"],
		"META_KEYWORDS" => $arParams["DETAIL_META_KEYWORDS"],
		"BROWSER_TITLE" => $arParams["DETAIL_BROWSER_TITLE"],
		"REVIEW_IBLOCK_ID" => $arParams["REVIEW_IBLOCK_ID"],
		"SECTION_ID" => $arResult["VARIABLES"]["SECTION_ID"],
		"SALE_IBLOCK_TYPE" => $arParams["SALE_IBLOCK_TYPE"],
		"CONVERT_CURRENCY" => $arParams["CONVERT_CURRENCY"],
		"SHOW_DEACTIVATED" => $arParams["SHOW_DEACTIVATED"],
		"DISPLAY_CHEAPER" => $arParams["DISPLAY_CHEAPER"],
		"CHEAPER_FORM_ID" => $arParams["CHEAPER_FORM_ID"],
		"SALE_IBLOCK_ID" => $arParams["SALE_IBLOCK_ID"],
		"PRODUCT_PRICE_CODE" => $arParams["PRICE_CODE"],
		"PICTURE_HEIGHT" => $arParams["PICTURE_HEIGHT"],
		"SET_STATUS_404" => $arParams["SET_STATUS_404"],
		"SHOW_SERVICES" => $arParams["SHOW_SERVICES"],
		"PICTURE_WIDTH" => $arParams["PICTURE_WIDTH"],
		"HIDE_MEASURES" => $arParams["HIDE_MEASURES"],
		"CURRENCY_ID" => $arParams["CURRENCY_ID"],
		"MESSAGE_404" => $arParams["MESSAGE_404"],
		"IBLOCK_TYPE" => $arParams["IBLOCK_TYPE"],
		"CACHE_TIME" => $arParams["CACHE_TIME"],
		"CACHE_TYPE" => $arParams["CACHE_TYPE"],
		"USE_REVIEW" => $arParams["USE_REVIEW"],
		"SET_TITLE" => $arParams["SET_TITLE"],
		"IBLOCK_ID" => $arParams["IBLOCK_ID"],
		"SHOW_404" => $arParams["SHOW_404"],
		"FILE_404" => $arParams["FILE_404"],
		"SET_VIEWED_IN_COMPONENT" => "Y",
		"DISPLAY_MORE_PICTURES" => "Y",
		"SET_META_DESCRIPTION" => "Y",
		"DISPLAY_LAST_SECTION" => "Y",
		"DISPLAY_FILES_VIDEO" => "Y",
		"ADD_SECTIONS_CHAIN" => "Y",
		"SET_META_KEYWORDS" => "Y",
		"SET_BROWSER_TITLE" => "Y",
		"GET_MORE_PICTURES" => "Y", // more picture + detail picture
		"DISPLAY_RELATED" => "Y",
		"DISPLAY_SIMILAR" => "Y",
		"DETAIL_ELEMENT" => "Y",
		"ADD_OPEN_GRAPH" => "Y",
		"DISPLAY_BRAND" => "Y"
	),
	false,
	array("HIDE_ICONS" => "Y")
);?><?$this->EndViewTarget();?>