<?define("STOP_STATISTICS", true);?>
<?define("NO_AGENT_CHECK", true);?>
<?require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_before.php");?>
<?error_reporting(0);?>
<?if(CModule::IncludeModule("catalog") && CModule::IncludeModule("sale") && CModule::IncludeModule("dw.deluxe")){
	
	if($_REQUEST["act"] == "getFastDelivery"){
		if(!empty($_REQUEST["site_id"]) && !empty(intval($_REQUEST["product_id"]))){

			$loadScript = !empty($_REQUEST["loadScript"]) ? $_REQUEST["loadScript"] : "Y";
			//buffer component html
			ob_start();
			$APPLICATION->IncludeComponent(
				"dresscode:fast.calculate.delivery", 
				".default", 
				array(
					"SITE_ID" => $_REQUEST["site_id"],
					"CALC_ALL_PRODUCTS" => "N",
					"PRODUCT_ID" => intval($_REQUEST["product_id"]),
					"LOAD_SCRIPT" => $loadScript
				),
				false,
				array("HIDE_ICONS" => "Y")
			);
			//save buffer
			$arLastOffer["COMPONENT_HTML"] = ob_get_contents();
			//end buffer
			ob_end_clean();	

			//return data
			echo \Bitrix\Main\Web\Json::encode($arLastOffer);

		}
	}

	elseif($_REQUEST["act"] == "selectSku"){
		if(!empty($_REQUEST["params"]) &&
		   !empty($_REQUEST["iblock_id"]) &&
		   !empty($_REQUEST["prop_id"]) &&
		   !empty($_REQUEST["product_id"]) &&
		   !empty($_REQUEST["level"]) &&
		   !empty($_REQUEST["props"])
		){

			$OPTION_ADD_CART = COption::GetOptionString("catalog", "default_can_buy_zero");
			$OPTION_CURRENCY  = CCurrency::GetBaseCurrency();

			$arResult["PRODUCT_PRICE_ALLOW"] = array();
			$arResult["PRODUCT_PRICE_ALLOW_FILTER"] = array();
			$arPriceCode = array();

			//utf8 convert
			$_REQUEST["price-code"] = !defined("BX_UTF") ? iconv("UTF-8", "windows-1251", $_REQUEST["price-code"]) : $_REQUEST["price-code"];
			
			if(!empty($_REQUEST["price-code"]) && $_REQUEST["price-code"] != "undefined"){
				$arPriceCode = explode("||", $_REQUEST["price-code"]);
				$dbPriceType = CCatalogGroup::GetList(
			        array("SORT" => "ASC"),
			        array("NAME" => $arPriceCode)
			    );
				while ($arPriceType = $dbPriceType->Fetch()){
					if($arPriceType["CAN_BUY"] == "Y")
				    	$arResult["PRODUCT_PRICE_ALLOW"][] = $arPriceType;
				    $arResult["PRODUCT_PRICE_ALLOW_FILTER"][] = $arPriceType["ID"];
				}
			}

			$arTmpFilter = array(
				"ACTIVE" => "Y",
				"IBLOCK_ID" => intval($_REQUEST["iblock_id"]),
				"PROPERTY_".intval($_REQUEST["prop_id"]) => intval($_REQUEST["product_id"])
			);

			// if($OPTION_ADD_CART == N){
			// 	$arTmpFilter[">CATALOG_QUANTITY"] = 0;
			// }

			$arProps = array();
			$arParams =  array();
			$arTmpParams = array();
			$arCastFilter = array();
			$arProperties = array();
			$arPropActive = array();
			$arPropertyTypes = array();
			$arAllProperties = array();
			$arPropCombination = array();
			$arHighloadProperty = array();

			$PROPS = BX_UTF != 1 ? iconv("UTF-8", "windows-1251", $_REQUEST["props"]) : $_REQUEST["props"];
			$PARAMS = BX_UTF != 1 ? iconv("UTF-8", "windows-1251", $_REQUEST["params"]) : $_REQUEST["params"];
			$HIGHLOAD = BX_UTF != 1 ? iconv("UTF-8", "windows-1251", $_REQUEST["highload"]) : $_REQUEST["highload"];

			//normalize property
			$exProps = explode(";", trim($PROPS, ";"));
			$exParams = explode(";", trim($PARAMS, ";"));
			$exHighload = explode(";", trim($HIGHLOAD, ";"));

			if(empty($exProps) || empty($exParams))
				die("error #1 | Empty params or propList _no valid data");

			if(!empty($exHighload)){
				foreach ($exHighload as $ihl => $nextHighLoad) {
					$arHighloadProperty[$nextHighLoad] = "Y";
				}
			}

			foreach ($exProps as $ip => $sProp) {
				$msp = explode(":", $sProp);
				$arProps[$msp[0]][$msp[1]] = "D";
			}

			foreach ($exParams as $ip => $pProp) {
				$msr = explode(":", $pProp);
				$arParams[$msr[0]] = $msr[1];
				$resProp = CIBlockProperty::GetByID($msr[0]);
				if($arNextPropGet = $resProp->GetNext()){
					$arPropertyTypes[$msr[0]] = $arNextPropGet["PROPERTY_TYPE"];
					if(empty($arHighloadProperty[$msr[0]]) && $arNextPropGet["PROPERTY_TYPE"] != "E"){
						$arTmpParams["PROPERTY_".$msr[0]."_VALUE"] = $msr[1];
					}else{
						$arTmpParams["PROPERTY_".$msr[0]] = $msr[1];
					}
				}
			}

			$arFilter = array_merge($arTmpFilter, array_slice($arTmpParams, 0, $_REQUEST["level"]));

			$rsOffer = CIBlockElement::GetList(
				array(),
				$arFilter, false, false,
				array(
					"ID",
					"NAME",
					"IBLOCK_ID"
				)
			);

			while($obOffer = $rsOffer->GetNextElement()){
				$arOfferParams = $obOffer->GetFields();
				$arFilterProp = $obOffer->GetProperties();
				foreach ($arFilterProp as $ifp => $arNextProp) {
					if($arNextProp["PROPERTY_TYPE"] == "L" || $arNextProp["PROPERTY_TYPE"] == "E" && !empty($arNextProp["VALUE"])
						|| $arNextProp["PROPERTY_TYPE"] == "S" && !empty($arNextProp["USER_TYPE_SETTINGS"]["TABLE_NAME"]) && !empty($arNextProp["VALUE"])
					){
						$arProps[$arNextProp["CODE"]][$arNextProp["VALUE"]] = "N";
						$arProperties[$arNextProp["CODE"]] = $arNextProp["VALUE"];
						$arPropCombination[$arOfferParams["ID"]][$arNextProp["CODE"]][$arNextProp["VALUE"]] = "Y";
					}
				}
			}

			if(!empty($arParams)){
				foreach ($arParams as $propCode => $arField) {
					if($arProps[$propCode][$arField] == "N"){
					 	$arProps[$propCode][$arField] = "Y";
					}else{
						if(!empty($arProps[$propCode])){
							foreach ($arProps[$propCode] as $iCode => $upProp) {
								if($upProp == "N"){
									$arProps[$propCode][$iCode] = "Y";
									break(1);
								}
							}
						}
					}
				}
			}

			if(!empty($arProps)){
				$activeIntertion = 0;
				foreach ($arProps as $ip => $arNextProp) {
					foreach ($arNextProp as $inv => $arNextPropValue) {
						if($arNextPropValue == "Y"){
							$arPropActive[$ip] = $inv;
							$arPropActiveIndex[$activeIntertion++] = $inv;
						}
					}
				}
			}

			if(!empty($arProps)){
				$arPrevLevelProp = array();
				$levelIteraion = 0;
				foreach ($arProps as $inp => $arNextProp){ //level each
					if($levelIteraion > 0){
						foreach ($arNextProp as $inpp => $arNextPropEach) {
							if($arNextPropEach == "N" && !empty($arPrevLevelProp)){
								$seachSuccess = false;
								foreach ($arPropCombination as $inc => $arNextCombination) {
									if($arNextCombination[$inp][$inpp] == "Y" && $arNextCombination[$arPrevLevelProp["INDEX"]][$arPrevLevelProp["VALUE"]] == "Y"){
										$seachSuccess = true;
										break(1);
									}
								}
								if($seachSuccess == false){
									$arProps[$inp][$inpp] = "D";
								}
							}
						}
					}$levelIteraion++;
					$arPrevLevelProp = array("INDEX" => $inp, "VALUE" => $arPropActive[$inp]);
				}
			}

			$arLastFilter = array(
				"ACTIVE" => "Y",
				"IBLOCK_ID" => intval($_REQUEST["iblock_id"]),
				"PROPERTY_".intval($_REQUEST["prop_id"]) => intval($_REQUEST["product_id"])
			);

			// if($OPTION_ADD_CART == "N" ){
			// 	$arTmpFilter[">CATALOG_QUANTITY"] = 0;
			// }

			foreach ($arPropActive as $icp => $arNextProp) {
				if(empty($arHighloadProperty[$icp]) && $arPropertyTypes[$icp] != "E"){
					$arLastFilter["PROPERTY_".$icp."_VALUE"] = $arNextProp;
				}else{
					$arLastFilter["PROPERTY_".$icp] = $arNextProp;
				}
			}

			$arSkuPriceCodes = array();

			if(!empty($arResult["PRODUCT_PRICE_ALLOW"])){
				$arSkuPriceCodes["PRODUCT_PRICE_ALLOW"] = $arResult["PRODUCT_PRICE_ALLOW"];
			}

			if(!empty($arPriceCode)){
				$arSkuPriceCodes["PARAMS_PRICE_CODE"] = $arPriceCode;
			}

			$arLastOffer = getLastOffer($arLastFilter, $arProps, $_REQUEST["product_id"], $OPTION_CURRENCY, $arSkuPriceCodes);
			$arLastOffer["PRODUCT"]["CAN_BUY"] = $arLastOffer["PRODUCT"]["CATALOG_AVAILABLE"];

			//clear ''
			$arLastOffer["PRODUCT"]["NAME"] = str_replace("'", "", $arLastOffer["PRODUCT"]["NAME"]);
			$arLastOffer["PRODUCT"]["~NAME"] = str_replace("'", "", $arLastOffer["PRODUCT"]["~NAME"]);

			if(!empty($arLastOffer["PRODUCT"]["CATALOG_MEASURE"])){
				//коэффициент еденица измерения
				$rsMeasure = CCatalogMeasure::getList(
					array(),
					array(
						"ID" => $arLastOffer["PRODUCT"]["CATALOG_MEASURE"]
					),
					false,
					false,
					false
				);

				while($arNextMeasure = $rsMeasure->Fetch()) {
					$arLastOffer["PRODUCT"]["MEASURE"] = $arNextMeasure;
				}
			}

			ob_start();
			$APPLICATION->IncludeComponent(
				"dresscode:catalog.properties.list", 
				"no-group", 
				array(
					"PRODUCT_ID" => $arLastOffer["PRODUCT"]["ID"],
					"COUNT_PROPERTIES" => 7
				),
				false
			);
			$arLastOffer["PRODUCT"]["RESULT_PROPERTIES_NO_GROUP"] = ob_get_contents();
			ob_end_clean();

			ob_start();
			$APPLICATION->IncludeComponent(
				"dresscode:catalog.properties.list", 
				"group", 
			array(
				"PRODUCT_ID" => $arLastOffer["PRODUCT"]["ID"]
			),
			false
			);
			$arLastOffer["PRODUCT"]["RESULT_PROPERTIES_GROUP"] = ob_get_contents();
			ob_end_clean();

			//stores component
			if(!empty($_REQUEST["stores_params"])){

				$arStoresParams = \Bitrix\Main\Web\Json::decode($_REQUEST["stores_params"]);
				$arStoresParams["ELEMENT_ID"] = intval($_REQUEST["product_id"]);
				$arStoresParams["OFFER_ID"] = intval($arLastOffer["PRODUCT"]["ID"]);
				ob_start();
				$APPLICATION->IncludeComponent(
					"bitrix:catalog.store.amount", 
					".default", 
					$arStoresParams,
					false
				);
				$arLastOffer["PRODUCT"]["STORES_COMPONENT"] = ob_get_contents();
				ob_end_clean();
			}

			//price count
			$arPriceFilter = array("PRODUCT_ID" => $arLastOffer["PRODUCT"]["ID"], "CAN_ACCESS" => "Y");
			if(!empty($arResult["PRODUCT_PRICE_ALLOW_FILTER"])){
				$arPriceFilter["CATALOG_GROUP_ID"] = $arResult["PRODUCT_PRICE_ALLOW_FILTER"];
			}
			$dbPrice = CPrice::GetList(
		        array(),
		        $arPriceFilter,
		        false,
		        false,
		        array("ID")
		    );
			$arLastOffer["PRODUCT"]["COUNT_PRICES"] = $dbPrice->SelectedRowsCount();
			
			//Информация о складах
			$rsStore = CCatalogStoreProduct::GetList(array(), array("PRODUCT_ID" => $arLastOffer["PRODUCT"]["ID"]), false, false, array("ID", "AMOUNT")); 
			while($arNextStore = $rsStore->GetNext()){
				$arLastOffer["PRODUCT"]["STORES"][] = $arNextStore;
			}
			
			$arLastOffer["PRODUCT"]["STORES_COUNT"] = count($arLastOffer["PRODUCT"]["STORES"]);

			if(!empty($arProps)){
				echo \Bitrix\Main\Web\Json::encode(
					array(
						array("PRODUCT" => $arLastOffer["PRODUCT"]),
						array("PROPERTIES" => $arLastOffer["PROPERTIES"])
					)
				);
			}

		}
	}elseif($_REQUEST["act"] == "getOfferByID" && !empty($_REQUEST["id"])){
		$rsOffer = CIBlockElement::GetList(
			array(),
			array("ID" => intval($_REQUEST["id"])), false, false,
			array(
				"ID",
				"NAME",
				"IBLOCK_ID"
			)
		);

		while($obOffer = $rsOffer->GetNextElement()){
			$arFilterProp = $obOffer->GetProperties();
			foreach ($arFilterProp as $ifp => $arNextProp) {
				if($arNextProp["PROPERTY_TYPE"] == "L" || $arNextProp["PROPERTY_TYPE"] == "E" && !empty($arNextProp["VALUE"]) || $arNextProp["PROPERTY_TYPE"] == "S" && !empty($arNextProp["USER_TYPE_SETTINGS"]["TABLE_NAME"])){
					$arResultProperties[$arNextProp["CODE"]] = $arNextProp["VALUE"];
				}
			}
		}
		if(!empty($arResultProperties)){
			echo \Bitrix\Main\Web\Json::encode(array($arResultProperties));
		}
	}
}

function picture_separate_array_push($pictureID, $arPushImage = array()){
	if(!empty($pictureID)){

    	//vars
    	$arWaterMark = array();
    	
    	//get settings
		$arTemplateSettings = DwSettings::getInstance()->getCurrentSettings();

		//watermark options
		if(!empty($arTemplateSettings["TEMPLATE_USE_AUTO_WATERMARK"]) && $arTemplateSettings["TEMPLATE_USE_AUTO_WATERMARK"] == "Y"){
	    	$arWaterMark = Array(
	            array(
	                "alpha_level" => $arTemplateSettings["TEMPLATE_WATERMARK_ALPHA_LEVEL"],
	                "coefficient" => $arTemplateSettings["TEMPLATE_WATERMARK_COEFFICIENT"],
	                "position" => $arTemplateSettings["TEMPLATE_WATERMARK_POSITION"],
	                "file" => $arTemplateSettings["TEMPLATE_WATERMARK_PICTURE"],
					"color" => $arTemplateSettings["TEMPLATE_WATERMARK_COLOR"],
	                "type" => $arTemplateSettings["TEMPLATE_WATERMARK_TYPE"],
	                "size" => $arTemplateSettings["TEMPLATE_WATERMARK_SIZE"],
	                "fill" => $arTemplateSettings["TEMPLATE_WATERMARK_FILL"],
					"font" => $arTemplateSettings["TEMPLATE_WATERMARK_FONT"],
					"text" => $arTemplateSettings["TEMPLATE_WATERMARK_TEXT"],
	                "name" => "watermark",
	            )
	        );
		}

		$arPushImage = array();
		$arPushImage["SMALL_IMAGE"] = array_change_key_case(CFile::ResizeImageGet($pictureID, array("width" => 50, "height" => 50), BX_RESIZE_IMAGE_PROPORTIONAL, false), CASE_UPPER);
		$arPushImage["MEDIUM_IMAGE"] = array_change_key_case(CFile::ResizeImageGet($pictureID, array("width" => 500, "height" => 500), BX_RESIZE_IMAGE_PROPORTIONAL, false, $arWaterMark), CASE_UPPER);
		$arPushImage["LARGE_IMAGE"] = array_change_key_case(CFile::ResizeImageGet($pictureID, array("width" => 1200, "height" => 1200), BX_RESIZE_IMAGE_PROPORTIONAL, false, $arWaterMark), CASE_UPPER);
		return $arPushImage;
	}else{
		return false;
	}
}

function getLastOffer($arLastFilter, $arProps, $productID, $opCurrency, $arPrices = array()){
	$rsLastOffer = CIBlockElement::GetList(
		array(),
		$arLastFilter, false, false,
		array(
			"ID",
			"NAME",
			"IBLOCK_ID",
			"DETAIL_PICTURE",
			"DETAIL_PAGE_URL",
			"PREVIEW_TEXT",
			"DETAIL_TEXT",
			"CATALOG_QUANTITY",
			"CATALOG_AVAILABLE",
			"CATALOG_QUANTITY_TRACE",
			"CATALOG_CAN_BUY_ZERO"
		)
	);
	if(!$rsLastOffer->SelectedRowsCount()){
		$st = array_pop($arLastFilter);
		$mt = array_pop($arProps);
		return getLastOffer($arLastFilter, $arProps, $productID, $opCurrency, $arPrices);
	}else{
		if($obReturnOffer = $rsLastOffer->GetNextElement()){
			
			$productFilelds = $obReturnOffer->GetFields();
			$productProperties = $obReturnOffer->GetProperties();
			$productFilelds["IMAGES"] = array();
			$rsProductSelect = array("ID", "IBLOCK_ID", "DETAIL_PICTURE");

			if(!empty($productFilelds["DETAIL_PICTURE"])){
				$productFilelds["IMAGES"][] = picture_separate_array_push($productFilelds["DETAIL_PICTURE"]);
			}

			if(!empty($productProperties["MORE_PHOTO"]["VALUE"])){
				foreach ($productProperties["MORE_PHOTO"]["VALUE"] as $irp => $nextPictureID) {
					$productFilelds["IMAGES"][] = picture_separate_array_push($nextPictureID);
				}				
			}

			if(empty($productFilelds["DETAIL_PICTURE"]) || empty($productProperties["MORE_PHOTO"]["VALUE"]) || empty($productFilelds["PROPERTIES"]["BONUS"]["VALUE"])){
				if($rsProduct = CIBlockElement::GetList(array(), array("ID" => $productID), false, false, $rsProductSelect)->GetNextElement()){
					
					$rsProductFields = $rsProduct->GetFields();
					$rsProductProperties = $rsProduct->GetProperties(array("sort" => "asc", "name" => "asc"), array("EMPTY" => "N"));

					//bonus
					if(empty($productProperties["BONUS"]["VALUE"])){
						if(!empty($rsProductProperties["BONUS"]["VALUE"])){
							$productProperties["BONUS"] = $rsProductProperties["BONUS"];
						}
					}

					if(!empty($rsProductFields["DETAIL_PICTURE"]) || !empty($rsProductProperties["MORE_PHOTO"]["VALUE"])){
						if(!empty($rsProductFields["DETAIL_PICTURE"]) && empty($productFilelds["DETAIL_PICTURE"])){
							array_unshift($productFilelds["IMAGES"], picture_separate_array_push($rsProductFields["DETAIL_PICTURE"]));
						}
						if(!empty($rsProductProperties["MORE_PHOTO"]["VALUE"]) && empty($productProperties["MORE_PHOTO"]["VALUE"])){
							foreach ($rsProductProperties["MORE_PHOTO"]["VALUE"] as $irp => $nextPictureID) {
								if(!empty($nextPictureID)){
									$productFilelds["IMAGES"][] = picture_separate_array_push($nextPictureID);
								}
							}
						}
					}else{
						if(empty($productFilelds["IMAGES"])){
							$productFilelds["IMAGES"][0]["SMALL_IMAGE"] = array("SRC" => SITE_TEMPLATE_PATH."/images/empty.png");  
							$productFilelds["IMAGES"][0]["MEDIUM_IMAGE"] = array("SRC" => SITE_TEMPLATE_PATH."/images/empty.png");   
							$productFilelds["IMAGES"][0]["LARGE_IMAGE"] = array("SRC" => SITE_TEMPLATE_PATH."/images/empty.png");
						} 
					}
				}
			}

			//get price info
			$productFilelds["EXTRA_SETTINGS"] = array();
			$productFilelds["EXTRA_SETTINGS"]["PRODUCT_PRICE_ALLOW"] = array();
			$productFilelds["EXTRA_SETTINGS"]["PRODUCT_PRICE_ALLOW_FILTER"] = array();

			if(!empty($arPrices["PARAMS_PRICE_CODE"])){

				//get available prices code & id
				$arPricesInfo = DwPrices::getPriceInfo($arPrices["PARAMS_PRICE_CODE"], $productFilelds["IBLOCK_ID"]);
				if(!empty($arPricesInfo)){
			    	$productFilelds["EXTRA_SETTINGS"]["PRODUCT_PRICE_ALLOW"] = $arPricesInfo["ALLOW"];
				    $productFilelds["EXTRA_SETTINGS"]["PRODUCT_PRICE_ALLOW_FILTER"] = $arPriceType["ALLOW_FILTER"];
				}

			}

			$productFilelds["PRICE"] = DwPrices::getPricesByProductId($productFilelds["ID"], $productFilelds["EXTRA_SETTINGS"]["PRODUCT_PRICE_ALLOW"], $productFilelds["EXTRA_SETTINGS"]["PRODUCT_PRICE_ALLOW_FILTER"], $arPrices["PARAMS_PRICE_CODE"], $productFilelds["IBLOCK_ID"], $opCurrency);
			$productFilelds["PRICE"]["DISCOUNT_PRICE"] = CCurrencyLang::CurrencyFormat($productFilelds["PRICE"]["DISCOUNT_PRICE"], $opCurrency, true);
			$productFilelds["PRICE"]["RESULT_PRICE"]["BASE_PRICE"] = CCurrencyLang::CurrencyFormat($productFilelds["PRICE"]["RESULT_PRICE"]["BASE_PRICE"], $opCurrency, true);
			$productFilelds["PRICE"]["DISCOUNT_PRINT"] = CCurrencyLang::CurrencyFormat($productFilelds["PRICE"]["RESULT_PRICE"]["DISCOUNT"], $opCurrency, true);

			if(!empty($productFilelds["PRICE"]["EXTENDED_PRICES"])){
				$productFilelds["PRICE"]["EXTENDED_PRICES_JSON_DATA"] = \Bitrix\Main\Web\Json::encode($productFilelds["PRICE"]["EXTENDED_PRICES"]);
			}

			if(!empty($productFilelds["PRICE"]["DISCOUNT"])){
				unset($productFilelds["PRICE"]["DISCOUNT"]);
			}
			
			if(!empty($productFilelds["PRICE"]["DISCOUNT_LIST"])){
				unset($productFilelds["PRICE"]["DISCOUNT_LIST"]);
			}

			//коэффициент еденица измерения
			$productFilelds["BASKET_STEP"] = 1;
			$rsMeasureRatio = CCatalogMeasureRatio::getList(
				array(),
				array("PRODUCT_ID" => intval($productFilelds["ID"])),
				false,
				false,
				array()
			);

			if($arProductMeasureRatio = $rsMeasureRatio->Fetch()){
				if(!empty($arProductMeasureRatio["RATIO"])){
					$productFilelds["BASKET_STEP"] = $arProductMeasureRatio["RATIO"];
				}
			}

			return array(
				"PRODUCT" => array_merge(
					$productFilelds, array(
						"PROPERTIES" => $productProperties
					)
				),
				"PROPERTIES" => $arProps
			);
		}
	}
}

function priceFormat($data, $str = ""){
	$price = explode(".", $data);
	$strLen = strlen($price[0]);
	for ($i = $strLen; $i > 0 ; $i--) {
		$str .=	(!($i%3) ? " " : "").$price[0][$strLen - $i];
	}
	return $str.($price[1] > 0 ? ".".$price[1] : "");
}

function jsonEn($data, $multi = false){
	if(!$multi){
		foreach ($data as $index => $arValue) {
			$arJsn[] = '"'.$index.'" : "'.addslashes($arValue).'"';
		}
		return  "{".implode($arJsn, ",")."}";
	}
}

function jsonMultiEn($data){
	if(is_array($data)){
		if(count($data) > 0){
			$arJsn = "[".implode(getJnLevel($data, 0), ",")."]";
		}else{
			$arJsn = implode(getJnLevel($data), ",");
		}
	}
	return str_replace(array("\t", "\r", "\n"), "", $arJsn);
}

function getJnLevel($data, $level = 1, $arJsn = array()){
	foreach ($data as $i => $arNext) {
		if(!is_array($arNext)){
			$arJsn[] = '"'.$i.'":"'.addslashes($arNext).'"';
		}else{
			if($level === 0){
				$arJsn[] = "{".implode(getJnLevel($arNext), ",")."}";
			}else{
				$arJsn[] = '"'.$i.'":{'.implode(getJnLevel($arNext),",").'}';
			}
		}
	}
	return $arJsn;
}

?>