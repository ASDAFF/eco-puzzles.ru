<?if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();?>
<?if (CModule::IncludeModule("sale") && CModule::IncludeModule("catalog") && CModule::IncludeModule("iblock")){
	
	GLOBAL $USER;
	$OPTION_ADD_CART  = COption::GetOptionString("catalog", "default_can_buy_zero");
	$OPTION_PRICE_TAB = COption::GetOptionString("catalog", "show_catalog_tab_with_offers");
	$OPTION_QUANTITY_TRACE = COption::GetOptionString("catalog", "default_quantity_trace");
	$arMeasureProductsID = array();

	$arResult["BASE_LANG_CURRENCY"] = $OPTION_CURRENCY = CCurrency::GetBaseCurrency();
	$arResult["DELIVERY_PERSON_ARRAY_ID"] = array();
	$arResult["ERRORS"] = array();
	$arResult["OPTION_QUANTITY_TRACE"] = $OPTION_QUANTITY_TRACE;

	$dbPriceType = CCatalogGroup::GetList(
	        array("SORT" => "ASC"),
	        array()
	);

	while ($arPriceType = $dbPriceType->Fetch()){
	    $PRICE_CODES[$arPriceType["NAME"]] = $arPriceType["ID"];
	}

	CSaleBasket::UpdateBasketPrices(CSaleBasket::GetBasketUserID(), SITE_ID);

	$arID = array();
	$arBasketItems = array();
	$arBasketOrder = array("PRICE" => "ASC");
	$arBasketUser = array("FUSER_ID" => CSaleBasket::GetBasketUserID(), "LID" => SITE_ID, "ORDER_ID" => "NULL");
	$arBasketSelect = array("ID", "CALLBACK_FUNC", "MODULE", "PRODUCT_ID", "QUANTITY", "DELAY",
			"CAN_BUY", "PRICE", "WEIGHT", "NAME", "CURRENCY", "CATALOG_XML_ID", "VAT_RATE",
			"NOTES", "DISCOUNT_PRICE", "PRODUCT_PROVIDER_CLASS", "DIMENSIONS", "TYPE", "SET_PARENT_ID", "DETAIL_PAGE_URL", "*"
	);
	$dbBasketItems = CSaleBasket::GetList($arBasketOrder, $arBasketUser, false, false, $arBasketSelect);

	$arResult["SUM"]          = 0;
	$arResult["ORDER_WEIGHT"] = 0;
	$arResult["SUM_DELIVERY"] = 0;

	$arResult["MAX_DIMENSIONS"] = array();
	$arResult["ITEMS_DIMENSIONS"] = array();

	while ($arItems = $dbBasketItems->Fetch()){

		// CSaleBasket::UpdatePrice(
		// 	$arItems["ID"],
		// 	$arItems["CALLBACK_FUNC"],
		// 	$arItems["MODULE"],
		// 	$arItems["PRODUCT_ID"],
		// 	$arItems["QUANTITY"],
		// 	"N",
		// 	CSaleBasket::GetProductProvider($arItems)
		// );

		$xres = CIBlockElement::GetList(Array(), Array("ID" => $arItems["PRODUCT_ID"]), false, false, Array("ACTIVE"));
		if($arProducts = $xres->GetNext()){
			if($arProducts["ACTIVE"] == "Y" && !CSaleBasketHelper::isSetItem($arItems)){
				array_push($arID, $arItems["ID"]);
			}
		}

		$arDim = $arItems["DIMENSIONS"] = $arItems["~DIMENSIONS"];

		if(is_array($arDim)){
			$arResult["MAX_DIMENSIONS"] = CSaleDeliveryHelper::getMaxDimensions(
				array(
					$arDim["WIDTH"],
					$arDim["HEIGHT"],
					$arDim["LENGTH"]
					),
				$arResult["MAX_DIMENSIONS"]);

			$arResult["ITEMS_DIMENSIONS"][] = $arDim;
		}
	}

	if (!empty($arID)){

		$dbBasketItems = CSaleBasket::GetList(
			$arBasketOrder,
			array(
				"ID" => $arID,
				"ORDER_ID" => "NULL"
			),
			false,
			false,
			$arBasketSelect
		);

		while ($arItems = $dbBasketItems->Fetch()){
		    $arResult["SUM"]    += ($arItems["PRICE"]  * $arItems["QUANTITY"]);
		    $arResult["ORDER_WEIGHT"] += ($arItems["WEIGHT"] * $arItems["QUANTITY"]);
		    $arResult["ITEMS"][$arItems["PRODUCT_ID"]] = $arItems;
		    $arResult["TMP_ITEMS"][$arItems["PRODUCT_ID"]] = $arItems;
		    $arResult["ID"][] = $arItems["PRODUCT_ID"];
		}
	 
	    $arOrder = array(
	      "SITE_ID" => SITE_ID,
	      "USER_ID" => $USER->GetID(),
	      "ORDER_PRICE" => $arResult["SUM"],
	      "ORDER_WEIGHT" => $arResult["ORDER_WEIGHT"],
	      "BASKET_ITEMS" => $arResult["ITEMS"]
	   );
	   
	   $arOptions = array(
	      "COUNT_DISCOUNT_4_ALL_QUANTITY" => "Y",
	   );
	   
	   $arErrors = array();
	   
	   CSaleDiscount::DoProcessOrder($arOrder, $arOptions, $arErrors);

	   $PRICE_ALL = 0;
	   $DISCOUNT_PRICE_ALL = 0;
	   $QUANTITY_ALL = 0;

	   foreach ($arOrder["BASKET_ITEMS"] as $arItem){
	      $arResult["ITEMS"][$arItem["PRODUCT_ID"]] = $arItem;
	      $PRICE_ALL += $arItem["PRICE"] * $arItem["QUANTITY"];
	      $DISCOUNT_PRICE_ALL += $arItem["DISCOUNT_PRICE"] * $arItem["QUANTITY"];      
	      $QUANTITY_ALL += $arItem['QUANTITY'];
	   }

	   $arResult["SUM"] = $PRICE_ALL;
	   $arResult["DISCOUNT_PRICE_ALL"] = $DISCOUNT_PRICE_ALL;
	   $arResult["QUANTITY_ALL"] = $QUANTITY_ALL;
	   $arResult["FULL_DISCOUNT_LIST"] = $arOrder["FULL_DISCOUNT_LIST"];
	   $arResult["APPLIED_DISCOUNT_LIST"] = $arOrder["DISCOUNT_LIST"];

	}

	// add fields
	if(!empty($arResult["ID"])){

		$arSelect = Array(
			"ID",
			"IBLOCK_ID",
			"NAME",
			"DETAIL_PAGE_URL",
			"DETAIL_PICTURE",
			"CATALOG_GROUP_1",
			"CATALOG_QUANTITY",
			"CATALOG_MEASURE",
			"PROPERTY_*",
			"ACTIVE",
			"*"
		);

		$res = CIBlockElement::GetList(
			Array(),
			Array(
				"ID" => $arResult["ID"],
				"IBLOCK_ID" => $arResult["IBLOCK_ID"]
			),
			false,
			false,
			$arSelect
		);

		while($ob = $res->GetNextElement()){
			$arFields = $ob->GetFields();
			$arProductProperties = $ob->GetProperties();

			$skuProductInfo = CCatalogSKU::getProductList($arFields["ID"]);
			
			if(!empty($skuProductInfo)){
				foreach ($skuProductInfo as $itx => $skuProductInfoElement) {
					$productBySku = CIBlockElement::GetByID($skuProductInfoElement["ID"]);
					if(!empty($productBySku)){
						if($arResProductSku = $productBySku->GetNextElement()){
							$arResProductSkuProperties = $arResProductSku->GetProperties();
							$arProductProperties = array_merge($arResProductSkuProperties, $arProductProperties);
						}
					}
				}
			}

			foreach ($arProductProperties as $isp => $arProperty) {
				if($arProperty["PROPERTY_TYPE"] == "E" || $arProperty["PROPERTY_TYPE"] == "S" || $arProperty["PROPERTY_TYPE"] == "N"){
					$arProductProperties[$isp] = CIBlockFormatProperties::GetDisplayValue($arFields, $arProductProperties[$isp], "catalog_out");
				}
			}

			$arPrice = CCatalogProduct::GetOptimalPrice($arFields["ID"], 1, $USER->GetUserGroupArray());
			if($arPrice["PRICE"]["CURRENCY"] != $OPTION_CURRENCY){
				$arPrice["PRICE"]["PRICE"] = CCurrencyRates::ConvertCurrency($arPrice["PRICE"]["PRICE"], $arPrice["PRICE"]["CURRENCY"], $OPTION_CURRENCY);
			}

			//комплект	
			$arComplectID = array();
			$arFields["COMPLECT"] = array();
			
			$rsComplect = CCatalogProductSet::getList(
				array("SORT" => "ASC"),
				array(
					"TYPE" => 1, //complect or set
					"OWNER_ID" => $arFields["ID"],
					"!ITEM_ID" => $arFields["ID"]
				),
				false,
				false,
				array("*")
			);

			if($arComplectItem = $rsComplect->Fetch()) {
				$arFields["IS_COMPLECT"] = "Y";
			}

			// while ($arComplectItem = $rsComplect->Fetch()){
			// 	$arFields["COMPLECT"]["ITEMS"][$arComplectItem["ITEM_ID"]] = $arComplectItem;
			// 	$arComplectID[$arComplectItem["ITEM_ID"]] = $arComplectItem["ITEM_ID"];
			// }

			if(!empty($arComplectID)){
		
				$arFields["COMPLECT"]["RESULT_PRICE"] = 0;
				$arFields["COMPLECT"]["RESULT_BASE_DIFF"] = 0;
				$arFields["COMPLECT"]["RESULT_BASE_PRICE"] = 0;

				$arSelect = Array("ID", "IBLOCK_ID", "NAME", "DETAIL_PICTURE", "DETAIL_PAGE_URL", "CATALOG_MEASURE", "CATALOG_AVAILABLE", "CATALOG_WEIGHT");
				$arFilter = Array("ID" => $arComplectID, "ACTIVE_DATE" => "Y", "ACTIVE" => "Y");
				$rsComplectProducts = CIBlockElement::GetList(Array(), $arFilter, false, false, $arSelect);
				while($obComplectProducts = $rsComplectProducts->GetNextElement()){
					$complectProductFields = $obComplectProducts->GetFields();
					$complectProductFields["PRICE"] = CCatalogProduct::GetOptimalPrice($complectProductFields["ID"], 1, $USER->GetUserGroupArray());
					$complectProductFields["PRICE"]["DISCOUNT_PRICE"] = $complectProductFields["PRICE"]["DISCOUNT_PRICE"] * $arFields["COMPLECT"]["ITEMS"][$complectProductFields["ID"]]["QUANTITY"];
					$complectProductFields["PRICE"]["DISCOUNT_PRICE"] -= $complectProductFields["PRICE"]["DISCOUNT_PRICE"] * $arFields["COMPLECT"]["ITEMS"][$complectProductFields["ID"]]["DISCOUNT_PERCENT"] / 100;
					$complectProductFields["PRICE"]["RESULT_PRICE"]["BASE_PRICE"] = $complectProductFields["PRICE"]["RESULT_PRICE"]["BASE_PRICE"]  * $arFields["COMPLECT"]["ITEMS"][$complectProductFields["ID"]]["QUANTITY"];
					$complectProductFields["PRICE"]["PRICE_DIFF"] = $complectProductFields["PRICE"]["RESULT_PRICE"]["BASE_PRICE"] - $complectProductFields["PRICE"]["DISCOUNT_PRICE"];
					$complectProductFields["PRICE"]["BASE_PRICE_FORMATED"] = CurrencyFormat($complectProductFields["PRICE"]["RESULT_PRICE"]["BASE_PRICE"], $OPTION_CURRENCY);
					$complectProductFields["PRICE"]["PRICE_FORMATED"] = CurrencyFormat($complectProductFields["PRICE"]["DISCOUNT_PRICE"], $OPTION_CURRENCY);
					$arFields["COMPLECT"]["RESULT_BASE_PRICE"] += $complectProductFields["PRICE"]["RESULT_PRICE"]["BASE_PRICE"];
				}
				$arFields["OLD_PRICE"] = $arFields["COMPLECT"]["RESULT_BASE_PRICE"];
			}else{
				$arFields["OLD_PRICE"] = $arPrice["PRICE"]["PRICE"]; //discount
			}

			if(empty($arFields["DETAIL_PICTURE"])){
				$productSkuInfo = CCatalogSku::GetProductInfo($arFields["ID"]);
				if (is_array($productSkuInfo)){
					$getListSkuProductInfo = CIBlockElement::GetList(
						Array(),
						array(
							"ID" => $productSkuInfo["ID"]
						),
						false,
						false,
						array(
							"DETAIL_PICTURE",
						)
					)->GetNextElement();
					$arSkuProductResult = $getListSkuProductInfo->GetFields();
					$arFields["DETAIL_PICTURE"] = $arSkuProductResult["DETAIL_PICTURE"];
				}			
			}
			$arFields["PICTURE"] = CFile::ResizeImageGet(
				$arFields["DETAIL_PICTURE"],
				array(
					"width"  => $arParams["BASKET_PICTURE_WIDTH"],
					"height" => $arParams["BASKET_PICTURE_HEIGHT"]
				),
				BX_RESIZE_IMAGE_PROPORTIONAL,
				false
			);
			
			$arFields["CAN_BUY"] = $OPTION_ADD_CART == "Y" ?  true : false;

			$addBasketQuantity = 1;
			$rsMeasureRatio = CCatalogMeasureRatio::getList(
				array(), 
				array("PRODUCT_ID" => $arFields["ID"]), 
				false, 
				false, 
				array()
			);
			
			if($arProductMeasureRatio = $rsMeasureRatio->Fetch()){
				if(!empty($arProductMeasureRatio["RATIO"])){
					$addBasketQuantity = $arProductMeasureRatio["RATIO"];
				}
			}

			//price count
			$dbPrice = CPrice::GetList(
		        array(),
		        array("PRODUCT_ID" => $arFields["ID"], "CAN_ACCESS" => "Y"),
		        false,
		        false,
		        array("ID")
		    );
			$arFields["COUNT_PRICES"] = $dbPrice->SelectedRowsCount();

			if(empty($arFields["COMPLECT"]) && empty($arFields["IS_COMPLECT"])){
				//Информация о складах
				$rsStore = CCatalogStoreProduct::GetList(array(), array("PRODUCT_ID" => $arFields["ID"]), false, false, array("ID", "AMOUNT")); 
				while($arNextStore = $rsStore->GetNext()){
					$arFields["STORES"][] = $arNextStore;
				}
			}

			$arFields["ADDBASKET_QUANTITY_RATIO"] = $addBasketQuantity;
			$arMeasureProductsID[$arFields["CATALOG_MEASURE"]] = $arFields["CATALOG_MEASURE"];
			if($arFields["ACTIVE"] == "Y"){
				$arResult["ITEMS"][$arFields["ID"]]["INFO"] = array_merge($arFields, array("PROPERTIES" => $arProductProperties));
			}
		}

	}

	if(!empty($arResult["ID"])){


		###
		#############
		############# SALE ORDER #############
								 #############
									       ###


		global $USER;

		$rsUser = CUser::GetByID($USER->GetID());
		$arResult["USER"] = $rsUser->Fetch();

		if(!empty($_SESSION["USER_GEO_POSITION"]["locationID"])){
			$dbLoc = CSaleLocation::GetList(array("LOC_DEFAULT" => "ASC"), array("ID" => $_SESSION["USER_GEO_POSITION"]["locationID"], "LID" => LANGUAGE_ID), false, false, array("*"));
			if($arLoc = $dbLoc->Fetch()){
				$arResult["LOCATION"] = $arLoc;
				$arUserResult["DELIVERY_LOCATION"] = $arLoc["ID"];
				$arUserResult["DELIVERY_LOCATION_BCODE"] = $arLoc['CODE'];
			}
		}else{
			include(dirname(__FILE__)."/functions.php");

			$getUserCity = dwGetCity(BX_UTF);

			if(!$getUserCity){
				$dbProps = CSaleOrderProps::GetList(
			        array(),
			        array(
			            "IS_LOCATION" => "Y",
			            "ACTIVE" => "Y",
			            "UTIL" => "N",
			            "RELATED" => false
			        ),
			        false,
			        false,
			        array("*")
			    );

				while($arProps = $dbProps->Fetch()){
					if(!empty($arProps["DEFAULT_VALUE"])){
						$arLocFilter = array("ID" => $arProps["DEFAULT_VALUE"], "LID" => LANGUAGE_ID);
						break;
					}
				}

			}else{
				$arLocFilter = array("CITY_NAME" => $getUserCity["CITY"][1], "LID" => LANGUAGE_ID);
			}

			$dbLoc = CSaleLocation::GetList(array("LOC_DEFAULT" => "ASC"), $arLocFilter, false, false, array("*"));
			if($arLoc = $dbLoc->Fetch()){
				$arResult["LOCATION"] = $arLoc;
				$arUserResult["DELIVERY_LOCATION"] = $arLoc["ID"];
				$arUserResult["DELIVERY_LOCATION_BCODE"] = $arLoc['CODE'];
			}
		}

		$arLocs = CSaleLocation::GetLocationZIP($arUserResult["DELIVERY_LOCATION"]);
		if($arLocs = $arLocs->Fetch()){
			if(!empty($arLocs["ZIP"])){
				$arUserResult["DELIVERY_LOCATION_ZIP"] = $arLocs["ZIP"];
			}
		}

		$locFrom = COption::GetOptionString("sale", "location", false, SITE_ID);

		$arD2P = array();
		$arP2D = array();

		$dbRes = CSaleDelivery::GetDelivery2PaySystem(array());
		while ($arRes = $dbRes->Fetch()){
			$dCode = $arRes["DELIVERY_ID"];

			if(!empty($arRes["DELIVERY_PROFILE_ID"]))
				$dCode .= ':'.$arRes["DELIVERY_PROFILE_ID"];

			$dId = CSaleDelivery::getIdByCode($dCode);
			$arD2P[$dId][$arRes["PAYSYSTEM_ID"]] = $arRes["PAYSYSTEM_ID"];
			$arP2D[$arRes["PAYSYSTEM_ID"]][$dId] = $dId;
			$bShowDefaultSelected = False;
		}

		$dbPerson = CSalePersonType::GetList(
			Array(
				"SORT" => "ASC"
				),
			Array(
				"LID" => SITE_ID,
				"ACTIVE" => Y
			)
		);

		while ($arPersonType = $dbPerson->Fetch()){

			if(empty($arResult["PERSON_TYPE"])){
				$arPersonType["FIRST"] = "Y";
			}

			if(empty($arResult["PERSON_FIRST"])){
				$arResult["PERSON_FIRST"] = $arPersonType["ID"];
			}

			$arResult["PERSON_TYPE"][$arPersonType["ID"]] =  $arPersonType;
			$arUserResult["PERSON_TYPE_ID"] = $arPersonType["ID"];

			$dbGrop = CSaleOrderPropsGroup::GetList(
		        array(
		        	"SORT" => "ASC"
		        ),
		        array(
		        	"PERSON_TYPE_ID" => $arPersonType["ID"]
		        ),
		        false,
		        false,
		        array()
			);

			while ($arGroups = $dbGrop->Fetch()){
				$arResult["PROP_GROUP"][$arPersonType["ID"]][$arGroups["ID"]] = $arGroups;

				$dbProps = CSaleOrderProps::GetList(
			        array(
			        	"SORT" => "ASC"
			        ),
			        array(
			            "PROPS_GROUP_ID" => $arGroups["ID"],
			            "PERSON_TYPE_ID" => $arPersonType["ID"],
			            "ACTIVE" => "Y",
			            "UTIL" => "N",
			            "RELATED" => false
			        ),
			        false,
			        false,
			        array("*")
			    );

				while ($arProps = $dbProps->Fetch()){
					$arResult["PROPERTIES"][$arGroups["ID"]][] = $arProps;
				}
			}

			$dbPay = CSalePaySystem::GetList(
				$arOrder = Array(
					"SORT"=>"ASC",
					"PSA_NAME"=>"ASC"
				),
				Array(
					"ACTIVE" => "Y",
					"PERSON_TYPE_ID" => $arPersonType["ID"]
				)
			);

			while ($arPay = $dbPay->Fetch()){
				
				if(empty($arResult["PAYSYSTEM_FIRST"])){
					$arResult["PAYSYSTEM_FIRST"] = $arPay["ID"];
				}

				$arResult["PAYSYSTEM"][$arPersonType["ID"]][] = $arPay;

				if(empty($arUserResult["PAY_SYSTEM_ID"])){
					$arUserResult["PAY_SYSTEM_ID"] = $arPay["ID"];
				}

			}

			$shipment = CSaleDelivery::convertOrderOldToNew(array(
				"SITE_ID" => SITE_ID,
				"WEIGHT" => $arResult["ORDER_WEIGHT"],
				"PRICE" =>  $arResult["ORDER_PRICE"],
				"LOCATION_TO" => isset($arUserResult["DELIVERY_LOCATION_BCODE"]) ? $arUserResult["DELIVERY_LOCATION_BCODE"] : $arUserResult["DELIVERY_LOCATION"],
				"LOCATION_ZIP" => $arUserResult["DELIVERY_LOCATION_ZIP"],
				"ITEMS" =>  $arResult["ITEMS"],
				"CURRENCY" => $arResult["BASE_LANG_CURRENCY"],
				"PERSON_TYPE_ID" => $arUserResult["PERSON_TYPE_ID"]
			));

			$arDeliveryServiceAll = \Bitrix\Sale\Delivery\Services\Manager::getRestrictedObjectsList($shipment);

			foreach($arDeliveryServiceAll as $deliveryObj){ 
				
				$arNextDelivery = array(
					"ID" => $deliveryObj->getID(),
					"NAME" => $deliveryObj->getName(),
				);
				
				if($arP2D[$arUserResult["PAY_SYSTEM_ID"]][$arNextDelivery["ID"]] == $arNextDelivery["ID"]){

					$deliveryPriceCalculate = $deliveryObj->calculate($shipment);
					$arNextDelivery["PRICE"] = $deliveryPriceCalculate->getPrice();
					$arNextDelivery["PRICE_FORMATED"] = SaleFormatCurrency($deliveryPriceCalculate->getPrice(), $arResult["BASE_LANG_CURRENCY"]);
					$arNextDelivery["CURRENCY"] = $arResult["BASE_LANG_CURRENCY"];
					
					if(!$deliveryPriceCalculate->isSuccess()){
						foreach($deliveryPriceCalculate->getErrorMessages() as $message){
							$arResult["ERRORS"][] = "component: sale.basket.basket ".$message."(delivery id: ".$arNextDelivery["ID"].") - (personTypeId: ".$arUserResult["PERSON_TYPE_ID"].")";
						}
					}else{
						$arResult["DELIVERY"][$arPersonType["ID"]][] = $arNextDelivery;
						if(empty($arResult["DELIVERY_FIRST"])){
							$arResult["DELIVERY_FIRST"] = $arNextDelivery["ID"];
						}
					}
				}
			}

			if(!empty($arResult["DELIVERY"][$arPersonType["ID"]])){
				foreach ($arResult["DELIVERY"][$arPersonType["ID"]] as $id => $arDelivery) {

					$dbProps = CSaleOrderProps::GetList(
				        array(
				        	"SORT" => "ASC"
				        ),
				        array(
				            "ACTIVE" => "Y",
				            "UTIL" => "N",
				            "RELATED" => array("DELIVERY_ID" => $arDelivery["ID"])
				        ),
				        false,
				        false,
				        array("*")
				    );

					while ($arProps = $dbProps->GetNext()){
						$arProps["DELIVERY_ID"] = $arDelivery["ID"];
						$arResult["DELIVERY_PROPS"][$arProps["ID"]] = $arProps;
					}

				}
			}

		} //end while


		if (!empty($arID) && $arResult["DELIVERY_FIRST"]){

			$arOrder = array(
				"SITE_ID" => SITE_ID,
				"USER_ID" => $USER->GetID(),
				"CURRENCY" => $OPTION_CURRENCY,
				"ORDER_PRICE"  => $arResult["SUM"],
				"ORDER_WEIGHT" => $arResult["ORDER_WEIGHT"],
				"BASKET_ITEMS" => $arResult["TMP_ITEMS"],
				"PERSON_TYPE_ID" => $arResult["PERSON_FIRST"],
				"PAY_SYSTEM_ID" => $arResult["PAYSYSTEM_FIRST"],
				"DELIVERY_ID" => $arResult["DELIVERY_FIRST"]
			);

			$arOptions = array(
				"COUNT_DISCOUNT_4_ALL_QUANTITY" => "Y",
			);

			$arErrors = array();

			CSaleDiscount::DoProcessOrder($arOrder, $arOptions, $arErrors);

			$PRICE_ALL = 0;
			$DISCOUNT_PRICE_ALL = 0;
			$QUANTITY_ALL = 0;

			foreach ($arOrder["BASKET_ITEMS"] as $arItem){
				
				$arResult["ITEMS"][$arItem["PRODUCT_ID"]]["PRICE"] = $arItem["PRICE"];
				
				$PRICE_ALL += $arItem["PRICE"] * $arItem["QUANTITY"];
				$DISCOUNT_PRICE_ALL += $arItem["DISCOUNT_PRICE"] * $arItem["QUANTITY"];      
				$QUANTITY_ALL += $arItem['QUANTITY'];

			}

			$arResult["SUM"] = $PRICE_ALL;
			$arResult["DISCOUNT_PRICE_ALL"] = $DISCOUNT_PRICE_ALL;
			$arResult["QUANTITY_ALL"] = $QUANTITY_ALL;   

		}

		//коэффициент еденица измерения 
		$rsMeasure = CCatalogMeasure::getList(
			array(),
			array(
				"ID" => $arMeasureProductsID
			),
			false,
			false,
			false
		);
		
		while($arNextMeasure = $rsMeasure->Fetch()) {
			$arResult["MEASURES"][$arNextMeasure["ID"]] = $arNextMeasure;
		}

	}

	$this->IncludeComponentTemplate();
}

?>
