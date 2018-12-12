<?

	if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true)
		die();
	
	//d7 namespace
	use Bitrix\Highloadblock as HL,
		Bitrix\Main\Type\DateTime,
		Bitrix\Main\Application,
		Bitrix\Main\Diag\Debug,
		Bitrix\Main\Context,
		Bitrix\Main\Config,
		Bitrix\Main\Entity,
		Bitrix\Currency,
		Bitrix\Catalog,
		Bitrix\Iblock;

	global $USER;
	global $APPLICATION;

	if (!isset($arParams["CACHE_TIME"])){
		$arParams["CACHE_TIME"] = 1285912;
	}

	//extra params
	$arParams["DISPLAY_FORMAT_PROPERTIES"] = !empty($arParams["DISPLAY_FORMAT_PROPERTIES"]) ? $arParams["DISPLAY_FORMAT_PROPERTIES"] : "N";
	$arParams["DISPLAY_MORE_PICTURES"] = !empty($arParams["DISPLAY_MORE_PICTURES"]) ? $arParams["DISPLAY_MORE_PICTURES"] : "N";
	$arParams["DISPLAY_LAST_SECTION"] = !empty($arParams["DISPLAY_LAST_SECTION"]) ? $arParams["DISPLAY_LAST_SECTION"] : "N";
	$arParams["DISPLAY_OFFERS_TABLE"] = !empty($arParams["DISPLAY_OFFERS_TABLE"]) ? $arParams["DISPLAY_OFFERS_TABLE"] : "N";
	$arParams["DISPLAY_FILES_VIDEO"] = !empty($arParams["DISPLAY_FILES_VIDEO"]) ? $arParams["DISPLAY_FILES_VIDEO"] : "N";
	$arParams["SET_CANONICAL_URL"] = !empty($arParams["SET_CANONICAL_URL"]) ? $arParams["SET_CANONICAL_URL"] : "N";
	$arParams["DISPLAY_RELATED"] = !empty($arParams["DISPLAY_RELATED"]) ? $arParams["DISPLAY_RELATED"] : "N";
	$arParams["DISPLAY_SIMILAR"] = !empty($arParams["DISPLAY_SIMILAR"]) ? $arParams["DISPLAY_SIMILAR"] : "N";
	$arParams["DISPLAY_BRAND"] = !empty($arParams["DISPLAY_BRAND"]) ? $arParams["DISPLAY_BRAND"] : "N";

	//clear undefined values after ajax
	foreach ($arParams as $inx => $paramValue){
		
		if(is_array($paramValue)){
			$paramValue = $paramValue[0];
		}

		if($paramValue == "undefined"){
			unset($arParams[$inx]);
		}

	}

	//clear not used params
	if($arParams["CONVERT_CURRENCY"] != "Y"){
		if(isset($arParams["CURRENCY_ID"])){
			unset($arParams["CURRENCY_ID"]);
		}
	}

	//component include (class) DwSkuOffers

	//iblock_id
	//element_id

	//parent product - product get from sku
	//main product - product get from arParams

	//set params if empty
	$arParams["PRODUCT_PRICE_CODE"] = empty($arParams["PRODUCT_PRICE_CODE"]) ? array() : $arParams["PRODUCT_PRICE_CODE"];	
	$arParams["AVAILABLE_OFFERS"] = empty($arParams["AVAILABLE_OFFERS"]) ? array() : $arParams["AVAILABLE_OFFERS"];
	$arParams["PICTURE_HEIGHT"] = empty($arParams["PICTURE_HEIGHT"]) ? "200" : $arParams["PICTURE_HEIGHT"];
	$arParams["PICTURE_WIDTH"] = empty($arParams["PICTURE_WIDTH"]) ? "220" : $arParams["PICTURE_WIDTH"];
	$arParams["IMAGE_QUALITY"] = empty($arParams["IMAGE_QUALITY"]) ? "80" : $arParams["IMAGE_QUALITY"];
	$arParams["IBLOCK_ID"] = empty($arParams["IBLOCK_ID"]) ?: $arParams["IBLOCK_ID"];

	if(empty($arParams["PRODUCT_ID"])){
		ShowError("product id not set!");
		return 0;			
	}

	if(empty($arParams["IBLOCK_ID"])){
		ShowError("iblock id not set!");
		return 0;			
	}

	//create cache id
	$cacheID = array(
		"NAME" => "ELEMENT_FULL_LIST",
		"PRODUCT_PRICE_CODE" => implode(",", $arParams["PRODUCT_PRICE_CODE"]),
		"PICTURE_HEIGHT" => floatval($arParams["PICTURE_HEIGHT"]),
		"HIDE_NOT_AVAILABLE" => $arParams["HIDE_NOT_AVAILABLE"],
		"PICTURE_WIDTH" => floatval($arParams["PICTURE_WIDTH"]),
		"AVAILABLE_OFFERS" => $arParams["AVAILABLE_OFFERS"],
		"CONVERT_CURRENCY" => $arParams["CONVERT_CURRENCY"],
		"PRODUCT_ID" =>	floatval($arParams["PRODUCT_ID"]),
		"CURRENCY_ID" => $arParams["CURRENCY_ID"],
		"USER_GROUPS" => $USER->GetGroups(),
		"SITE_ID" => SITE_ID
	);

	$cacheDir = "/";

	//extra settings from cache
	$obExtraCache = new CPHPCache();
	if($arParams["CACHE_TYPE"] != "N" && $obExtraCache->InitCache($arParams["CACHE_TIME"], serialize($cacheID), $cacheDir)){
		//get info by cache
		$arResult = $obExtraCache->GetVars();
		//set from cache flag
		$arResult["FROM_CACHE"] = "Y";
	}

	elseif($obExtraCache->StartDataCache()){

		//check include modules
		if(
			   !\Bitrix\Main\Loader::includeModule("dw.deluxe")
			|| !\Bitrix\Main\Loader::includeModule("iblock")
			|| !\Bitrix\Main\Loader::includeModule('highloadblock')
			|| !\Bitrix\Main\Loader::includeModule("catalog")
			|| !\Bitrix\Main\Loader::includeModule("sale")
			|| !\Bitrix\Main\Loader::includeModule("currency")
		){

			$obExtraCache->AbortDataCache();
			ShowError("modules not installed!");
			return 0;

		}

		//currency params
		$opCurrency = ($arParams["CONVERT_CURRENCY"] == "Y" && !empty($arParams["CURRENCY_ID"])) ? $arParams["CURRENCY_ID"] : NULL;

		// main array for product info
		$arElement = array();
		$arResult = array();

		//set from cache flag
		$arElement["FROM_CACHE"] = "N";

		//get parent product for current offer (if product id == offer id)
		$skuParentProduct = CCatalogSku::GetProductInfo($arParams["PRODUCT_ID"]);

		//check exist offers for product
		$arContainOffers = CCatalogSKU::getExistOffers($arParams["PRODUCT_ID"], $arParams["IBLOCK_ID"]);
		$productContainOffers = !empty($arContainOffers) ? !empty($arContainOffers[$arParams["PRODUCT_ID"]]) : false;

		//get parent product id
		if(!empty($skuParentProduct)){
			$arElement["PARENT_PRODUCT_ID"] = $skuParentProduct["ID"];
			$arElement["PARENT_PRODUCT_IBLOCK_ID"] = $skuParentProduct["IBLOCK_ID"];
		}

		// op = operation id
		//set id, iblock for calc sku
		$opIblockId = empty($skuParentProduct) ? $arParams["IBLOCK_ID"] : $arElement["PARENT_PRODUCT_IBLOCK_ID"];
		$opProductId = empty($skuParentProduct) ? $arParams["PRODUCT_ID"] : $arElement["PARENT_PRODUCT_ID"];

		//main select fields
		$arSelect = Array(
			"ID",
			"NAME",
			"CODE",
			"TIMESTAMP_X",
			"PREVIEW_TEXT",
			"DETAIL_TEXT",
			"DATE_CREATE",
			"IBLOCK_ID",
			"IBLOCK_TYPE",
			"DATE_MODIFY",
			"DATE_ACTIVE_TO",
			"DETAIL_PICTURE",
			"DATE_ACTIVE_FROM",
			"CATALOG_QUANTITY",
			"DETAIL_PAGE_URL",
			"IBLOCK_SECTION_ID",
			"CATALOG_MEASURE",
			"CATALOG_AVAILABLE",
			"CATALOG_SUBSCRIBE",
			"CATALOG_QUANTITY_TRACE",
			"CATALOG_CAN_BUY_ZERO",
			"CANONICAL_PAGE_URL"
		);

		//prepare to select parent product from sku
		if(!empty($skuParentProduct) || !empty($productContainOffers)){

			//parent element filter
			$arFilter = Array(
				"IBLOCK_ID" => $opIblockId,
				"ID" => $opProductId,
				"ACTIVE_DATE" => "Y",
				"ACTIVE" => "Y"
			);

			//show deactivated products
			if(!empty($arParams["SHOW_DEACTIVATED"]) && $arParams["SHOW_DEACTIVATED"] == "Y"){
				$arFilter["ACTIVE_DATE"] = "";
				$arFilter["ACTIVE"] = "";
			}

			//select from base
			$rsBaseProduct = CIBlockElement::GetList(Array(), $arFilter, false, false, $arSelect);
			if($oBaseProduct = $rsBaseProduct->GetNextElement()){

				//write
				$arElement["PARENT_PRODUCT"] = $oBaseProduct->GetFields();
				$arElement["PARENT_PRODUCT"]["PROPERTIES"] = $oBaseProduct->GetProperties(array("sort" => "asc", "name" => "asc"), array("EMPTY" => "N"));

				//set seo params from parent product
				$seoValues = new \Bitrix\Iblock\InheritedProperty\ElementValues($arElement["PARENT_PRODUCT"]["IBLOCK_ID"], $arElement["PARENT_PRODUCT"]["ID"]);
				$arElement["PARENT_PRODUCT"]["IPROPERTY_VALUES"] = $seoValues->getValues();

				//set iblock_id from parent product
				$arElement["IBLOCK_ID"] = $arElement["PARENT_PRODUCT"]["IBLOCK_ID"];

				//set section id from parent product
				$arElement["IBLOCK_SECTION_ID"] = $arElement["PARENT_PRODUCT"]["IBLOCK_SECTION_ID"];

				//set name from parent product
				$arElement["NAME"] = $arElement["PARENT_PRODUCT"]["NAME"];

				//set name from parent product
				$arElement["DETAIL_PAGE_URL"] = $arElement["PARENT_PRODUCT"]["DETAIL_PAGE_URL"];

				//set preview text from parent product
				$arElement["PREVIEW_TEXT"] = $arElement["PARENT_PRODUCT"]["PREVIEW_TEXT"];

				//set detail text from parent product
				$arElement["DETAIL_TEXT"] = $arElement["PARENT_PRODUCT"]["DETAIL_TEXT"];
				$arElement["~DETAIL_TEXT"] = $arElement["PARENT_PRODUCT"]["~DETAIL_TEXT"];

				//set picture from parent product
				if(!empty($arElement["PARENT_PRODUCT"]["DETAIL_PICTURE"])){
					$arElement["PICTURE"] = CFile::ResizeImageGet($arElement["PARENT_PRODUCT"]["DETAIL_PICTURE"], array("width" => $arParams["PICTURE_WIDTH"], "height" => $arParams["PICTURE_HEIGHT"]), BX_RESIZE_IMAGE_PROPORTIONAL, false, false, false, $arParams["IMAGE_QUALITY"]);
				}

				if(!empty($arElement["PARENT_PRODUCT"]["CANONICAL_PAGE_URL"])){
					$arElement["CANONICAL_PAGE_URL"] = $arElement["PARENT_PRODUCT"]["CANONICAL_PAGE_URL"];
				}

				//edit buttons
				$arButtons = CIBlock::GetPanelButtons(
					$arElement["PARENT_PRODUCT"]["IBLOCK_ID"],
					$arElement["PARENT_PRODUCT"]["ID"],
					$arElement["PARENT_PRODUCT"]["IBLOCK_SECTION_ID"],
					array("SECTION_BUTTONS" => true,
						  "SESSID" => true,
						  "CATALOG" => true
					)
				);

				$arElement["PARENT_PRODUCT"]["EDIT_LINK"] = $arButtons["edit"]["edit_element"]["ACTION_URL"];
				$arElement["PARENT_PRODUCT"]["DELETE_LINK"] = $arButtons["edit"]["delete_element"]["ACTION_URL"];

			}

		}

		//prepare to select product from arParams

		//if product has sku exist
		if(!empty($skuParentProduct) || !empty($productContainOffers)){

			//get sku iblock info
			$arOffersSkuInfo = CCatalogSKU::GetInfoByProductIBlock($opIblockId);

			//set first sku offers
			$opFirstOfferId = !empty($skuParentProduct) ? $arParams["PRODUCT_ID"] : false;

			//offers id filter
			$opOffersFilterId = !empty($arParams["AVAILABLE_OFFERS"]) ? $arParams["AVAILABLE_OFFERS"]  : false;

			//set sku params
			$arSkuParams = array(
				"PRODUCT_PRICE_CODE" => $arParams["PRODUCT_PRICE_CODE"],
				"HIDE_NOT_AVAILABLE" => $arParams["HIDE_NOT_AVAILABLE"],
				"PICTURE_HEIGHT" => $arParams["PICTURE_HEIGHT"],
				"PICTURE_WIDTH" =>	$arParams["PICTURE_WIDTH"],
				"IMAGE_QUALITY" => $arParams["IMAGE_QUALITY"]
			);

			//set filter for sku offers
			if(!empty($arParams["PRODUCT_SKU_FILTER"])){

				//clear section id for sku
				if(isset($arParams["PRODUCT_SKU_FILTER"]["SECTION_ID"])){
					unset($arParams["PRODUCT_SKU_FILTER"]["SECTION_ID"]);
				}

				//save filter
				$arSkuParams["FILTER"] = $arParams["PRODUCT_SKU_FILTER"];

			}

			$arSkuOffersFromProduct = DwSkuOffers::getSkuFromProduct(
				$opProductId, // set main (parent) product id
				$opIblockId, // set main (parent) iblock id
				$opOffersFilterId, //set offers id filter
				$opFirstOfferId, //set first sku offers
				$arOffersSkuInfo, //sku iblock info
				$arSkuParams, //send sku params
				$opCurrency // currency
			);

			//set result from sku offers
			if(!empty($arSkuOffersFromProduct)){

				if(!empty($arElement)){

					//merge properties (parent product & main product)
					$arSkuOffersFromProduct["PROPERTIES"] = array_merge($arElement["PARENT_PRODUCT"]["PROPERTIES"], $arSkuOffersFromProduct["PROPERTIES"]);

					//merge parent product & sku offer
					$arElement = array_merge($arElement, $arSkuOffersFromProduct);

					//create display properties
					// foreach ($arElement["PROPERTIES"] as $arNextProperty){
					// 	// $arElement["DISPLAY_PROPERTIES"][$arNextProperty["CODE"]] = CIBlockFormatProperties::GetDisplayValue($arElement, $arNextProperty, "catalog_out");
					// }

				}

				//info from sku iblock
				$arElement["SKU_INFO"] = $arOffersSkuInfo;

				// if parent and base product image not found
				if(empty($arElement["PICTURE"])){
					$arElement["PICTURE"]["src"] = SITE_TEMPLATE_PATH."/images/empty.png";
				}

				//target cache
				global $CACHE_MANAGER;

				//start
				$CACHE_MANAGER->StartTagCache($cacheDir);
				$CACHE_MANAGER->RegisterTag("iblock_id_".$arElement["IBLOCK_ID"]);

				//target cache for parent iblock
				if(!empty($arElement["PARENT_PRODUCT_IBLOCK_ID"])){
					$CACHE_MANAGER->RegisterTag("iblock_id_".$arElement["PARENT_PRODUCT_IBLOCK_ID"]);
				}

				//end
				$CACHE_MANAGER->EndTagCache();

			}

			// else element not found
			else{

				//abort cache
				$obExtraCache->AbortDataCache();

				//clear
				$arElement = array();
				
				//404 message
				if($arParams["DETAIL_ELEMENT"] == "Y"){
					Iblock\Component\Tools::process404(
						trim($arParams["MESSAGE_404"]) ?: GetMessage("CATALOG_ITEM_NOT_FOUND")
						,true
						,$arParams["SET_STATUS_404"] === "Y"
						,$arParams["SHOW_404"] === "Y"
						,$arParams["FILE_404"]
					);
				}

			}

		}

		//product not have sku
		else{

			//element filter
			$arFilter = Array(
				"IBLOCK_ID" => $opIblockId,
				"ID" => $arParams["PRODUCT_ID"],
				"ACTIVE_DATE" => "Y",
				"ACTIVE" => "Y"
			);

			//show deactivated products
			if(!empty($arParams["SHOW_DEACTIVATED"]) && $arParams["SHOW_DEACTIVATED"] == "Y"){
				$arFilter["ACTIVE_DATE"] = "";
				$arFilter["ACTIVE"] = "";
			}

			//select from base
			$res = CIBlockElement::GetList(Array(), $arFilter, false, false, $arSelect);
			if($ob = $res->GetNextElement()){

				//write
				$arElement = array_merge($arElement, $ob->GetFields());
				$arElement["PROPERTIES"] = $ob->GetProperties(array("sort" => "asc", "name" => "asc"), array("EMPTY" => "N"));
				$arElement["DISPLAY_PROPERTIES"] = array();

				//merge properties (parent product & main product)
				if(!empty($skuParentProduct)){
					$arElement["PROPERTIES"] = array_merge($arElement["PARENT_PRODUCT"]["PROPERTIES"], $arElement["PROPERTIES"]);
				}

				// //create display properties
				// foreach ($arElement["PROPERTIES"] as $arNextProperty){
				// 	$arElement["DISPLAY_PROPERTIES"][$arNextProperty["CODE"]] = CIBlockFormatProperties::GetDisplayValue($arElement, $arNextProperty, "catalog_out");
				// }

				//get sku info from current iblock
				$mainIblockId = !empty($arElement["PARENT_PRODUCT_IBLOCK_ID"]) ? $arElement["PARENT_PRODUCT_IBLOCK_ID"] : $arElement["IBLOCK_ID"];
				$arElement["SKU_INFO"] = CCatalogSKU::GetInfoByProductIBlock($mainIblockId);

				//picture
				if(!empty($arElement["DETAIL_PICTURE"])){
					$arElement["PICTURE"] = CFile::ResizeImageGet($arElement["DETAIL_PICTURE"], array("width" => $arParams["PICTURE_WIDTH"], "height" => $arParams["PICTURE_HEIGHT"]), BX_RESIZE_IMAGE_PROPORTIONAL, false, false, false, $arParams["IMAGE_QUALITY"]);
				}else{
					// if parent and base product image not found
					if(empty($arElement["PICTURE"])){
						$arElement["PICTURE"]["src"] = SITE_TEMPLATE_PATH."/images/empty.png";
					}
				}

				//get price info
				$arElement["EXTRA_SETTINGS"]["PRODUCT_PRICE_ALLOW"] = array();
				$arElement["EXTRA_SETTINGS"]["PRODUCT_PRICE_ALLOW_FILTER"] = array();

				if(!empty($arParams["PRODUCT_PRICE_CODE"])){

					//get available prices code & id
					$arPricesInfo = DwPrices::getPriceInfo($arParams["PRODUCT_PRICE_CODE"], $arElement["IBLOCK_ID"]);
					if(!empty($arPricesInfo)){
				    	$arElement["EXTRA_SETTINGS"]["PRODUCT_PRICE_ALLOW"] = $arPricesInfo["ALLOW"];
					    $arElement["EXTRA_SETTINGS"]["PRODUCT_PRICE_ALLOW_FILTER"] = $arPricesInfo["ALLOW_FILTER"];
					}

				}

				//get prices
				$arElement["PRICE"] = DwPrices::getPricesByProductId($arElement["ID"], $arElement["EXTRA_SETTINGS"]["PRODUCT_PRICE_ALLOW"], $arElement["EXTRA_SETTINGS"]["PRODUCT_PRICE_ALLOW_FILTER"], $arParams["PRODUCT_PRICE_CODE"], $arElement["IBLOCK_ID"], $opCurrency);
					
				//if > 0 display [?] for more price table
				$arElement["EXTRA_SETTINGS"]["COUNT_PRICES"] = $arElement["PRICE"]["COUNT_PRICES"];

				//set base currency
				$arElement["EXTRA_SETTINGS"]["CURRENCY"] = empty($opCurrency) ? $arElement["PRICE"]["RESULT_PRICE"]["CURRENCY"] : $opCurrency;

				//check for complect
				$rsComplect = CCatalogProductSet::getList(
					array("SORT" => "ASC"),
					array(
						"TYPE" => 1,
						"OWNER_ID" => $arElement["ID"],
						"!ITEM_ID" => $arElement["ID"]
					),
					false,
					false,
					array("*")
				);

				if(!$arComplectItem = $rsComplect->Fetch()){
					//stores info
					$arElement["EXTRA_SETTINGS"]["STORES_MAX_QUANTITY"] = 0;
					$rsStore = CCatalogStoreProduct::GetList(array(), array("PRODUCT_ID" => $arElement["ID"]), false, false, array("ID", "AMOUNT"));
					while($arNextStore = $rsStore->GetNext()){
						$arElement["EXTRA_SETTINGS"]["STORES"][] = $arNextStore;
						if($arNextStore["AMOUNT"] > $arElement["EXTRA_SETTINGS"]["STORES_MAX_QUANTITY"]){
							$arElement["EXTRA_SETTINGS"]["STORES_MAX_QUANTITY"] = $arNextStore["AMOUNT"];
						}
					}
				}

				//get measures for product
				$rsMeasure = CCatalogMeasure::getList(
					array(),
					array(
						"ID" => $arElement["CATALOG_MEASURE"]
					),
					false,
					false,
					false
				);

				while($arNextMeasure = $rsMeasure->Fetch()){
					$arElement["EXTRA_SETTINGS"]["MEASURES"][$arNextMeasure["ID"]] = $arNextMeasure;
				}

				//get measure ratio for product
				//default ratio
				$arElement["EXTRA_SETTINGS"]["BASKET_STEP"] = 1;

				//get ratio from BD
				$rsMeasureRatio = CCatalogMeasureRatio::getList(
					array(),
					array("PRODUCT_ID" => floatval($arElement["ID"])),
					false,
					false,
					array()
				);

				if($arProductMeasureRatio = $rsMeasureRatio->Fetch()){
					if(!empty($arProductMeasureRatio["RATIO"])){
						$arElement["EXTRA_SETTINGS"]["BASKET_STEP"] = $arProductMeasureRatio["RATIO"];
					}
				}

				//set seo params
				$seoValues = new \Bitrix\Iblock\InheritedProperty\ElementValues($arElement["IBLOCK_ID"], $arElement["ID"]);
				$arElement["IPROPERTY_VALUES"] = $seoValues->getValues();

				//edit buttons
				$arButtons = CIBlock::GetPanelButtons(
					$arElement["IBLOCK_ID"],
					$arElement["ID"],
					$arElement["IBLOCK_SECTION_ID"],
					array("SECTION_BUTTONS" => true,
						  "SESSID" => true,
						  "CATALOG" => true
					)
				);

				$arElement["EDIT_LINK"] = $arButtons["edit"]["edit_element"]["ACTION_URL"];
				$arElement["DELETE_LINK"] = $arButtons["edit"]["delete_element"]["ACTION_URL"];

				//target cache
				global $CACHE_MANAGER;
				$CACHE_MANAGER->StartTagCache($cacheDir);
				$CACHE_MANAGER->RegisterTag("iblock_id_".$arElement["IBLOCK_ID"]);
				$CACHE_MANAGER->EndTagCache();

			}

			// else element not found
			else{
				
				//404 message
				if($arParams["DETAIL_ELEMENT"] == "Y"){
					Iblock\Component\Tools::process404(
						trim($arParams["MESSAGE_404"]) ?: GetMessage("CATALOG_ITEM_NOT_FOUND")
						,true
						,$arParams["SET_STATUS_404"] === "Y"
						,$arParams["SHOW_404"] === "Y"
						,$arParams["FILE_404"]
					);
				}

				$obExtraCache->AbortDataCache();

			}

		}//

		if(!empty($arElement)){

			//extra

			//timer
			$arElement["EXTRA_SETTINGS"]["TIMER_UNIQ_ID"] = $this->randString();
			if(!empty($arElement["PROPERTIES"]["TIMER_DATE"]["VALUE"])){
				$dateDiff = MakeTimeStamp($arElement["PROPERTIES"]["TIMER_DATE"]["VALUE"], "DD.MM.YYYY HH:MI:SS") - time();
				$arElement["EXTRA_SETTINGS"]["SHOW_TIMER"] = $dateDiff > 0;
			}elseif(!empty($arElement["PROPERTIES"]["TIMER_LOOP"]["VALUE"])){
				$arElement["EXTRA_SETTINGS"]["SHOW_TIMER"] = true;
			}else{
				$arElement["EXTRA_SETTINGS"]["SHOW_TIMER"] = false;
			}

			//save cache
			$obExtraCache->EndDataCache($arElement);

			//drop
			unset($obExtraCache);

			//write end array
			$arResult = $arElement;
			unset($arElement);

		}

	}

	//check include modules
	if(!\Bitrix\Main\Loader::includeModule("dw.deluxe")){
		ShowError("modules not installed!");
		return 0;
	}

	$extraParams = array(
		"DISPLAY_FORMAT_PROPERTIES" => $arParams["DISPLAY_FORMAT_PROPERTIES"],
		"DISPLAY_MORE_PICTURES" => $arParams["DISPLAY_MORE_PICTURES"],
		"DISPLAY_OFFERS_TABLE" => $arParams["DISPLAY_OFFERS_TABLE"],
		"DISPLAY_FILES_VIDEO" => $arParams["DISPLAY_FILES_VIDEO"],
		"DISPLAY_RELATED" => $arParams["DISPLAY_RELATED"],
		"DISPLAY_SIMILAR" => $arParams["DISPLAY_SIMILAR"],
		"DISPLAY_BRAND" => $arParams["DISPLAY_BRAND"]
	);

	//get extra content (section info, more pictures, brand info, etc//)
	$extraContent = DwItemInfo::get_extra_content($arParams["CACHE_TIME"], $arParams["CACHE_TYPE"], $cacheID, $cacheDir, $extraParams, $arParams, $arResult, $opCurrency);

	if(!empty($extraContent)){
		$arResult = $extraContent;
	}

	//no cache 
	if(!empty($arResult)){

		//element seo values
		$elementTitle = (!empty($arResult["IPROPERTY_VALUES"]["ELEMENT_PAGE_TITLE"]) ? $arResult["IPROPERTY_VALUES"]["ELEMENT_PAGE_TITLE"] : (!empty($arResult["PARENT_PRODUCT"]["IPROPERTY_VALUES"]["ELEMENT_PAGE_TITLE"]) ? $arResult["PARENT_PRODUCT"]["IPROPERTY_VALUES"]["ELEMENT_PAGE_TITLE"] : $arResult["NAME"]));
		$elementBrowserTitle = (!empty($arResult["IPROPERTY_VALUES"]["ELEMENT_META_TITLE"]) ? $arResult["IPROPERTY_VALUES"]["ELEMENT_META_TITLE"] : (!empty($arResult["PARENT_PRODUCT"]["IPROPERTY_VALUES"]["ELEMENT_META_TITLE"]) ? $arResult["PARENT_PRODUCT"]["IPROPERTY_VALUES"]["ELEMENT_META_TITLE"] : $arResult["NAME"]));
		$elementMetaKeywords = (!empty($arResult["IPROPERTY_VALUES"]["ELEMENT_META_KEYWORDS"]) ? $arResult["IPROPERTY_VALUES"]["ELEMENT_META_KEYWORDS"] : (!empty($arResult["PARENT_PRODUCT"]["IPROPERTY_VALUES"]["ELEMENT_META_KEYWORDS"]) ? $arResult["PARENT_PRODUCT"]["IPROPERTY_VALUES"]["ELEMENT_META_KEYWORDS"] : ""));
		$elementMetaDescription = (!empty($arResult["IPROPERTY_VALUES"]["ELEMENT_META_DESCRIPTION"]) ? $arResult["IPROPERTY_VALUES"]["ELEMENT_META_DESCRIPTION"] : (!empty($arResult["PARENT_PRODUCT"]["IPROPERTY_VALUES"]["ELEMENT_META_DESCRIPTION"]) ? $arResult["PARENT_PRODUCT"]["IPROPERTY_VALUES"]["ELEMENT_META_DESCRIPTION"] : ""));

		//title params
		if(!empty($arParams["SET_TITLE"]) && $arParams["SET_TITLE"] == "Y"){
			$arTitleOptions = array(
				"ADMIN_EDIT_LINK" => $arButtons["submenu"]["edit_element"]["ACTION"],
				"PUBLIC_EDIT_LINK" => $arButtons["edit"]["edit_element"]["ACTION"],
				"COMPONENT_NAME" => $this->getName(),
			);
		}

		//page title
		if(!empty($arParams["SET_TITLE"]) && $arParams["SET_TITLE"] == "Y" && !empty($elementTitle)){
			$APPLICATION->SetTitle($elementTitle, $arTitleOptions);
		}

		//browser title
		if ($arParams["SET_BROWSER_TITLE"] == "Y"){
			if(!empty($elementBrowserTitle)){
				$APPLICATION->SetPageProperty("title", $elementBrowserTitle, $arTitleOptions);
			}
		}

		//meta keywords
		if($arParams["SET_META_KEYWORDS"] == "Y"){
			$APPLICATION->SetPageProperty("keywords", $elementMetaKeywords, $arTitleOptions);
		}
		
		//meta description
		if($arParams["SET_META_DESCRIPTION"] == "Y"){
			$APPLICATION->SetPageProperty("description", $elementMetaDescription, $arTitleOptions);
		}

		//nav chain section
		if($arParams["ADD_SECTIONS_CHAIN"] == "Y"){
			
			//get section from db
			if(empty($arParams["SECTION_ID"]) && !empty($arParams["SECTION_CODE"])){
				$dbSection = CIBlockSection::GetList(array(), array("CODE" => $arParams["SECTION_CODE"]), false);
				if($arSection = $dbSection->GetNext()){
					$arResult["SECTION"] = $arSection;
					$arParams["SECTION_ID"] = $arResult["SECTION"]["ID"];
				}
			}
		
			if(!empty($arResult["SECTION_PATH_LIST"])){
				foreach ($arResult["SECTION_PATH_LIST"] as $arPath){
					
					if(!empty($arPath["UF_SHOW_SKU_TABLE"])){
						$arResult["SHOW_SKU_TABLE"] = $arPath["UF_SHOW_SKU_TABLE"];
					}

					$ipropValues = new \Bitrix\Iblock\InheritedProperty\SectionValues($arParams["IBLOCK_ID"], $arPath["ID"]);
					$arPath["IPROPERTY_VALUES"] = $ipropValues->getValues();

					if (!empty($arPath["IPROPERTY_VALUES"]["SECTION_PAGE_TITLE"])){
						$APPLICATION->AddChainItem($arPath["IPROPERTY_VALUES"]["SECTION_PAGE_TITLE"], $arPath["~SECTION_PAGE_URL"]);
					}

					else{
						$APPLICATION->AddChainItem($arPath["NAME"], $arPath["~SECTION_PAGE_URL"]);
					}

				}
			}

		}

		//canonical
		if($arParams["SET_CANONICAL_URL"] == "Y"){

			//set canonical var from sku parent product 
			if(!empty($arResult["PARENT_PRODUCT"]["CANONICAL_PAGE_URL"])){
				$arResult["CANONICAL_PAGE_URL"] = $arResult["PARENT_PRODUCT"]["CANONICAL_PAGE_URL"];
			}

			//add head
			if(!empty($arResult["CANONICAL_PAGE_URL"])){
				$APPLICATION->AddHeadString('<link href="'.$arResult["CANONICAL_PAGE_URL"].'" rel="canonical" />', true);
			}

		}

		//Open Graph
		if($arParams["ADD_OPEN_GRAPH"] == "Y"){
			$APPLICATION->AddHeadString('<meta property="og:title" content="'.$arResult["NAME"].'" />');
			$APPLICATION->AddHeadString('<meta property="og:description" content="'.htmlspecialcharsbx($arResult["PREVIEW_TEXT"]).'" />');
			$APPLICATION->AddHeadString('<meta property="og:url" content="'.(CMain::IsHTTPS() ? "https://" : "http://").SITE_SERVER_NAME.$arResult["DETAIL_PAGE_URL"].'" />');
			$APPLICATION->AddHeadString('<meta property="og:type" content="website" />');
			if(!empty($arResult["IMAGES"][0]["LARGE_IMAGE"]["SRC"])){
				$APPLICATION->AddHeadString('<meta property="og:image" content="'.(CMain::IsHTTPS() ? "https://" : "http://").SITE_SERVER_NAME.$arResult["IMAGES"][0]["LARGE_IMAGE"]["SRC"].'" />');
			}
		}

		//nav chain element
		if ($arParams["ADD_ELEMENT_CHAIN"]){
			$APPLICATION->AddChainItem($elementTitle);
		}

		//last modified
		if ($arParams["SET_LAST_MODIFIED"] && $arResult["TIMESTAMP_X"]){
			Context::getCurrent()->getResponse()->setLastModified(DateTime::createFromUserTime($arResult["TIMESTAMP_X"]));
		}

		//viewed product
		if($arParams["SET_VIEWED_IN_COMPONENT"] == "Y"){

			$_SESSION["VIEWED_ENABLE"] = "Y";

			//viewed params
			$arFields = array(
				"IBLOCK_ID" => $arParams["IBLOCK_ID"],
				"PRODUCT_ID" => $arResult["ID"],
				"MODULE" => "catalog",
				"LID" => SITE_ID
			);

			//add
			CSaleViewedProduct::Add($arFields);

			//refresh
			\Bitrix\Catalog\CatalogViewedProductTable::refresh(
				$arResult["ID"], CSaleBasket::GetBasketUserID(), SITE_ID, $arResult["PARENT_PRODUCT"]["ID"]
			);

		}

		//clear resultCacheKeys
		$this->setResultCacheKeys(array("CANONICAL_PAGE_URL"));

		//include component template
		$this->IncludeComponentTemplate();

	}



?>