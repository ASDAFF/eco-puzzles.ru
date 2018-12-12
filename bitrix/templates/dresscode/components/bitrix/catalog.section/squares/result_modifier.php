<?
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED!==true) die();

	CModule::IncludeModule('highloadblock');
	use Bitrix\Highloadblock as HL;
	use Bitrix\Main\Entity;

	$OPTION_ADD_CART  = COption::GetOptionString("catalog", "default_can_buy_zero");
	$OPTION_PRICE_TAB = COption::GetOptionString("catalog", "show_catalog_tab_with_offers");
	$OPTION_CURRENCY  = CCurrency::GetBaseCurrency();

	if(!empty($arResult["ITEMS"])){

		if(empty($arParams["OFFERS_SORT_FIELD"])){
			$arParams["OFFERS_SORT_FIELD"] = "sort";
		}

		if(empty($arParams["OFFERS_SORT_ORDER"])){
			$arParams["OFFERS_SORT_ORDER"] = "desc";
		}

		$COLOR_PROPERTY_NANE = "COLOR";
		$SKU_INFO = CCatalogSKU::GetInfoByProductIBlock($arParams["IBLOCK_ID"]);

		$arResult["PRODUCT_PRICE_ALLOW"] = array();
		$arResult["PRODUCT_PRICE_ALLOW_FILTER"] = array();

		if(!empty($arParams["PRICE_CODE"])){
			$dbPriceType = CCatalogGroup::GetList(
		        array("SORT" => "ASC"),
		        array("NAME" => $arParams["PRICE_CODE"])
		    );
			while ($arPriceType = $dbPriceType->Fetch()){
				if($arPriceType["CAN_BUY"] == "Y"){
			    	$arResult["PRODUCT_PRICE_ALLOW"][] = $arPriceType;
				}
			    $arResult["PRODUCT_PRICE_ALLOW_FILTER"][] = $arPriceType["ID"];
			}
		}

		if(is_array($SKU_INFO)){
			$properties = CIBlockProperty::GetList(Array("sort" => "asc", "name" => "asc"), Array("ACTIVE" => "Y", "IBLOCK_ID" => $SKU_INFO["IBLOCK_ID"]));
			while ($prop_fields = $properties->GetNext()){
				if($prop_fields["SORT"] <= 100 && $prop_fields["PROPERTY_TYPE"] == "L"){
					$propValues = array();
					$prop_fields["HIGHLOAD"] = "N";
					$property_enums = CIBlockPropertyEnum::GetList(Array("SORT" => "ASC", "DEF" => "DESC"), Array("IBLOCK_ID" => $SKU_INFO["IBLOCK_ID"], "CODE" => $prop_fields["CODE"]));
					while($enum_fields = $property_enums->GetNext()){
						$propValues[$enum_fields["VALUE"]] = array(
							"VALUE"  => $enum_fields["VALUE"],
							"DISPLAY_VALUE"  => $enum_fields["VALUE"],
							"SELECTED"  => N,
							"DISABLED"  => N,
							"HIGHLOAD" => N
						);
					}
					$arResult["PROPERTIES"][$prop_fields["CODE"]] = array_merge(
						$prop_fields, array("VALUES" => $propValues)
					);
				}elseif($prop_fields["SORT"] <= 100 && $prop_fields["PROPERTY_TYPE"] == "S" && !empty($prop_fields["USER_TYPE_SETTINGS"]["TABLE_NAME"])){
					$propValues = array();
					$prop_fields["HIGHLOAD"] = "Y";
					$hlblock = \Bitrix\Highloadblock\HighloadBlockTable::getList(
				        array("filter" => array(
				            "TABLE_NAME" => $prop_fields["USER_TYPE_SETTINGS"]["TABLE_NAME"]
				        ))
				    )->fetch();

				    if(!empty($hlblock)){

						$hlblock_requests = HL\HighloadBlockTable::getById($hlblock["ID"])->fetch();
						$entity_requests = HL\HighloadBlockTable::compileEntity($hlblock_requests);
						$entity_requests_data_class = $entity_requests->getDataClass();

						$main_query_requests = new Entity\Query($entity_requests_data_class);
						$main_query_requests->setSelect(array("*"));
						$result_requests = $main_query_requests->exec();
						$result_requests = new CDBResult($result_requests);

						while ($row_requests = $result_requests->Fetch()) {

							if(!empty($row_requests["UF_FILE"])){
	 							$row_requests["UF_FILE"] = CFile::ResizeImageGet($row_requests["UF_FILE"], array("width" => 30, "height" => 30), BX_RESIZE_IMAGE_PROPORTIONAL, false);
								$hasPicture = true;
							}

							$propValues[$row_requests["UF_XML_ID"]] = array(
								"VALUE" => $row_requests["UF_XML_ID"],
								"DISPLAY_VALUE"  => $row_requests["UF_NAME"],
								"SELECTED" => N,
								"DISABLED" => N,
								"UF_XML_ID" => $row_requests["UF_XML_ID"],
								"IMAGE" => $row_requests["UF_FILE"],
								"NAME" => $row_requests["UF_NAME"],
								"HIGHLOAD" => "Y"
							);

						}

						$prop_fields["HIGHLOAD"] = "Y";
						$arResult["PROPERTIES"][$prop_fields["CODE"]] = array_merge(
							$prop_fields, array("VALUES" => $propValues)
						);

						// print_r($requests);

					}
				}elseif($prop_fields["SORT"] <= 100 && $prop_fields["PROPERTY_TYPE"] == "E" && !empty($prop_fields["LINK_IBLOCK_ID"])){
					$rsLinkElements = CIBlockElement::GetList(array(), array("IBLOCK_ID" => $prop_fields["LINK_IBLOCK_ID"], "ACTIVE" => "Y"), false, false, array("ID", "NAME", "DETAIL_PICTURE"));
					while ($arNextLinkElement = $rsLinkElements->GetNext()){
						if(!empty($arNextLinkElement["DETAIL_PICTURE"])){
 							$arNextLinkElement["UF_FILE"] = CFile::ResizeImageGet($arNextLinkElement["DETAIL_PICTURE"], array("width" => 30, "height" => 30), BX_RESIZE_IMAGE_PROPORTIONAL, false);
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
							"SELECTED" => N,
							"DISABLED" => N,
						);
					}
					$prop_fields["TYPE"] = "E";
					$arResult["PROPERTIES"][$prop_fields["CODE"]] = array_merge(
						$prop_fields, array("VALUES" => $propValues)
					);
				}
			}
		}

		foreach ($arResult["ITEMS"] as $index => $arElement){

			$arButtons = CIBlock::GetPanelButtons(
				$arElement["IBLOCK_ID"],
				$arElement["ID"],
				$arElement["ID"],
				array("SECTION_BUTTONS" => false,
					  "SESSID" => false,
					  "CATALOG" => true
				)
			);

			$arElement["SKU"] = CCatalogSKU::IsExistOffers($arElement["ID"]);
			if($arElement["SKU"]){
				if(empty($SKU_INFO)){
					$SKU_INFO = CCatalogSKU::GetInfoByProductIBlock($arElement["IBLOCK_ID"]);
				}
				if (is_array($SKU_INFO)){
					$arOffersFilter = array("IBLOCK_ID" => $SKU_INFO["IBLOCK_ID"], "PROPERTY_".$SKU_INFO["SKU_PROPERTY_ID"] => $arElement["ID"], "ACTIVE" => "Y");
					if($arParams["HIDE_NOT_AVAILABLE"] == "Y"){
						$arOffersFilter[">CATALOG_QUANTITY"] = 0;
					}
					$rsOffers = CIBlockElement::GetList(array($arParams["OFFERS_SORT_FIELD"] => $arParams["OFFERS_SORT_ORDER"]), $arOffersFilter, false, false, array("ID", "IBLOCK_ID", "DETAIL_PAGE_URL", "DETAIL_PICTURE", "NAME", "CATALOG_QUANTITY", "CATALOG_MEASURE", "CATALOG_AVAILABLE")); 
					while($arSku = $rsOffers->GetNextElement()){

						$arSkuFields = $arSku->GetFields();
						$arSkuProperties = $arSku->GetProperties();

						if(!empty($arResult["PRODUCT_PRICE_ALLOW"])){
							$arPriceCodes = array();
							foreach($arResult["PRODUCT_PRICE_ALLOW"] as $ipc => $arNextAllowPrice){
								$dbPrice = CPrice::GetList(
							        array(),
							        array(
							            "PRODUCT_ID" => $arSkuFields["ID"],
							            "CATALOG_GROUP_ID" => $arNextAllowPrice["ID"]
							        )
							    );
								if($arPriceValues = $dbPrice->Fetch()){
									$arPriceCodes[] = array(
										"ID" => $arNextAllowPrice["ID"],
										"PRICE" => $arPriceValues["PRICE"],
										"CURRENCY" => $arPriceValues["CURRENCY"],
										"CATALOG_GROUP_ID" => $arNextAllowPrice["ID"]
									);
								}
							}
						}

						if(!empty($arResult["PRODUCT_PRICE_ALLOW"]) && !empty($arPriceCodes) || empty($arParams["PRICE_CODE"]))
							$arSkuFields["PRICE"] = CCatalogProduct::GetOptimalPrice($arSkuFields["ID"], 1, $USER->GetUserGroupArray(), "N", $arPriceCodes);

						$arElement["SKU_PRODUCT"][] = array_merge($arSkuFields, array("PROPERTIES" => $arSkuProperties));

						$arElement["SKU_PRICES"][] = $arSkuPrice["DISCOUNT_PRICE"];
					}

					$arElement["ADDSKU"] = $OPTION_ADD_CART === "Y" ? true : $arElement["CATALOG_QUANTITY"] > 0;
					$arElement["SKU_INFO"] = $SKU_INFO;
				}
			}

			$arResult["ITEMS"][$index]["EDIT_LINK"] = $arButtons["edit"]["edit_element"]["ACTION_URL"];
			$arResult["ITEMS"][$index]["DELETE_LINK"] = $arButtons["edit"]["delete_element"]["ACTION_URL"];

			if(!empty($arElement["SKU_PRODUCT"]) && !empty($arResult["PROPERTIES"])){
				$arElement["SKU_PROPERTIES"] = $arResult["PROPERTIES"];
				foreach ($arElement["SKU_PROPERTIES"] as $ip => $arProp) {
					foreach ($arProp["VALUES"] as $ipv => $arPropValue) {
						$find = false;;
						foreach ($arElement["SKU_PRODUCT"] as $ipo => $arOffer) {
							if($arProp["HIGHLOAD"] == "Y"){
								if($arOffer["PROPERTIES"][$arProp["CODE"]]["VALUE"] == $arPropValue["UF_XML_ID"]){
									$find = true;
									break(1);
								}
							}else{
								if($arOffer["PROPERTIES"][$arProp["CODE"]]["VALUE"] == $arPropValue["VALUE"]){
									$find = true;
									break(1);
								}
							}
						}
						if(!$find){
							unset($arElement["SKU_PROPERTIES"][$ip]["VALUES"][$ipv]);
						}
					}
				}

				// first display

				$arPropClean = array();
				$iter = 0;

				foreach ($arElement["SKU_PROPERTIES"] as $ip => $arProp) {
					if(!empty($arProp["VALUES"])){
						$arKeys = array_keys($arProp["VALUES"]);
						$selectedUse = false;
						if($iter === 0){
							$arElement["SKU_PROPERTIES"][$ip]["VALUES"][$arKeys[0]]["SELECTED"] = Y;
							$arPropClean[$ip] = array(
								"PROPERTY" => $ip,
								"VALUE"    => $arKeys[0],
								"HIGHLOAD" => $arProp["HIGHLOAD"]
							);
						}else{
							foreach ($arKeys as $key => $keyValue) {
								$disabled = true;
								$checkValue = $arElement["SKU_PROPERTIES"][$ip]["VALUES"][$keyValue]["VALUE"];

								foreach ($arElement["SKU_PRODUCT"] as $io => $arOffer) {
									if($arOffer["PROPERTIES"][$ip]["VALUE"] == $checkValue){
										$disabled = false; $selected = true;
										foreach ($arPropClean as $ic => $arNextClean) {
											if($arNextClean["HIGHLOAD"] == "Y" && $arOffer["PROPERTIES"][$arNextClean["PROPERTY"]]["VALUE"] != $arNextClean["VALUE"] || $arNextClean["HIGHLOAD"] != "Y" && $arOffer["PROPERTIES"][$arNextClean["PROPERTY"]]["VALUE"] != $arNextClean["VALUE"]){
												if($ic == $ip){
													break(2);
												}
												$disabled = true;
												$selected = false;
												break(1);
											}
										}
										if($selected && !$selectedUse){
											$selectedUse = true;
											$arElement["SKU_PROPERTIES"][$ip]["VALUES"][$keyValue]["SELECTED"] = Y;
											$arPropClean[$ip] = array(
												"PROPERTY" => $ip,
												"VALUE"    => $keyValue,
												"HIGHLOAD" => $arProp["HIGHLOAD"]
											);
											break(1);
										}
									}
								}
								if($disabled){
									$arElement["SKU_PROPERTIES"][$ip]["VALUES"][$keyValue]["DISABLED"] = "Y";
								}
							}
						}
						$iter++;
					}
				}

				if(!empty($arElement["SKU_PROPERTIES"][$COLOR_PROPERTY_NANE])){
					foreach ($arElement["SKU_PROPERTIES"][$COLOR_PROPERTY_NANE]["VALUES"] as $ic => $arProperty) {
						foreach ($arElement["SKU_PRODUCT"] as $io => $arOffer) {
							if($arOffer["PROPERTIES"][$COLOR_PROPERTY_NANE]["VALUE"] == $arProperty["VALUE"]){
								if(!empty($arOffer["DETAIL_PICTURE"])){
									$arPropertyFile = CFile::GetFileArray($arOffer["DETAIL_PICTURE"]);
									$arPropertyImage = CFile::ResizeImageGet($arPropertyFile, array('width' => 30, 'height' => 30), BX_RESIZE_IMAGE_PROPORTIONAL, false, false, false, 80);
									$arElement["SKU_PROPERTIES"][$COLOR_PROPERTY_NANE]["VALUES"][$ic]["IMAGE"] = $arPropertyImage;
									break(1);
								}
							}
						}
					}
				}

				foreach ($arElement["SKU_PRODUCT"] as $ir => $arOffer) {
					$active = true;
					foreach ($arPropClean as $ic => $arNextClean) {
						if($arNextClean["HIGHLOAD"] == "Y" || $arResult["PROPERTIES"][$ic]["TYPE"] == "E"){
							if($arOffer["PROPERTIES"][$arNextClean["PROPERTY"]]["VALUE"] != $arNextClean["VALUE"]){
								$active = false;
								break(1);
							}
						}else{
							if($arOffer["PROPERTIES"][$arNextClean["PROPERTY"]]["VALUE"] != $arNextClean["VALUE"]){
								$active = false;
								break(1);
							}
						}
					}
					if($active){

						if(!empty($arOffer["DETAIL_PICTURE"])){
							$arElement["DETAIL_PICTURE"] = $arOffer["DETAIL_PICTURE"];
						}

						// if(!empty($arOffer["NAME"])){
						// 	$arElement["NAME"] = $arOffer["NAME"];
						// }

						if(!empty($arOffer["PROPERTIES"]["MORE_PHOTO"]["VALUE"])){
							foreach ($arOffer["PROPERTIES"]["MORE_PHOTO"]["VALUE"] as $impr => $arMorePhoto) {
								$arElement["MORE_PHOTO"][] = CFile::ResizeImageGet($arMorePhoto, array("width" => 40, "height" => 50), BX_RESIZE_IMAGE_PROPORTIONAL, false, false, false, 80);
							}
						}

						$arElement["~ID"] = $arElement["ID"];
						$arElement["ID"] = $arOffer["ID"];

						if(!empty($arResult["PRODUCT_PRICE_ALLOW"])){
							$arPriceCodes = array();
							foreach($arResult["PRODUCT_PRICE_ALLOW"] as $ipc => $arNextAllowPrice){
								$dbPrice = CPrice::GetList(
							        array(),
							        array(
							            "PRODUCT_ID" => $arOffer["ID"],
							            "CATALOG_GROUP_ID" => $arNextAllowPrice["ID"]
							        )
							    );
								if($arPriceValues = $dbPrice->Fetch()){
									$arPriceCodes[] = array(
										"ID" => $arNextAllowPrice["ID"],
										"PRICE" => $arPriceValues["PRICE"],
										"CURRENCY" => $arPriceValues["CURRENCY"],
										"CATALOG_GROUP_ID" => $arNextAllowPrice["ID"]
									);
								}
							}
						}

						if(!empty($arResult["PRODUCT_PRICE_ALLOW"]) && !empty($arPriceCodes) || empty($arParams["PRICE_CODE"])){
							$arElement["TMP_PRICE"] = CCatalogProduct::GetOptimalPrice($arOffer["ID"], 1, $USER->GetUserGroupArray(), "N", $arPriceCodes);
							$arElement["MIN_PRICE"]["PRINT_DISCOUNT_VALUE"] = CurrencyFormat($arElement["TMP_PRICE"]["RESULT_PRICE"]["DISCOUNT_PRICE"], $OPTION_CURRENCY);
							$arElement["MIN_PRICE"]["PRINT_DISCOUNT_DIFF"] = $arElement["TMP_PRICE"]["RESULT_PRICE"]["DISCOUNT"];
							$arElement["MIN_PRICE"]["PRINT_VALUE"] = CurrencyFormat($arElement["TMP_PRICE"]["RESULT_PRICE"]["BASE_PRICE"], $OPTION_CURRENCY);
						}

						$arElement["IBLOCK_ID"] = $arOffer["IBLOCK_ID"];
						$arElement["CATALOG_QUANTITY"] = $arOffer["CATALOG_QUANTITY"];
						$arElement["CAN_BUY"] = $arOffer["CATALOG_AVAILABLE"];
						$arElement["CATALOG_MEASURE"] = $arOffer["CATALOG_MEASURE"];

					}
				}

			}

			//price count
			$arPriceFilter = array("PRODUCT_ID" => $arElement["ID"], "CAN_ACCESS" => "Y");
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
			$arElement["COUNT_PRICES"] = $dbPrice->SelectedRowsCount();

			//комплекты
			$arElement["COMPLECT"] = array();
			$arComplectID = array();

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

			// while ($arComplectItem = $rsComplect->Fetch()) {
			// 	$arElement["COMPLECT"]["ITEMS"][$arComplectItem["ITEM_ID"]] = $arComplectItem;
			// 	$arComplectID[$arComplectItem["ITEM_ID"]] = $arComplectItem["ITEM_ID"];
			// }

			if($arComplectItem = $rsComplect->Fetch()) {
				$arElement["IS_COMPLECT"] = "Y";
			}

			if(!empty($arComplectID)){

				$arElement["COMPLECT"]["RESULT_PRICE"] = 0;
				$arElement["COMPLECT"]["RESULT_BASE_DIFF"] = 0;
				$arElement["COMPLECT"]["RESULT_BASE_PRICE"] = 0;

				$arSelect = Array("ID", "IBLOCK_ID", "NAME", "DETAIL_PICTURE", "DETAIL_PAGE_URL", "CATALOG_MEASURE");
				$arFilter = Array("ID" => $arComplectID, "ACTIVE_DATE" => "Y", "ACTIVE" => "Y");
				$rsComplectProducts = CIBlockElement::GetList(Array(), $arFilter, false, false, $arSelect);
				while($obComplectProducts = $rsComplectProducts->GetNextElement()){

					$complectProductFields = $obComplectProducts->GetFields();

					if(!empty($arResult["PRODUCT_PRICE_ALLOW"])){
						$arPriceCodes = array();
						foreach($arResult["PRODUCT_PRICE_ALLOW"] as $ipc => $arNextAllowPrice){
							$dbPrice = CPrice::GetList(
						        array(),
						        array(
						            "PRODUCT_ID" => $complectProductFields["ID"],
						            "CATALOG_GROUP_ID" => $arNextAllowPrice["ID"]
						        )
						    );
							if($arPriceValues = $dbPrice->Fetch()){
								$arPriceCodes[] = array(
									"ID" => $arNextAllowPrice["ID"],
									"PRICE" => $arPriceValues["PRICE"],
									"CURRENCY" => $arPriceValues["CURRENCY"],
									"CATALOG_GROUP_ID" => $arNextAllowPrice["ID"]
								);
							}
						}
					}

					if(!empty($arResult["PRODUCT_PRICE_ALLOW"]) && !empty($arPriceCodes) || empty($arParams["PRICE_CODE"]))
						$complectProductFields["PRICE"] = CCatalogProduct::GetOptimalPrice($complectProductFields["ID"], 1, $USER->GetUserGroupArray(), "N", $arPriceCodes);

					$complectProductFields["PRICE"]["DISCOUNT_PRICE"] = $complectProductFields["PRICE"]["DISCOUNT_PRICE"] * $arElement["COMPLECT"]["ITEMS"][$complectProductFields["ID"]]["QUANTITY"];
					$complectProductFields["PRICE"]["DISCOUNT_PRICE"] -= $complectProductFields["PRICE"]["DISCOUNT_PRICE"] * $arElement["COMPLECT"]["ITEMS"][$complectProductFields["ID"]]["DISCOUNT_PERCENT"] / 100;
					$complectProductFields["PRICE"]["RESULT_PRICE"]["BASE_PRICE"] = $complectProductFields["PRICE"]["RESULT_PRICE"]["BASE_PRICE"] * $arElement["COMPLECT"]["ITEMS"][$complectProductFields["ID"]]["QUANTITY"];
					$complectProductFields["PRICE"]["PRICE_DIFF"] = $complectProductFields["PRICE"]["RESULT_PRICE"]["BASE_PRICE"] - $complectProductFields["PRICE"]["DISCOUNT_PRICE"];
					$complectProductFields["PRICE"]["BASE_PRICE_FORMATED"] = CurrencyFormat($complectProductFields["PRICE"]["RESULT_PRICE"]["BASE_PRICE"], $OPTION_CURRENCY);
					$complectProductFields["PRICE"]["PRICE_FORMATED"] = CurrencyFormat($complectProductFields["PRICE"]["DISCOUNT_PRICE"], $OPTION_CURRENCY);
					$arElement["COMPLECT"]["RESULT_PRICE"] += $complectProductFields["PRICE"]["DISCOUNT_PRICE"];
					$arElement["COMPLECT"]["RESULT_BASE_PRICE"] += $complectProductFields["PRICE"]["RESULT_PRICE"]["BASE_PRICE"];
					$arElement["COMPLECT"]["RESULT_BASE_DIFF"] += $complectProductFields["PRICE"]["PRICE_DIFF"];

					$complectProductFields = array_merge(
						$arElement["COMPLECT"]["ITEMS"][$complectProductFields["ID"]],
						$complectProductFields
					);

					$arElement["COMPLECT"]["ITEMS"][$complectProductFields["ID"]] = $complectProductFields;

				}

				$arElement["COMPLECT"]["RESULT_PRICE_FORMATED"] = CurrencyFormat($arElement["COMPLECT"]["RESULT_PRICE"], $OPTION_CURRENCY);
				$arElement["COMPLECT"]["RESULT_BASE_DIFF_FORMATED"] = CurrencyFormat($arElement["COMPLECT"]["RESULT_BASE_DIFF"], $OPTION_CURRENCY);
				$arElement["COMPLECT"]["RESULT_BASE_PRICE_FORMATED"] = CurrencyFormat($arElement["COMPLECT"]["RESULT_BASE_PRICE"], $OPTION_CURRENCY); 

				//set price
				$arElement["MIN_PRICE"]["PRINT_DISCOUNT_VALUE"] = $arElement["COMPLECT"]["RESULT_PRICE_FORMATED"];
				$arElement["MIN_PRICE"]["PRINT_VALUE"] = $arElement["COMPLECT"]["RESULT_BASE_PRICE_FORMATED"];
				$arElement["MIN_PRICE"]["PRINT_DISCOUNT_DIFF"] = $arElement["COMPLECT"]["RESULT_BASE_DIFF"];

			}

			if(empty($arElement["COMPLECT"]) && empty($arElement["IS_COMPLECT"])){
				//Информация о складах
				$rsStore = CCatalogStoreProduct::GetList(array(), array("PRODUCT_ID" => $arElement["ID"]), false, false, array("ID", "AMOUNT")); 
				while($arNextStore = $rsStore->GetNext()){
					$arElement["STORES"][] = $arNextStore;
				}
			}

			$arMeasureProductsID[$arElement["CATALOG_MEASURE"]] = $arElement["CATALOG_MEASURE"];
			$arResult["ITEMS"][$index] = $arElement;

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

?>