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

	//set cache default time
	if (!isset($arParams["CACHE_TIME"])){
		$arParams["CACHE_TIME"] = 1285912;
	}

	$arParams["DISPLAY_PICTURE_COLUMN"] = (empty($arParams["DISPLAY_PICTURE_COLUMN"]) ? "Y" : $arParams["DISPLAY_PICTURE_COLUMN"]);

	//pagination params
	$arParams["PAGER_NAV_HEADING"] = (empty($arParams["PAGER_NAV_HEADING"]) ? GetMessage("PAGINATION_NAV_HEADING") : $arParams["PAGER_NAV_HEADING"]);
	$arParams["NAV_COUNT_ELEMENTS"] = (empty($arParams["NAV_COUNT_ELEMENTS"]) ? 10 : $arParams["NAV_COUNT_ELEMENTS"]);
	$arParams["PAGER_SHOW_ALWAYS"] = (empty($arParams["PAGER_SHOW_ALWAYS"]) ? "N" : $arParams["PAGER_SHOW_ALWAYS"]);
	$arParams["PAGER_TEMPLATE"] = (empty($arParams["PAGER_TEMPLATE"]) ? ".default" : $arParams["PAGER_TEMPLATE"]);
	$arParams["PAGER_NUM"] = (empty($arParams["PAGER_NUM"]) ? 1 : $arParams["PAGER_NUM"]);

	//set default pictures params
	$arParams["PICTURE_WIDTH"] = (empty($arParams["PICTURE_WIDTH"]) ? 100 : $arParams["PICTURE_WIDTH"]);
	$arParams["PICTURE_HEIGHT"] = (empty($arParams["PICTURE_HEIGHT"]) ? 100 : $arParams["PICTURE_HEIGHT"]);
	$arParams["PICTURE_QUALITY"] = (empty($arParams["PICTURE_QUALITY"]) ? 100 : $arParams["PICTURE_QUALITY"]);

	//clear not used params
	if($arParams["CONVERT_CURRENCY"] != "Y"){
		if(isset($arParams["CURRENCY_ID"])){
			unset($arParams["CURRENCY_ID"]);
		}
	}

	//create cache id
	$cacheID = array(
		"CONVERT_CURRENCY" => $arParams["CONVERT_CURRENCY"],
		"PRODUCT_ID" =>	intval($arParams["PRODUCT_ID"]),
		"CURRENCY_ID" => $arParams["CURRENCY_ID"],
		"PAGER_NUM" => $arParams["PAGER_NUM"],
		"USER_GROUPS" => $USER->GetGroups(),
		"SITE_ID" => SITE_ID
	);

	if($this->StartResultCache($arParams["CACHE_TIME"], serialize($cacheID))){

		//check include modules
		if(
			   !\Bitrix\Main\Loader::includeModule("dw.deluxe")
			|| !\Bitrix\Main\Loader::includeModule("iblock")
			|| !\Bitrix\Main\Loader::includeModule('highloadblock')
			|| !\Bitrix\Main\Loader::includeModule("catalog")
			|| !\Bitrix\Main\Loader::includeModule("sale")
		){

			$this->AbortResultCache();
			ShowError("modules not installed!");
			return 0;

		}

		//arResult arrays
		$arResult["ITEMS"] = array();

		//currency params
		$opCurrency = ($arParams["CONVERT_CURRENCY"] == "Y" && !empty($arParams["CURRENCY_ID"])) ? $arParams["CURRENCY_ID"] : NULL;

		//sku properties constraint param
    	$skuSortParams = 100;

    	//property names for table
    	$arSkuPropNames = array();

		//check exist offers for product
		$arContainOffers = CCatalogSKU::getExistOffers($arParams["PRODUCT_ID"], $arParams["IBLOCK_ID"]);

		//if exist offers
		if(!empty($arContainOffers[$arParams["PRODUCT_ID"]])){

			//get parent product fields
			$rsProduct = CIBlockElement::GetList(
			   array(), 
			   array(
			   "IBLOCK_ID" => $arParams["IBLOCK_ID"],
			   "ID" => $arParams["PRODUCT_ID"]
			   ),
			   false, 
			   false,
			   array("ID", "IBLOCK_ID", "NAME", "DETAIL_PICTURE")
			);

			if($arParentProduct = $rsProduct->GetNext()){
				
				//get picture
				if(!empty($arParentProduct["DETAIL_PICTURE"])){
					$arParentProduct["PICTURE"] = CFile::ResizeImageGet($arParentProduct["DETAIL_PICTURE"], array("width" => $arParams["PICTURE_WIDTH"], "height" => $arParams["PICTURE_HEIGHT"]), BX_RESIZE_IMAGE_PROPORTIONAL, false, false, false, $arParams["PICTURE_QUALITY"]);
				}

				//storage
				$arResult["PARENT_PRODUCT"] = $arParentProduct;

			}

			//get sku iblock info
			$arOffersSkuInfo = CCatalogSKU::GetInfoByProductIBlock($arParams["IBLOCK_ID"]);

			//get price info
			$arResult["PRODUCT_PRICE_ALLOW"] = array();
			$arResult["PRODUCT_PRICE_ALLOW_FILTER"] = array();

			if(!empty($arParams["PRODUCT_PRICE_CODE"])){

				//get available prices code & id
				$arPricesInfo = DwPrices::getPriceInfo($arParams["PRODUCT_PRICE_CODE"], $arOffersSkuInfo["IBLOCK_ID"]);
				if(!empty($arPricesInfo)){
			    	$arResult["PRODUCT_PRICE_ALLOW"] = $arPricesInfo["ALLOW"];
				    $arResult["PRODUCT_PRICE_ALLOW_FILTER"] = $arPriceType["ALLOW_FILTER"];
				}

			}

			// sku offers sort
			$arSkuOffersSort = array(
				"SORT" => "ASC",
				"NAME" => "ASC"
			);

			// sku offers filter
			$arSkuOffersFilter = array(
				"PROPERTY_".$arOffersSkuInfo["SKU_PROPERTY_ID"] => $arParams["PRODUCT_ID"],
				"IBLOCK_ID" => $arOffersSkuInfo["IBLOCK_ID"],
				"INCLUDE_SUBSECTIONS" => "N",
				"ACTIVE" => "Y"
			);

			//if hide not available
			if($arParams["HIDE_NOT_AVAILABLE"] == "Y"){
				$arSkuOffersFilter["CATALOG_AVAILABLE"] = "Y";
			}

			//sku offers select fileds
			$arSkuOffersSelect = array(
				"ID",
				"IBLOCK_ID",
				"NAME",
				"CODE",
				"SORT",
				"DATE_CREATE",
				"DATE_MODIFY",
				"TIMESTAMP_X",
				"DATE_ACTIVE_TO",
				"DETAIL_PAGE_URL",
				"DETAIL_PICTURE",
				"PREVIEW_PICTURE",
				"DATE_ACTIVE_FROM",
				"CATALOG_QUANTITY",
				"CATALOG_MEASURE",
				"CATALOG_AVAILABLE",
				"CATALOG_SUBSCRIBE",
				"CATALOG_QUANTITY_TRACE",
				"CATALOG_CAN_BUY_ZERO",
				"CANONICAL_PAGE_URL"
			);

			//get sku offers
			$rsSkuOffers = CIBlockElement::GetList($arSkuOffersSort, $arSkuOffersFilter, false, false, $arSkuOffersSelect);

			//get all elements count
			$arResult["ROWS_ALL_COUNT"] = $rsSkuOffers->SelectedRowsCount();

			//calc ajax pager
			$arResult["PAGER_ENABLED"] = (($arResult["ROWS_ALL_COUNT"] - ($arParams["PAGER_NUM"] * $arParams["NAV_COUNT_ELEMENTS"])) > 0);

			//start nav ($arParams["PAGER_NUM"])
			$rsSkuOffers->NavStart($arParams["NAV_COUNT_ELEMENTS"], false, $arParams["PAGER_NUM"]);

			//get items
			while ($arNextSkuOffer = $rsSkuOffers->GetNextElement()){

				//get fileds and properties
				$arSkuFieldsMx = $arNextSkuOffer->GetFields();
				$arSkuPropertiesMx = $arNextSkuOffer->GetProperties(array("SORT" => "ASC"), array("ACTIVE" => "Y", "EMPTY" => "N"));

				//propery filtred by soft ($skuSortParams)
				$arSkuPropFiltred = array();

				//filter properties by soft ($skuSortParams)
				foreach ($arSkuPropertiesMx as $ixt => $arNextSkuProperty){
					if($arNextSkuProperty["SORT"] <= $skuSortParams){
						$arSkuPropNames[$arNextSkuProperty["NAME"]] = $arNextSkuProperty["NAME"];
						$arSkuPropFiltred[] = CIBlockFormatProperties::GetDisplayValue($arSkuFieldsMx, $arNextSkuProperty, "catalog_out");
					}
				}

				//get prices
				$arSkuFieldsMx["PRICE"] = DwPrices::getPricesByProductId($arSkuFieldsMx["ID"], $arResult["PRODUCT_PRICE_ALLOW"], $arResult["PRODUCT_PRICE_ALLOW_FILTER"], $arParams["PRODUCT_PRICE_CODE"], $arOffersSkuInfo["IBLOCK_ID"], $opCurrency);

				//if > 0 display [?] for more price table
				$arSkuFieldsMx["EXTRA_SETTINGS"]["COUNT_PRICES"] = $arSkuFieldsMx["PRICE"]["COUNT_PRICES"];

				//set main picture
				if(!empty($arSkuFieldsMx["DETAIL_PICTURE"])){
					$arSkuFieldsMx["PICTURE"] = CFile::ResizeImageGet($arSkuFieldsMx["DETAIL_PICTURE"], array("width" => $arParams["PICTURE_WIDTH"], "height" => $arParams["PICTURE_HEIGHT"]), BX_RESIZE_IMAGE_PROPORTIONAL, false, false, false, $arParams["PICTURE_QUALITY"]);
				}

				else{

					//get picture prom parent product
					if(!empty($arResult["PARENT_PRODUCT"]["PICTURE"])){
						$arSkuFieldsMx["PICTURE"] = $arResult["PARENT_PRODUCT"]["PICTURE"];
					}

					else{
						$arSkuFieldsMx["PICTURE"]["src"] = SITE_TEMPLATE_PATH."/images/empty.png";
					}
					
				}

				//stores info
				$arSkuFieldsMx["EXTRA_SETTINGS"]["STORES_MAX_QUANTITY"] = 0;
				$rsStore = CCatalogStoreProduct::GetList(array(), array("PRODUCT_ID" => $arSkuFieldsMx["ID"]), false, false, array("ID", "AMOUNT"));
				while($arNextStore = $rsStore->GetNext()){
					$arSkuFieldsMx["EXTRA_SETTINGS"]["STORES"][] = $arNextStore;
					if($arNextStore["AMOUNT"] > $arSkuFieldsMx["EXTRA_SETTINGS"]["STORES_MAX_QUANTITY"]){
						$arSkuFieldsMx["EXTRA_SETTINGS"]["STORES_MAX_QUANTITY"] = $arNextStore["AMOUNT"];
					}
				}

				//set base currency
				$arSkuFieldsMx["EXTRA_SETTINGS"]["CURRENCY"] = empty($opCurrency) ? $arSkuFieldsMx["PRICE"]["RESULT_PRICE"]["CURRENCY"] : $opCurrency;

				//get measures
				$rsMeasure = CCatalogMeasure::getList(
					array(),
					array(
						"ID" => $arSkuFieldsMx["CATALOG_MEASURE"]
					),
					false,
					false,
					false
				);

				while($arNextMeasure = $rsMeasure->Fetch()){
					$arSkuFieldsMx["EXTRA_SETTINGS"]["MEASURES"][$arNextMeasure["ID"]] = $arNextMeasure;
				}

				//get measure ratio for product

				//set default ratio
				$arSkuFieldsMx["EXTRA_SETTINGS"]["BASKET_STEP"] = 1;

				//get ratio from BD
				$rsMeasureRatio = CCatalogMeasureRatio::getList(
					array(),
					array("PRODUCT_ID" => intval($arSkuFieldsMx["ID"])),
					false,
					false,
					array()
				);

				//fetch ratio
				if($arProductMeasureRatio = $rsMeasureRatio->Fetch()){
					if(!empty($arProductMeasureRatio["RATIO"])){
						$arSkuFieldsMx["EXTRA_SETTINGS"]["BASKET_STEP"] = $arProductMeasureRatio["RATIO"];
					}
				}
				
				//edit buttons
				$arButtons = CIBlock::GetPanelButtons(
					$arSkuFieldsMx["IBLOCK_ID"],
					$arSkuFieldsMx["ID"],
					false,
					array("SECTION_BUTTONS" => true,
						  "SESSID" => true,
						  "CATALOG" => true
					)
				);

				//get edit link
				$arSkuFieldsMx["EDIT_LINK"] = $arButtons["edit"]["edit_element"]["ACTION_URL"];
				$arSkuFieldsMx["DELETE_LINK"] = $arButtons["edit"]["delete_element"]["ACTION_URL"];

				//save
				$arResult["ITEMS"][$arSkuFieldsMx["ID"]] = $arSkuFieldsMx;
				$arResult["ITEMS"][$arSkuFieldsMx["ID"]]["PROPERTIES"] = $arSkuPropertiesMx;
				$arResult["ITEMS"][$arSkuFieldsMx["ID"]]["PROPERTIES_FILTRED"] = $arSkuPropFiltred;
				
			}

			// uri params
			$uri = new \Bitrix\Main\Web\Uri($this->request->getRequestUri());
			$uri->deleteParams(
				array_merge(
					array(
						"PAGEN_".$rsSkuOffers->NavNum,
						"SIZEN_".$rsSkuOffers->NavNum,
						"SHOWALL_".$rsSkuOffers->NavNum,
						"PHPSESSID",
						"clear_cache",
						"bitrix_include_areas"
					),
					\Bitrix\Main\HttpRequest::getSystemParameters()
				)
			);

			//set base link
			$navComponentParameters["BASE_LINK"] = $uri->getUri();

			//save pagination string
			$arResult["NAV_STRING"] = $rsSkuOffers->GetPageNavStringEx(
				$navComponentObject,
				$arParams["PAGER_NAV_HEADING"],
				$arParams["PAGER_TEMPLATE"],
				$arParams["PAGER_SHOW_ALWAYS"],
				$this,
				$navComponentParameters
			);

			//save property names
			if(!empty($arSkuPropNames)){
				$arResult["PROPERTY_NAMES"] = $arSkuPropNames;
			}

		}
		
		$this->setResultCacheKeys(array());
		$this->IncludeComponentTemplate();

	}

?>