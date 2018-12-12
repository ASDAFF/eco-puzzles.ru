<?

use Bitrix\Highloadblock as HL;
use Bitrix\Main\Entity;
use Bitrix\Main;

class DwSkuOffers {

	//get sku properties
    public static function getSkuPropertiesFromIblock($arSkuIblockInfo){

    	global $USER;
    	global $DB;

    	//$arSkuIblockInfo
    	//$arSkuIblockInfo["IBLOCK_ID"]

    	//Print properties less than sorting ($skuSortParams)
    	$skuSortParams = 100;
    	$skuPictureParamsWidth = 30;
    	$skuPictureParamsHeight = 30;
    	$skuPictureParamsQuality = 100;

    	$arResult = array();

		if(!empty($arSkuIblockInfo)){

			//check sqlstat
			$dbg = $DB->ShowSqlStat;

			//get user groups for create cache
			$arSkuIblockInfo["USER_GROUP"] = $USER->GetUserGroupString();

			//time to life cache
			$cacheTime = 21285912;

			//create cache id
			$cacheID = serialize($arSkuIblockInfo);

			//cache dir ( / - all)
			$cacheDir = "/";

			//extra settings from cache
			$obExtraCache = new CPHPCache();

			//cache sql
			if($dbg){
				$DB->ShowSqlStat = false;
			}

			if($obExtraCache->InitCache($cacheTime, $cacheID, $cacheDir)){
			   $arResult = $obExtraCache->GetVars();
			}

			elseif($obExtraCache->StartDataCache()){

				// properties sort
				$arPropertiesSort = array(
					"SORT" => "ASC",
					"NAME" => "ASC"
				);

				// properties filter
				$arPropertiesFilter = array(
					"IBLOCK_ID" => $arSkuIblockInfo["IBLOCK_ID"],
					"ACTIVE" => "Y"
				);

				//get properties from sku iblock id
				$skuProperties = CIBlockProperty::GetList($arPropertiesSort, $arPropertiesFilter);

				//get properties
				while ($arNextProperty = $skuProperties->GetNext()){

					//if property type == list & sort <= $skuSortParams
					if($arNextProperty["SORT"] <= $skuSortParams && $arNextProperty["PROPERTY_TYPE"] == "L"){

						$propValues = array();
						$arNextProperty["HIGHLOAD"] = "N";

						// property value sort
						$arPropertyValueSort = array(
							"SORT" => "ASC",
							"DEF" => "DESC"
						);

						// property value filter
						$arPropertyValueFilter = array(
							"IBLOCK_ID" => $arSkuIblockInfo["IBLOCK_ID"],
							"CODE" => $arNextProperty["CODE"]
						);

						//get property list values
						$rsPropertyValues = CIBlockPropertyEnum::GetList($arPropertyValueSort, $arPropertyValueFilter);
						while($arNextPropertyValue = $rsPropertyValues->GetNext()){

							//write to array
							$propValues[$arNextPropertyValue["VALUE"]] = array(
								"VALUE"  => $arNextPropertyValue["VALUE"],
								"DISPLAY_VALUE"  => $arNextPropertyValue["VALUE"],
								"SELECTED"  => "N",
								"DISABLED"  => "N",
								"HIGHLOAD" => "N"
							);

						}

						//write values
						$arNextProperty["TYPE"] = "L";
						$arResult[$arNextProperty["CODE"]] = array_merge(
							$arNextProperty, array("VALUES" => $propValues)
						);

					}

					//if property type == highload & sort <= $skuSortParams
					elseif($arNextProperty["SORT"] <= $skuSortParams && $arNextProperty["PROPERTY_TYPE"] == "S" && !empty($arNextProperty["USER_TYPE_SETTINGS"]["TABLE_NAME"])){

						$propValues = array();
						$arNextProperty["HIGHLOAD"] = "Y";

						//get highload values
						$hlblock = \Bitrix\Highloadblock\HighloadBlockTable::getList(array("filter" => array("TABLE_NAME" => $arNextProperty["USER_TYPE_SETTINGS"]["TABLE_NAME"])))->fetch();

					    if(!empty($hlblock)){

							$hlblock_requests = HL\HighloadBlockTable::getById($hlblock["ID"])->fetch();
							$entity_requests = HL\HighloadBlockTable::compileEntity($hlblock_requests);
							$entity_requests_data_class = $entity_requests->getDataClass();

							$main_query_requests = new Entity\Query($entity_requests_data_class);
							$main_query_requests->setSelect(array("*"));
							$result_requests = $main_query_requests->exec();
							$result_requests = new CDBResult($result_requests);

							while ($row_requests = $result_requests->Fetch()){

								if(!empty($row_requests["UF_FILE"])){
									$row_requests["UF_FILE"] = CFile::ResizeImageGet($row_requests["UF_FILE"], array("width" => $skuPictureParamsWidth, "height" => $skuPictureParamsHeight), BX_RESIZE_IMAGE_PROPORTIONAL, false, false, false, $skuPictureParamsQuality);
									$hasPicture = true;
								}

								$propValues[$row_requests["UF_XML_ID"]] = array(
									"VALUE" => $row_requests["UF_XML_ID"],
									"DISPLAY_VALUE" => $row_requests["UF_NAME"],
									"SELECTED" => "N",
									"DISABLED" => "N",
									"UF_XML_ID" => $row_requests["UF_XML_ID"],
									"IMAGE" => $row_requests["UF_FILE"],
									"NAME" => $row_requests["UF_NAME"],
									"HIGHLOAD" => "Y"
								);

							}

							//write values
							$arNextProperty["HIGHLOAD"] = "Y";
							$arNextProperty["TYPE"] = "H";
							$arResult[$arNextProperty["CODE"]] = array_merge(
								$arNextProperty, array("VALUES" => $propValues)
							);

							// print_r($requests);

						}
					}

					//if property type == binding element & sort <= $skuSortParams
					elseif($arNextProperty["SORT"] <= $skuSortParams && $arNextProperty["PROPERTY_TYPE"] == "E" && !empty($arNextProperty["LINK_IBLOCK_ID"])){

						// binding element sort
						$arBindingElementSort = array();

						// binding element filter
						$arBindingElementFilter = array(
							"IBLOCK_ID" => $arNextProperty["LINK_IBLOCK_ID"],
							"ACTIVE" => "Y"
						);

						//binding element select fileds
						$arBindingElementSelect = array(
							"ID",
							"NAME",
							"DETAIL_PICTURE"
						);

						//get binding element
						$rsLinkElements = CIBlockElement::GetList($arBindingElementSort, $arBindingElementFilter, false, false, $arBindingElementSelect);
						while ($arNextLinkElement = $rsLinkElements->GetNext()){

							//get pictures from binding element
							if(!empty($arNextLinkElement["DETAIL_PICTURE"])){
								$arNextLinkElement["UF_FILE"] = CFile::ResizeImageGet($arNextLinkElement["DETAIL_PICTURE"], array("width" => $skuPictureParamsWidth, "height" => $skuPictureParamsHeight), BX_RESIZE_IMAGE_PROPORTIONAL, false, false, false, $skuPictureParamsQuality);
							}

							$propValues[$arNextLinkElement["ID"]] = array(
								"VALUE" => $arNextLinkElement["ID"],
								"VALUE_XML_ID" => $arNextLinkElement["ID"],
								"DISPLAY_VALUE" => $arNextLinkElement["NAME"],
								"UF_XML_ID" => $arNextLinkElement["ID"],
								"IMAGE" => $arNextLinkElement["UF_FILE"],
								"NAME" => $arNextLinkElement["NAME"],
								"TYPE" => "E",
								"HIGHLOAD" => "N",
								"SELECTED" => "N",
								"DISABLED" => "N",
							);
						}

						//write values
						$arNextProperty["TYPE"] = "E";
						$arResult[$arNextProperty["CODE"]] = array_merge(
							$arNextProperty, array("VALUES" => $propValues)
						);

					}

				}

				//target cache
				global $CACHE_MANAGER;
				$CACHE_MANAGER->StartTagCache($cacheDir);
				$CACHE_MANAGER->RegisterTag("iblock_id_".$arSkuIblockInfo["IBLOCK_ID"]);
				$CACHE_MANAGER->RegisterTag("iblock_id_".$arSkuIblockInfo["PRODUCT_IBLOCK_ID"]);
				$CACHE_MANAGER->EndTagCache();

				//save cache
 				$obExtraCache->EndDataCache($arResult);

			}

			if($dbg){
				//show sku stat
				$DB->ShowSqlStat = true;
			}

		}

		return $arResult;

    } // end getSkuPropertiesFromIblock

	//get sku properties
    public static function getSkuFromProduct($productId, $iblockId = 0, $offersFilterId = false, $firstSkuOfferId = false, $arSkuIblockInfo, $arParams, $opCurrency = null){

    	global $USER;

    	//set default params
		$arParams["PICTURE_WIDTH"] = !empty($arParams["PICTURE_WIDTH"]) ? $arParams["PICTURE_WIDTH"] : 200;
		$arParams["PICTURE_HEIGHT"] = !empty($arParams["PICTURE_HEIGHT"]) ? $arParams["PICTURE_HEIGHT"] : 250;
		$arParams["PICTURE_QUALITY"] = !empty($arParams["PICTURE_QUALITY"]) ? $arParams["PICTURE_QUALITY"] : 80;
    	$arParams["HIDE_NOT_AVAILABLE"] = !empty($arParams["HIDE_NOT_AVAILABLE"]) ? $arParams["HIDE_NOT_AVAILABLE"] : "N";
		$arParams["PRODUCT_PRICE_CODE"] = !empty($arParams["PRODUCT_PRICE_CODE"]) ? $arParams["PRODUCT_PRICE_CODE"] : array();
		
		//additional params
    	$skuPictureParamsWidth = 30;
    	$skuPictureParamsHeight = 30;
    	$skuPictureParamsQuality = 100;

    	//property name for print image
    	$colorPropertyName = "COLOR";

    	//check exist offers
		$arElement["SKU_EXIST"] = CCatalogSKU::IsExistOffers($productId, $iblockId);

		if($arElement["SKU_EXIST"]){

			if (is_array($arSkuIblockInfo)){

				$arSkuProperties = DwSkuOffers::getSkuPropertiesFromIblock($arSkuIblockInfo);

				if(empty($arSkuProperties)){
					return false;
				}

				//sku properties id for optimization
				$arSkuPropertiesId = array();
				foreach ($arSkuProperties as $arNextSkuProperty){
					$arSkuPropertiesId[$arNextSkuProperty["ID"]] = $arNextSkuProperty["ID"];
				}

				//sku offers filter
				$arOffersFilter = array(
					"IBLOCK_ID" => $arSkuIblockInfo["IBLOCK_ID"],
					"PROPERTY_".$arSkuIblockInfo["SKU_PROPERTY_ID"] => $productId,
					"INCLUDE_SUBSECTIONS" => "N",
					"ACTIVE" => "Y"
				);

				//set offers id filter
				if(!empty($offersFilterId)){
					$arOffersFilter["ID"] = $offersFilterId;
				}

				//if hide not available
				if($arParams["HIDE_NOT_AVAILABLE"] == "Y"){
					$arOffersFilter["CATALOG_AVAILABLE"] = "Y";
				}

				//additional filter
				if(!empty($arParams["FILTER"])){

					//parse smart filter params
					if(!empty($arParams["FILTER"]["OFFERS"])){
						$arOffersFilter = array_merge($arOffersFilter, $arParams["FILTER"]["OFFERS"]);
					}

					else{
						$arOffersFilter = array_merge($arOffersFilter, $arParams["FILTER"]);
					}

				}

				//sku offers sort
				$arOffersSort = array(
					"SORT" => "ASC",
					"NAME" => "ASC"
				);

				//sku offers select
				$arOffersSelect = array(
					"ID",
					"IBLOCK_ID",
					"NAME",
					"CODE",
					"SORT",
					"DATE_CREATE",
					"DATE_MODIFY",
					"TIMESTAMP_X",
					"DATE_ACTIVE_TO",
					// "DETAIL_PAGE_URL",
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

				//create arrays
				$arElement["SKU_OFFERS"] = array();
				$arElement["SKU_OFFERS_LINK"] = array();

				// get sku offers
				$rsOffersMx = CIBlockElement::GetList($arOffersSort, $arOffersFilter, false, false, $arOffersSelect);
				while($arSkuMx = $rsOffersMx->GetNextElement()){

					$arSkuFieldsMx = $arSkuMx->GetFields();
					$arSkuPropertiesMx = $arSkuMx->GetProperties(array("ID" => "ASC"), array("ID" => $arSkuPropertiesId, "EMPTY" => "N"));

					//write
					$arElement["SKU_OFFERS"][$arSkuFieldsMx["ID"]] = array_merge($arSkuFieldsMx, array("PROPERTIES" => $arSkuPropertiesMx));

					//write links _CIBElement
					$arElement["SKU_OFFERS_LINK"][$arSkuFieldsMx["ID"]] = $arSkuMx;

				}

			}

		}

		if(!empty($arElement["SKU_OFFERS"])){

			//sort offers params
			//disable sort offers (sort from properties)
			$offersEnableSort = false;
			//standart sort
			$offersLastSort = 500;

			//if set first sku offer
			if(!empty($firstSkuOfferId) && !empty($arElement["SKU_OFFERS"][$firstSkuOfferId])){

				//copy offer by index
				$arTmpOffer["SKU_OFFERS"][$firstSkuOfferId] = $arElement["SKU_OFFERS"][$firstSkuOfferId];
				//delete offer by index
				unset($arElement["SKU_OFFERS"][$firstSkuOfferId]);
				//Insert first
				$arElement["SKU_OFFERS"] = $arTmpOffer["SKU_OFFERS"] + $arElement["SKU_OFFERS"];
				//enable sort flag (sort replace first sku offer by func params)
				$offersEnableSort = true;
				//set first index
				$firstOfferIndex = $firstSkuOfferId;

			}

			//else calc from base sort
			else{

				//first offer key
				$firstOfferIndex = key($arElement["SKU_OFFERS"]);

				if($arElement["SKU_OFFERS"][$firstOfferIndex]["SORT"] != $offersLastSort){
					//enable sort offers (sort from sku offers)
					$offersEnableSort = true;
				}

			}

		}
		
		if(!empty($arElement["SKU_OFFERS"]) && !empty($arSkuProperties)){

			//tmp sku properties
			$arElement["SKU_PROPERTIES"] = $arSkuProperties;

			//check valid sku properties
			foreach ($arElement["SKU_PROPERTIES"] as $ip => $arProp){

				//check valid sku properties values
				foreach ($arProp["VALUES"] as $ipv => $arPropValue){

					$find = false;
					//check values for all offers if not found delete
					foreach ($arElement["SKU_OFFERS"] as $ipo => $arOffer){

						//check for highload
						if(!empty($arProp["HIGHLOAD"]) && $arProp["HIGHLOAD"] == "Y"){

							if($arOffer["PROPERTIES"][$arProp["CODE"]]["VALUE"] == $arPropValue["UF_XML_ID"]){
								$find = true;
								break(1);
							}

						}
						//check for after property
						else{

							if($arOffer["PROPERTIES"][$arProp["CODE"]]["VALUE"] == $arPropValue["VALUE"]){
								$find = true;
								break(1);
							}

						}

					}

					//delete invalid values
					if(!$find){
						unset($arElement["SKU_PROPERTIES"][$ip]["VALUES"][$ipv]);
					}

				}

			}

			//array for save property levels
			$arPropClean = array();
			//counter
			$iter = 0;

			//set active offer
			foreach ($arElement["SKU_PROPERTIES"] as $ip => $arProp){

				if(!empty($arProp["VALUES"])){

					//get all values for current property
					$arKeys = array_keys($arProp["VALUES"]);
					$selectedUse = false;

					//first iteration
					if($iter === 0){

						//if enabled offers sort
						if($offersEnableSort){

							//write first offers value by sort

							//set property selected
							$arElement["SKU_PROPERTIES"][$ip]["VALUES"][$arElement["SKU_OFFERS"][$firstOfferIndex]["PROPERTIES"][$ip]["VALUE"]]["SELECTED"] = "Y";

							//write values for current level
							$arPropClean[$ip] = array(
								"PROPERTY" => $ip,
								"VALUE"    => $arElement["SKU_OFFERS"][$firstOfferIndex]["PROPERTIES"][$ip]["VALUE"],
								"HIGHLOAD" => $arProp["HIGHLOAD"]
							);

						}

						else{

							//write first value

							//set property selected
							$arElement["SKU_PROPERTIES"][$ip]["VALUES"][$arKeys[0]]["SELECTED"] = "Y";

							//write values for current level
							$arPropClean[$ip] = array(
								"PROPERTY" => $ip,
								"VALUE"    => $arKeys[0],
								"HIGHLOAD" => $arProp["HIGHLOAD"]
							);

						}

					}else{

						//level >= 2
						//found selected property
						foreach ($arKeys as $key => $keyValue){

							//set disable flag
							$disabled = true;

							//get value for check
							$checkValue = $arElement["SKU_PROPERTIES"][$ip]["VALUES"][$keyValue]["VALUE"];

							//each all offers
							foreach ($arElement["SKU_OFFERS"] as $io => $arOffer){

								//check values
								if($arOffer["PROPERTIES"][$ip]["VALUE"] == $checkValue){

									//unset disable flag
									$disabled = false;

									//set selected flag
									$selected = true;

									//check values for previous level
									foreach ($arPropClean as $ic => $arNextClean){

										//if value is not in this sentence
										if($arOffer["PROPERTIES"][$arNextClean["PROPERTY"]]["VALUE"] != $arNextClean["VALUE"]){

											if($ic == $ip){
												break(2);
											}

											//set disable flag
											$disabled = true;

											//unset selected flag
											$selected = false;

											break(1);

										}

									}

									//if enable offers sort
									if($offersEnableSort && $disabled == false){
										break(1);
									}

									//if disabled offers sort
									if(!$offersEnableSort){

										if($selected && !$selectedUse){

											//set selected use flag
											$selectedUse = true;

											//set property selected
											$arElement["SKU_PROPERTIES"][$ip]["VALUES"][$keyValue]["SELECTED"] = "Y";

											//write values for current level
											$arPropClean[$ip] = array(
												"PROPERTY" => $ip,
												"VALUE"    => $keyValue,
												"HIGHLOAD" => $arProp["HIGHLOAD"]
											);

											break(1);

										}

									}

								}

							}

							//disable property values
							if($disabled){
								$arElement["SKU_PROPERTIES"][$ip]["VALUES"][$keyValue]["DISABLED"] = "Y";
							}

						}


						//if enable offers sort
						if($offersEnableSort){

							//set property selected
							$arElement["SKU_PROPERTIES"][$ip]["VALUES"][$arElement["SKU_OFFERS"][$firstOfferIndex]["PROPERTIES"][$ip]["VALUE"]]["SELECTED"] = "Y";

							//write values for current level
							$arPropClean[$ip] = array(
								"PROPERTY" => $ip,
								"VALUE"    => $arElement["SKU_OFFERS"][$firstOfferIndex]["PROPERTIES"][$ip]["VALUE"],
								"HIGHLOAD" => $arProp["HIGHLOAD"]
							);

						}

					}

					//next iteration
					$iter++;

				}

			}

			//get sku pictures
			if(!empty($arElement["SKU_PROPERTIES"][$colorPropertyName])){
				foreach ($arElement["SKU_PROPERTIES"][$colorPropertyName]["VALUES"] as $ic => $arProperty){
					foreach ($arElement["SKU_OFFERS"] as $io => $arNextOffer){
						if($arNextOffer["PROPERTIES"][$colorPropertyName]["VALUE"] == $arProperty["VALUE"]){
							if(!empty($arNextOffer["DETAIL_PICTURE"])){
								$arPropertyImage = CFile::ResizeImageGet($arNextOffer["DETAIL_PICTURE"], array("width" => $skuPictureParamsWidth, "height" => $skuPictureParamsHeight), BX_RESIZE_IMAGE_PROPORTIONAL, false, false, false, 100);
								$arElement["SKU_PROPERTIES"][$colorPropertyName]["VALUES"][$ic]["IMAGE"] = $arPropertyImage;
								break(1);
							}
						}
					}
				}
			}

			//write info for active sku offer
			foreach ($arElement["SKU_OFFERS"] as $ir => $arOffer){

				//set active flag
				$active = true;

				//if disabled offers sort
				//else first sku offer
				if(!$offersEnableSort){
					//to check save values for current sku offer
					foreach ($arPropClean as $ic => $arNextClean){
						if($arOffer["PROPERTIES"][$arNextClean["PROPERTY"]]["VALUE"] != $arNextClean["VALUE"]){
							//unset active flag
							$active = false;
							break(1);
						}
					}
				}

				//save info for active offer
				if($active){

					$arElement["~ID"] = $productId;
					$arElement["ID"] = $arOffer["ID"];

					//get price info
					$arElement["PRODUCT_PRICE_ALLOW"] = array();
					$arElement["PRODUCT_PRICE_ALLOW_FILTER"] = array();

					if(!empty($arParams["PRODUCT_PRICE_CODE"])){

						//get available prices code & id
						$arPricesInfo = DwPrices::getPriceInfo($arParams["PRODUCT_PRICE_CODE"], $arSkuIblockInfo["IBLOCK_ID"]);
						if(!empty($arPricesInfo)){
					    	$arElement["PRODUCT_PRICE_ALLOW"] = $arPricesInfo["ALLOW"];
						    $arElement["PRODUCT_PRICE_ALLOW_FILTER"] = $arPricesInfo["ALLOW_FILTER"];
						}

					}

					//get prices
					$arElement["PRICE"] = DwPrices::getPricesByProductId($arElement["ID"], $arElement["PRODUCT_PRICE_ALLOW"], $arElement["PRODUCT_PRICE_ALLOW_FILTER"], $arParams["PRODUCT_PRICE_CODE"], $arElement["IBLOCK_ID"], $opCurrency);

					//if > 0 display [?] for more price table
					$arElement["EXTRA_SETTINGS"]["COUNT_PRICES"] = $arElement["PRICE"]["COUNT_PRICES"];
					
					//set main picture
					if(!empty($arOffer["DETAIL_PICTURE"])){
						$arElement["PICTURE"] = CFile::ResizeImageGet($arOffer["DETAIL_PICTURE"], array("width" => $arParams["PICTURE_WIDTH"], "height" => $arParams["PICTURE_HEIGHT"]), BX_RESIZE_IMAGE_PROPORTIONAL, false, false, false, $arParams["PICTURE_QUALITY"]);
					}

					//stores info
					$arElement["EXTRA_SETTINGS"]["STORES_MAX_QUANTITY"] = 0;
					$rsStore = CCatalogStoreProduct::GetList(array(), array("PRODUCT_ID" => $arOffer["ID"]), false, false, array("ID", "AMOUNT"));
					while($arNextStore = $rsStore->GetNext()){
						$arElement["EXTRA_SETTINGS"]["STORES"][] = $arNextStore;
						if($arNextStore["AMOUNT"] > $arElement["EXTRA_SETTINGS"]["STORES_MAX_QUANTITY"]){
							$arElement["EXTRA_SETTINGS"]["STORES_MAX_QUANTITY"] = $arNextStore["AMOUNT"];
						}
					}

					//get full properties
					$arOffer["PROPERTIES"] = $arElement["SKU_OFFERS_LINK"][$arOffer["ID"]]->GetProperties(
						array("ID" => "ASC"), array("EMPTY" => "N")
					);

					//set more information
					$arElement["CODE"] = $arOffer["CODE"];
					$arElement["SKU_INFO"] = $arSkuIblockInfo;
					$arElement["IBLOCK_ID"] = $arOffer["IBLOCK_ID"];
					$arElement["PROPERTIES"] = $arOffer["PROPERTIES"];
					$arElement["TIMESTAMP_X"] = $arOffer["TIMESTAMP_X"];
					$arElement["DATE_CREATE"] = $arOffer["DATE_CREATE"];
					$arElement["DETAIL_PICTURE"] = $arOffer["DETAIL_PICTURE"];
					// $arElement["DETAIL_PAGE_URL"] = $arOffer["DETAIL_PAGE_URL"];
					$arElement["PREVIEW_PICTURE"] = $arOffer["PREVIEW_PICTURE"];
					$arElement["CATALOG_MEASURE"] = $arOffer["CATALOG_MEASURE"];
					$arElement["CATALOG_QUANTITY"] = $arOffer["CATALOG_QUANTITY"];
					$arElement["CATALOG_AVAILABLE"] = $arOffer["CATALOG_AVAILABLE"];
					$arElement["CATALOG_SUBSCRIBE"] = $arOffer["CATALOG_SUBSCRIBE"];
					$arElement["CANONICAL_PAGE_URL"] = $arOffer["CANONICAL_PAGE_URL"];
					$arElement["CATALOG_CAN_BUY_ZERO"] = $arOffer["CATALOG_CAN_BUY_ZERO"];
					$arElement["CATALOG_QUANTITY_TRACE"] = $arOffer["CATALOG_QUANTITY_TRACE"];

					//extra settings

					//set base currency
					$arElement["EXTRA_SETTINGS"]["CURRENCY"] = empty($opCurrency) ? $arElement["PRICE"]["RESULT_PRICE"]["CURRENCY"] : $opCurrency;

					//get measures
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
						array("PRODUCT_ID" => intval($arOffer["ID"])),
						false,
						false,
						array()
					);

					if($arProductMeasureRatio = $rsMeasureRatio->Fetch()){
						if(!empty($arProductMeasureRatio["RATIO"])){
							$arElement["EXTRA_SETTINGS"]["BASKET_STEP"] = $arProductMeasureRatio["RATIO"];
						}
					}

					//get pannel buttons info
					$arButtons = CIBlock::GetPanelButtons(
						$arElement["IBLOCK_ID"],
						$arElement["ID"],
						0,
						array("SECTION_BUTTONS" => false,
							  "SESSID" => true,
							  "CATALOG" => false
						)
					);

					//set edit links
					$arElement["EDIT_LINK"] = $arButtons["edit"]["edit_element"]["ACTION_URL"];
					$arElement["DELETE_LINK"] = $arButtons["edit"]["delete_element"]["ACTION_URL"];


				}

				//break each
				if($offersEnableSort){
					break(1);
				}

			}
		}
		
		//empty offers
		else{
			return false;
		}

		return $arElement;

	}//end getSkuFromProduct

}// end DwSkuOffers

?>