<?define("STOP_STATISTICS", true);?>
<?define("NO_AGENT_CHECK", true);?>
<?require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_before.php");?>
<?include("lang/".LANGUAGE_ID."/ajax.php");?>
<?if(!empty($_GET["act"]) && CModule::IncludeModule("sale") && CModule::IncludeModule("catalog") && CModule::IncludeModule("iblock")){
	
	// ini_set("error_reporting", E_ALL);
	// ini_set("display_errors", 1);
	// ini_set("display_startup_errors", 1);

	if($_GET["act"] == "upd"){

		//update basket quantity
		$addCartUpdateResult = CSaleBasket::Update(doubleval($_GET['id']), array(
		   "QUANTITY" => doubleval($_GET["q"]),
		   "DELAY" => "N"
		));
		
		if($addCartUpdateResult){
			echo $addCartUpdateResult;
		}

		else{
			echo "error addCart (product quantity limit!)".$addCartUpdateResult;
		}
	}

	elseif($_GET["act"] == "del"){
		echo CSaleBasket::Delete(doubleval($_GET['id']));
	}

	elseif($_GET["act"] == "emp"){
		echo CSaleBasket::DeleteAll(CSaleBasket::GetBasketUserID());
	}

	elseif($_GET["act"] == "coupon" && $_GET["value"]){
		$couponResult = CCatalogDiscountCoupon::SetCoupon($_GET["value"]);
		echo $couponResult === false ? CCatalogDiscountCoupon::ClearCoupon() : $couponResult;
	}

	elseif($_GET["act"] == "getFastBasketWindow"){
		$APPLICATION->IncludeComponent(
			"dresscode:basket.fast.order", 
			".default", 
			array(
				"SITE_ID" => !empty($_GET["site_id"]) ? $_GET["site_id"] : SITE_ID
			),
			false,
			Array(
				"HIDE_ICONS" => "Y"
			)
		);
	}

	// re calc delivery
	elseif ($_GET["act"] == "getProductPrices"){
	
		global $USER;

		$OPTION_CURRENCY  = CCurrency::GetBaseCurrency();

		$arID = array();
		$giftComponentResult = NULL;
		$arBasketOrder = array("PRICE" => "ASC");
		$arBasketUser = array("FUSER_ID" => CSaleBasket::GetBasketUserID(), "LID" => $_GET["SITE_ID"], "ORDER_ID" => "NULL");
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

			array_push($arID, $arItems["ID"]);

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
		    $arResult["ID"][] = $arItems["PRODUCT_ID"];
		}

	   $arOrder = array(
			"SITE_ID" => $_GET["SITE_ID"],
			"USER_ID" => $USER->GetID(),
			"ORDER_PRICE" => $arResult["SUM"],
			"CURRENCY" => $OPTION_CURRENCY,
			"ORDER_WEIGHT" => $arResult["ORDER_WEIGHT"],
			"BASKET_ITEMS" => $arResult["ITEMS"],
			"PERSON_TYPE_ID" => intval($_GET["PERSON_TYPE_ID"]),
			"PAY_SYSTEM_ID" => intval($_GET["PAY_SYSTEM_ID"]),
			"DELIVERY_ID" => intval($_GET["DELIVERY_ID"])
	   );

	   $arOptions = array(
	      "COUNT_DISCOUNT_4_ALL_QUANTITY" => "Y",
	   );
	   
	   $arErrors = array();
	   $allSum = 0;

		CSaleDiscount::DoProcessOrder($arOrder, $arOptions, $arErrors);

		if(!empty($arOrder["BASKET_ITEMS"])){ 
			foreach ($arOrder["BASKET_ITEMS"] as $arItem){
				
				$arItem["~PRICE"] = doubleval($arItem["PRICE"]);
				$arItem["~BASE_PRICE"] = $arItem["BASE_PRICE"];
				$arItem["SUM"] = FormatCurrency($arItem["PRICE"] * $arItem["QUANTITY"], $OPTION_CURRENCY);
				$arItem["PRICE"] = FormatCurrency($arItem["PRICE"], $OPTION_CURRENCY);
				$arItem["BASE_PRICE"] = FormatCurrency($arItem["BASE_PRICE"], $OPTION_CURRENCY);

				if(empty($arItem["PRICE_FORMATED"])){
					$arItem["PRICE_FORMATED"] = $arItem["PRICE"];
					$arItem["~DISCOUNT_PRICE"] = $arItem["DISCOUNT_PRICE"];
				}

				//комплект	
				$arComplectID = array();
				$arItem["COMPLECT"] = array();

				//коэффициент еденица измерения 
				$rsMeasure = CCatalogMeasure::getList(
					array(),
					array(
						"CODE" => $arItem["MEASURE_CODE"]
					),
					false,
					false,
					false
				);
				
				if($arNextMeasure = $rsMeasure->Fetch()) {
					$arItem["MEASURE_SYMBOL_RUS"] = $arNextMeasure["SYMBOL_RUS"];
				}


				$arReturnItems[$arItem["ID"]] = $arItem;
			}

			if(!empty($_GET["GIFT_PARAMS"])){

				ob_start();
				$giftParams = \Bitrix\Main\Web\Json::decode($_GET["GIFT_PARAMS"]);
				$APPLICATION->IncludeComponent("bitrix:sale.gift.basket", ".default", Array(
						"HIDE_NOT_AVAILABLE" => $giftParams["HIDE_NOT_AVAILABLE"],
						"PAGE_ELEMENT_COUNT" => $giftParams["PAGE_ELEMENT_COUNT"],
						"LINE_ELEMENT_COUNT" => $giftParams["LINE_ELEMENT_COUNT"],
						"PRODUCT_PRICE_CODE" => $giftParams["PRODUCT_PRICE_CODE"],
						"FULL_DISCOUNT_LIST" => $arOrder["FULL_DISCOUNT_LIST"],
						"CONVERT_CURRENCY" => $giftParams["CONVERT_CURRENCY"],
						"APPLIED_DISCOUNT_LIST" => $arOrder["DISCOUNT_LIST"],
						"HIDE_MEASURES" => $giftParams["HIDE_MEASURES"],
						"CACHE_GROUPS" => $giftParams["CACHE_GROUPS"],
						"CURRENCY_ID" => $giftParams["CURRENCY_ID"]
					),
					false
				);
				$giftComponentResult = ob_get_contents();
				ob_end_clean();

			}

		}

		echo \Bitrix\Main\Web\Json::encode(
			array(
				"ITEMS" => $arReturnItems,
				"GIFTS" => $giftComponentResult
			)
		);
	}

	elseif ($_GET["act"] == "reCalcDelivery") {
		
		global $USER;
		
		$FUSER_ID = CSaleBasket::GetBasketUserID();
		$OPTION_CURRENCY  = $arResult["BASE_LANG_CURRENCY"] = CCurrency::GetBaseCurrency();
		$SITE_ID = $_GET["SITE_ID"];


		CSaleBasket::UpdateBasketPrices($FUSER_ID, $SITE_ID);

		$res = CSaleBasket::GetList(
			array(
				"ID" => "ASC"
			),
			array(
					"FUSER_ID" => CSaleBasket::GetBasketUserID(),
					"LID" => $SITE_ID,
					"ORDER_ID" => "NULL"
				),
			false,
			false,
			array("ID", "CALLBACK_FUNC", "MODULE", "PRODUCT_ID", "QUANTITY", "DELAY",
				"CAN_BUY", "PRICE", "WEIGHT", "NAME", "CURRENCY", "CATALOG_XML_ID", "VAT_RATE",
				"NOTES", "DISCOUNT_PRICE", "PRODUCT_PROVIDER_CLASS", "DIMENSIONS", "TYPE", "SET_PARENT_ID", "DETAIL_PAGE_URL", "*"
			)
		);

		if($res->SelectedRowsCount() <= 0){
			exit(
				jsonEn(
					array(
						"ERROR" => GetMessage("ORDER_EMPTY")
					)
				)
			);
		}

		while ($arRes = $res->GetNext()){

			$ORDER_DISCOUNT  += ($arRes["QUANTITY"] * $arRes["DISCOUNT_PRICE"]);
			$ORDER_WEIGHT    += ($arRes["WEIGHT"] * $arRes["QUANTITY"]);
			$ORDER_PRICE     += ($arRes["PRICE"] * $arRes["QUANTITY"]);
			$ORDER_QUANTITY  += $arRes["QUANTITY"];
			$ORDER_MESSAGE   .= "<tr><td>".$arRes["NAME"]."</td><td>".$arRes["QUANTITY"]."</td><td>".SaleFormatCurrency($arRes["PRICE"], $arRes["CURRENCY"])." ".$arRes["CURRENCY"]."</td></tr>";

			if (!CSaleBasketHelper::isSetItem($arRes))
				$arResult["BASKET_ITEMS"][$arRes["ID"]] = $arRes;

			$arDim = $arRes["DIMENSIONS"] = $arRes["~DIMENSIONS"];

			if(is_array($arDim)){
				$arResult["MAX_DIMENSIONS"] = CSaleDeliveryHelper::getMaxDimensions(
					array(
						$arRes["WIDTH"],
						$arRes["HEIGHT"],
						$arRes["LENGTH"]
						),
					$arResult["MAX_DIMENSIONS"]);

				$arResult["ITEMS_DIMENSIONS"][] = $arDim;
			}

		}

		if(!empty($_GET["LOCATION_ID"])){
			
			$dbLoc = CSaleLocation::GetList(array(), array("ID" => $_GET["LOCATION_ID"]), false, false, array("*"));
			if($arLoc = $dbLoc->Fetch()){
				$arResult["LOCATION"] = $arLoc;
				$arUserResult["DELIVERY_LOCATION"] = $arLoc["ID"];
				$arUserResult["DELIVERY_LOCATION_BCODE"] = $arLoc["CODE"];
			}

			$arLocs = CSaleLocation::GetLocationZIP($arResult["LOCATION"]); 
			if(!empty($arLocs)){
				$arLocs = $arLocs->Fetch();
			}

			$locFrom = COption::GetOptionString("sale", "location", false, $SITE_ID);


			$dbPay = CSalePaySystem::GetList(
				$arOrder = Array(
					"SORT" => "ASC",
					"PSA_NAME" => "ASC"
				),
				Array(
					"ACTIVE" => "Y",
					"PERSON_TYPE_ID" => $_GET["PERSON_TYPE"]
				)
			);

			while ($arPay = $dbPay->Fetch()){
			
				if(empty($arResult["PAYSYSTEM"]["FIRST_ID"])){
					$arResult["PAYSYSTEM"]["FIRST_ID"] = $arPay["ID"];
				}
				
				$arResult["PAYSYSTEM"][$arPay["ID"]] = $arPay;
			
			}

			$_GET["PAYSYSTEM_ID"] = !empty($arResult["PAYSYSTEM"][$_GET["PAYSYSTEM_ID"]]) ? $_GET["PAYSYSTEM_ID"] : $arResult["PAYSYSTEM"]["FIRST_ID"];

			//adaptive to standart order

			$arResult["ORDER_WEIGHT"] = $ORDER_WEIGHT;
			$arResult["SUM"] = $ORDER_PRICE;
			$arResult["ITEMS"] = $arResult["BASKET_ITEMS"];

			$arUserResult["PERSON_TYPE_ID"] = $_GET["PERSON_TYPE"];
			$arUserResult["PAY_SYSTEM_ID"] = IntVal($_GET["PAYSYSTEM_ID"]);

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

			$shipment = CSaleDelivery::convertOrderOldToNew(array(
				"SITE_ID" => $SITE_ID,
				"WEIGHT" => $ORDER_WEIGHT,
				"PRICE" =>  $ORDER_PRICE,
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
					$arNextDelivery["PRICE"] = intval($deliveryPriceCalculate->getPrice());
					$arNextDelivery["PRICE_FORMATED"] = SaleFormatCurrency($deliveryPriceCalculate->getPrice(), $arResult["BASE_LANG_CURRENCY"]);
					$arNextDelivery["PRICE_FORMATED"] = str_replace("-", ".", $arNextDelivery["PRICE_FORMATED"]);
					$arNextDelivery["CURRENCY"] = $arResult["BASE_LANG_CURRENCY"];
					
					if(!$deliveryPriceCalculate->isSuccess()){
						foreach($deliveryPriceCalculate->getErrorMessages() as $message){
							$arResult["ERRORS"][] = "component: sale.basket.basket ".$message."(delivery id: ".$arNextDelivery["ID"].") - (personTypeId: ".$arUserResult["PERSON_TYPE_ID"].")";
						}
					}else{
						$arResult["DELIVERY"][$arNextDelivery["ID"]] = $arNextDelivery;
					}

				}
			}

			if(!empty($arResult["DELIVERY"][intval($_GET["DELISYSTEM_ID"])])){
				$arDumpActiveDeli = $arResult["DELIVERY"][intval($_GET["DELISYSTEM_ID"])];
				unset($arResult["DELIVERY"][intval($_GET["DELISYSTEM_ID"])]);
				array_unshift($arResult["DELIVERY"], $arDumpActiveDeli);
			}
			
			echo \Bitrix\Main\Web\Json::encode($arResult["DELIVERY"]);

		}else{
			exit(
				jsonEn(
					array(
						"ERROR" => "Delivery error (3); Check field IS_LOCATION!!!."
					)
				)
			);
		}
	
	}

	##### ORDER #####

	elseif($_GET["act"] == "location" && !empty($_GET["q"])){

		$LOCATIONS = array();
		$CITY_NAME = (BX_UTF == 1) ? $_GET["q"] : iconv("UTF-8", "CP1251//IGNORE", $_GET["q"]);

		$dbLocations = CSaleLocation::GetList(
			array(
				"SORT" => "ASC",
				"COUNTRY_NAME_LANG" => "ASC",
				"CITY_NAME_LANG" => "ASC"
			),
			array(
				"LID" => LANGUAGE_ID,
				"%CITY_NAME" => $CITY_NAME
			),
			false,
			Array(
				"nPageSize" => 5,
			),
			array("*")
		);
		while ($arLoc = $dbLocations->Fetch()){
			if(!empty($arLoc["CITY_NAME"])){
				$arLoc["REGION_NAME"] = !empty($arLoc["REGION_NAME"]) ? $arLoc["REGION_NAME"].", " : "";
				$LOCATIONS[$arLoc["ID"]] = $arLoc["COUNTRY_NAME"].", ".$arLoc["REGION_NAME"].$arLoc["CITY_NAME"];
			}
		}
		echo jsonEn($LOCATIONS);
	}

	##### ORDER MAKE #####

	elseif ($_GET["act"] == "orderMake") {
		
		global $USER;

		$FUSER_ID = CSaleBasket::GetBasketUserID(True);
		$OPTION_CURRENCY  = $arResult["BASE_LANG_CURRENCY"] = CCurrency::GetBaseCurrency();
		$SITE_ID = $_GET["SITE_ID"];
		$DELIVERY_ID = intval($_GET["DEVIVERY_TYPE"]);
		$DELIVERY_CODE = !empty($_GET["DEVIVERY_TYPE"]) ? \Bitrix\Sale\Delivery\Services\Table::getCodeById($_GET["DEVIVERY_TYPE"]) : null;
		$errorMessage = "";
		$profileName = "";
		$PROFILE_ID = "";
		$SAVE_FIELDS = TRUE;
		
		if(!empty($_GET["USER_NAME"])){
			$USER_NAME = explode(" ", $_GET["USER_NAME"]);
			$profileName = $_GET["USER_NAME"];
		}

		if(!empty($_GET["PERSONAL_MOBILE"])){
			$PERSONAL_MOBILE = $_GET["PERSONAL_MOBILE"];
		}

		if(!empty($_GET["PERSONAL_ADDRESS"])){
			$PERSONAL_ADDRESS = $_GET["PERSONAL_ADDRESS"];
		}

		$db_props = CSaleOrderProps::GetList(
	        array("SORT" => "ASC"),
	        array(
	                "PERSON_TYPE_ID" => intval($_GET["PERSON_TYPE"]),
	                "IS_EMAIL" => "Y",
	                "CODE" => "EMAIL"
	            ),
	        false,
	        false,
	        array()
	    );

		if ($props = $db_props->Fetch()){
			if($props["REQUIED"] == "Y"){
				$OPTION_REGISTER = "Y";
			}
		}

		if(!$USER->IsAuthorized()){
			if($OPTION_REGISTER == "Y"){
				$arResult = $USER->SimpleRegister($_GET["email"]);
				if($arResult["TYPE"] == "ERROR"){
					exit(
						jsonEn(
							array(
								"ERROR" => $arResult["MESSAGE"]
							)
						)
					);
				}
				// else{
				// 	CUser::SendUserInfo($USER->GetID(), $_GET["SITE_ID"], GetMessage("NEW_REGISTER"), true);
				// }
			}else{

				$rsUser = CUser::GetByLogin("unregistered");
				$arUser = $rsUser->Fetch();
				if(!empty($arUser)){
					$USER_ID = $arUser["ID"];
				}else{

					$newUser = new CUser;
					$newPass = rand(0, 999999999);
					$arUserFields = Array(
					  "NAME"              => "unregistered",
					  "LAST_NAME"         => "unregistered",
					  "EMAIL"             => "unregistered@unregistered.com",
					  "LOGIN"             => "unregistered",
					  "LID"               => "ru",
					  "ACTIVE"            => "Y",
					  "GROUP_ID"          => array(),
					  "PASSWORD"          => $newPass,
					  "CONFIRM_PASSWORD"  => $newPass,
					);
					
					$USER_ID = $newUser->Add($arUserFields);
				}
				$SAVE_FIELDS = false;
			}
		}

		if(!empty($USER_NAME) && count($USER_NAME) > 0){

			if(!empty($USER_NAME[1])){
				$fields["NAME"] = BX_UTF == true ? $USER_NAME[1] : iconv("UTF-8","windows-1251//IGNORE", $USER_NAME[1]);
			}
			
			if(!empty($USER_NAME[0])){
				$fields["LAST_NAME"] = BX_UTF == true ? $USER_NAME[0] : iconv("UTF-8","windows-1251//IGNORE", $USER_NAME[0]);
			}

			if(!empty($USER_NAME[2])){
				$fields["SECOND_NAME"] = BX_UTF == true ? $USER_NAME[2] : iconv("UTF-8","windows-1251//IGNORE", $USER_NAME[2]);
			}

		}

		if(!empty($PERSONAL_MOBILE)){
			$fields["PERSONAL_MOBILE"] = BX_UTF == true ? $PERSONAL_MOBILE : iconv("UTF-8","windows-1251//IGNORE", $PERSONAL_MOBILE);
		}

		if(!empty($PERSONAL_ADDRESS)){
			$fields["PERSONAL_STREET"] = BX_UTF == true ? $PERSONAL_ADDRESS : iconv("UTF-8","windows-1251//IGNORE", $PERSONAL_ADDRESS);
		}
		
		$user = new CUser;
		$user->Update($USER->GetID(), $fields);

		$ORDER_PRICE    = 0;
		$ORDER_QUANTITY = 0;
		$ORDER_DISCOUNT = 0;
		$ORDER_WEIGHT   = 0;
		$ORDER_MESSAGE  = "<tr><td>".GetMessage("TOP_NAME")."</td><td>".GetMessage("TOP_QTY")."</td><td>".GetMessage("PRICE")."</td></tr>";

		CSaleBasket::UpdateBasketPrices($FUSER_ID, $SITE_ID);

		$arID = array();
		$arBasketItems = array();
		$arBasketOrder = array("NAME" => "ASC", "ID" => "ASC");
		$arBasketUser = array("FUSER_ID" => $FUSER_ID, "LID" => $SITE_ID, "ORDER_ID" => "NULL");
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

			CSaleBasket::UpdatePrice(
				$arItems["ID"],
				$arItems["CALLBACK_FUNC"],
				$arItems["MODULE"],
				$arItems["PRODUCT_ID"],
				$arItems["QUANTITY"],
				"N",
				CSaleBasket::GetProductProvider($arItems)
			);

			array_push($arID, $arItems["ID"]);

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

			if($dbBasketItems->SelectedRowsCount() <= 0){
				exit(
					jsonEn(
						array(
							"ERROR" => GetMessage("ORDER_EMPTY")
						)
					)
				);
			}

			while ($arItems = $dbBasketItems->Fetch()){
			    $arResult["SUM"]    += ($arItems["PRICE"]  * $arItems["QUANTITY"]);
			    $arResult["ORDER_WEIGHT"] += ($arItems["WEIGHT"] * $arItems["QUANTITY"]);
			    $arResult["ITEMS"][$arItems["PRODUCT_ID"]] = $arItems;
			    $arResult["ID"][] = $arItems["PRODUCT_ID"];
			}
		 
			$arOrder = array(
				"SITE_ID" => $SITE_ID,
				"USER_ID" => $USER_ID,
				"ORDER_PRICE" => $arResult["SUM"],
				"ORDER_WEIGHT" => $arResult["ORDER_WEIGHT"],
				"BASKET_ITEMS" => $arResult["ITEMS"],
				"PERSON_TYPE_ID" => intval($_GET["PERSON_TYPE"]),
				"PAY_SYSTEM_ID" => intval($_GET["PAY_TYPE"]),
				"DELIVERY_ID" => $DELIVERY_ID
			);

			$arOptions = array(
				"COUNT_DISCOUNT_4_ALL_QUANTITY" => "Y",
			);

			$arErrors = array();

			CSaleDiscount::DoProcessOrder($arOrder, $arOptions, $arErrors);

			foreach ($arResult["ITEMS"] as $arItem){

				$ORDER_DISCOUNT  += ($arItem["QUANTITY"] * $arItem["DISCOUNT_PRICE"]);
				$ORDER_WEIGHT    += ($arItem["WEIGHT"] * $arItem["QUANTITY"]);
				$ORDER_PRICE     += ($arItem["PRICE"] * $arItem["QUANTITY"]);
				$ORDER_QUANTITY  += $arItem["QUANTITY"];
				$ORDER_MESSAGE   .= "<tr><td>".$arItem["NAME"]."</td><td>".$arItem["QUANTITY"]."</td><td>".SaleFormatCurrency($arItem["PRICE"], $arItem["CURRENCY"])." ".$arItem["CURRENCY"]."</td></tr>";

				// if (!CSaleBasketHelper::isSetItem($arItem))
				// 	$arResult["BASKET_ITEMS"][$arRes["ID"]] = $arItem;

				$arDim = $arItem["DIMENSIONS"] = $arItem["~DIMENSIONS"];

				if(is_array($arDim)){
					$arResult["MAX_DIMENSIONS"] = CSaleDeliveryHelper::getMaxDimensions(
					array(
						$arItem["WIDTH"],
						$arItem["HEIGHT"],
						$arItem["LENGTH"]
					),
					$arResult["MAX_DIMENSIONS"]);

					$arResult["ITEMS_DIMENSIONS"][] = $arDim;
				}

			}


			if(!empty($_GET["location"])){
				
				$dbLoc = CSaleLocation::GetList(array(), array("ID" => $_GET["location"]), false, false, array("*"));
				if($arLoc = $dbLoc->Fetch()){
					$arResult["LOCATION"] = $arLoc;
					$arUserResult["DELIVERY_LOCATION_ID"] = $arLoc["ID"];
					$arUserResult["DELIVERY_LOCATION"] = $arLoc["CODE"];			}

				$arLocs = CSaleLocation::GetLocationZIP($arUserResult["DELIVERY_LOCATION_ID"]); 
				if(!empty($arLocs)){
					$arLocs = $arLocs->Fetch();
				}

				$locFrom = COption::GetOptionString("sale", "location", false, $SITE_ID);

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
				
				$arUserResult["PERSON_TYPE_ID"] = intval($_GET["PERSON_TYPE"]);
				$arUserResult["PAY_SYSTEM_ID"] = intval($_GET["PAY_TYPE"]);

				$shipment = CSaleDelivery::convertOrderOldToNew(array(
					"SITE_ID" => $SITE_ID,
					"WEIGHT" => $ORDER_WEIGHT,
					"PRICE" =>  $ORDER_PRICE,
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
							$arResult["DELIVERY"][$arNextDelivery["ID"]] = $arNextDelivery;
						}
					}
				}
			}else{
				exit(
					jsonEn(
						array(
							"ERROR" => "Delivery error (3); Check field IS_LOCATION!!!."
						)
					)
				);
			}

			$DELIVERY_INFO = $arResult["DELIVERY"][$DELIVERY_ID];

			if(!empty($DELIVERY_INFO)){

				foreach ($_GET as $i => $prop_value) {
					if(strstr($i, "ORDER_PROP")){

						$nextProp = CSaleOrderProps::GetByID(
							preg_replace('/[^0-9]/', '', $i)
						);

						if($nextProp["IS_LOCATION"] == "Y"){
							$prop_value = $_GET["location"];
						}

						$arResult["ORDER_PROP"][$nextProp["ID"]] = (BX_UTF == 1) ? $prop_value : iconv("UTF-8", "windows-1251//IGNORE", $prop_value);

					}
				}

				$arOrderDat = CSaleOrder::DoCalculateOrder(
					$SITE_ID,
					!empty($USER_ID) ? $USER_ID : IntVal($USER->GetID()),
					$arResult["ITEMS"],
					$_GET["PERSON_TYPE"],
					$arResult["ORDER_PROP"],
					$DELIVERY_CODE,
					$_GET["PAY_TYPE"],
					array(),
					$arErrors,
					$arWarnings
				);

				if(empty($arErrors)){

					$arOrderFields = array(
					   "LID" => $SITE_ID,
					   "PERSON_TYPE_ID" => $_GET["PERSON_TYPE"],
					   "PAYED" => "N",
					   "CANCELED" => "N",
					   "STATUS_ID" => "N",
					   "PRICE" => ($DELIVERY_INFO["PRICE"] + $ORDER_PRICE),
					   "CURRENCY" => $OPTION_CURRENCY,
					   "USER_ID" => !empty($USER_ID) ? $USER_ID : IntVal($USER->GetID()),
					   "PAY_SYSTEM_ID" => $_GET["PAY_TYPE"],
					   "PRICE_DELIVERY" => $DELIVERY_INFO["PRICE"],
					   "DELIVERY_ID" => $DELIVERY_CODE,
					   "DISCOUNT_VALUE" => $ORDER_DISCOUNT,
					   "TAX_VALUE" => 0.0,
					   "USER_DESCRIPTION" => (BX_UTF == 1) ? $_GET["COMMENT"] : iconv("UTF-8", "windows-1251//IGNORE", $_GET["COMMENT"])
					);
					
					$ORDER_ID = (int)CSaleOrder::DoSaveOrder($arOrderDat, $arOrderFields, 0, $arResult["ERROR"]);
					
					if(!empty($arResult["ERROR"])){
						exit(
							jsonEn(
								array(
									"ERROR" => $arResult["ERROR"]
								)
							)
						);	
					}

					if(empty($ORDER_ID)){
						exit(
							jsonEn(
								array(
									"ERROR" => GetMessage("ORDER_ERROR")
								)
							)
						);
					}

					$orderInfo = CSaleOrder::GetByID($ORDER_ID);
			
					CSaleBasket::OrderBasket(
						intval($ORDER_ID), intval($_SESSION["SALE_USER_ID"]), $SITE_ID, false
					);


					$PAYSYSTEM = CSalePaySystem::GetByID(
						$_GET["PAY_TYPE"],
						$_GET["PERSON_TYPE"]
					);
					
					$res = CSalePaySystemAction::GetList(
						array(),
						array(
								"PAY_SYSTEM_ID" => $PAYSYSTEM["ID"],
								"PERSON_TYPE_ID" => $_GET["PERSON_TYPE"]
							),
						false,
						false,
						array("ID", "PAY_SYSTEM_ID", "PERSON_TYPE_ID", "NAME", "ACTION_FILE", "RESULT_FILE", "NEW_WINDOW", "PARAMS", "ENCODING", "LOGOTIP")
					);

					if ($PAYSYSTEM_ACTION = $res->Fetch()){
						$dbOrder = CSaleOrder::GetList(
							array("DATE_UPDATE" => "DESC"),
							array(
								"LID" => $SITE_ID,
								"ID" => $ORDER_ID
							)
						);
						if($arOrder = $dbOrder->GetNext()){
							CSalePaySystemAction::InitParamArrays($arOrder, $arOrder["ID"], $PAYSYSTEM_ACTION["PARAMS"]);
							$PAY_DATA = returnBuff($_SERVER["DOCUMENT_ROOT"].$PAYSYSTEM_ACTION["ACTION_FILE"]."/payment.php");
							echo jsonEn(
								array(
									"ORDER_ID" => $orderInfo["ACCOUNT_NUMBER"],
									"NEW_WINDOW" => $PAYSYSTEM_ACTION["NEW_WINDOW"],
									"PAYSYSTEM" => trim(
										str_replace(
											array("\n", "\r", "\t"), "", $PAY_DATA)
									)
								)
							);
						}
					}
				
					$arFields = Array(
						"ORDER_ID" => $orderInfo["ACCOUNT_NUMBER"],
						"ORDER_DATE" => Date($DB->DateFormatToPHP(CLang::GetDateFormat("SHORT", $SITE_ID))),
						"ORDER_USER" => $USER->GetFormattedName(false),
						"PRICE" => SaleFormatCurrency($ORDER_PRICE, $OPTION_CURRENCY),
						"BCC" => COption::GetOptionString("sale", "order_email", "order@".$SERVER_NAME),
						"EMAIL" => !empty($_GET["email"]) ? $_GET["email"] : $USER->GetEmail(),
						"ORDER_LIST" => "<table width=100%>".$ORDER_MESSAGE."</table>",
						"SALE_EMAIL" => COption::GetOptionString("sale", "order_email", "order@".$SERVER_NAME),
						"DELIVERY_PRICE" => $DELIVERY_INFO["PRICE"],
					);

					$eventName = "SALE_NEW_ORDER";

					$bSend = true;
					foreach (GetModuleEvents("sale", "OnOrderNewSendEmail", true) as $arEvent){
						if (ExecuteModuleEventEx($arEvent, Array($ORDER_ID, &$eventName, &$arFields))===false)
							$bSend = false;
					}

					if($bSend){
						$event = new CEvent;
						$event->Send($eventName, $SITE_ID, $arFields, "N");
					}

					$db_sales = CSaleOrderUserProps::GetList(
						array("DATE_UPDATE" => "DESC"),
						array("USER_ID" => !empty($USER_ID) ? $USER_ID : IntVal($USER->GetID())),
					false,
					array("nTopCount" => 1)   
					);

					while ($ar_sales = $db_sales->Fetch()){
						$PROFILE_ID = $ar_sales['ID'];   
					}

					CSaleOrderUserProps::DoSaveUserProfile(
						!empty($USER_ID) ? $USER_ID : IntVal($USER->GetID()),
						$PROFILE_ID,
						$profileName,
						$_GET["PERSON_TYPE"],
						$arResult["ORDER_PROP"],
						$arErrors
					);

				}else{
					foreach($arErrors as $nextError){
						$errorMessage .= $nextError["TEXT"]."<br>";
					}
					exit(
						jsonEn(
							array(
								"ERROR" => $errorMessage
							)
						)
					);					
				}
			}else{
				exit(
					jsonEn(
						array(
							"ERROR" => "Delivery error (4); Check logo delivery system please."
						)
					)
				);
			}
		}else{
			exit(
				jsonEn(
					array(
						"ERROR" => "You basket is empty!"
					)
				)
			);			
		}

	}
}else{
	die(false);
}

function jsonEn($data){
	foreach ($data as $index => $arValue) {
		$arJsn[] = '"'.$index.'" : "'.addslashes($arValue).'"';
	}
	return  "{".implode($arJsn, ",")."}";
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

function getJnLevel($data = array(), $level = 1, $arJsn = array()){
	if(!empty($data)){
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
	}
	return $arJsn;
}

function returnBuff($file){
	ob_start();
	include($file);
	$fData = ob_get_contents();
	ob_end_clean();
	return $fData;
}

?>