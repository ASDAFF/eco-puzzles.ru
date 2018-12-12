<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();?>
<?
	CModule::IncludeModule('highloadblock');
	use Bitrix\Highloadblock as HL; 
	use Bitrix\Main\Entity;

	$OPTION_ADD_CART  = COption::GetOptionString("catalog", "default_can_buy_zero");
	$OPTION_PRICE_TAB = COption::GetOptionString("catalog", "show_catalog_tab_with_offers");
	$OPTION_CURRENCY  = CCurrency::GetBaseCurrency();
?>
<?
if(!empty($arResult)){
	
	$arGetParentToGetImage = array();
	$arTableParentToSkuProduct = array();

	foreach($arResult as $key => $nextElement){
		if(CCatalogSKU::IsExistOffers($nextElement["PRODUCT_ID"])){
			$skuProductIblock = $nextElement["IBLOCK_ID"];
		}
	}
	
	if(!empty($skuProductIblock)){
		
		$COLOR_PROPERTY_NANE = "COLOR";
		$SKU_INFO = CCatalogSKU::GetInfoByProductIBlock($skuProductIblock);

		if(is_array($SKU_INFO)){
			$properties = CIBlockProperty::GetList(Array("sort" => "asc", "name" => "asc"), Array("ACTIVE" => "Y", "IBLOCK_ID" => $SKU_INFO["IBLOCK_ID"]));
			while ($prop_fields = $properties->GetNext()){
				if($prop_fields["SORT"] <= 100 && $prop_fields["PROPERTY_TYPE"] == "L"){
					$propValues = array();
					$prop_fields["HIGHLOAD"] = "N";
					$property_enums = CIBlockPropertyEnum::GetList(Array("SORT" => "ASC", "DEF" => "DESC"), Array("IBLOCK_ID" => $SKU_INFO["IBLOCK_ID"], "CODE" => $prop_fields["CODE"]));
					while($enum_fields = $property_enums->GetNext()){
						$propValues[$enum_fields["EXTERNAL_ID"]] = array(
							"VALUE"  => $enum_fields["VALUE"],
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
					$prop_fields["TYPE"] = "L";
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
								"SELECTED" => N,
								"DISABLED" => N,
								"UF_XML_ID" => $row_requests["UF_XML_ID"],
								"IMAGE" => $row_requests["UF_FILE"],
								"NAME" => $row_requests["UF_NAME"],
								"HIGHLOAD" => "Y"
							);

						}

						$prop_fields["HIGHLOAD"] = "Y";
						$prop_fields["TYPE"] = "H";
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
	}

	foreach($arResult as $key => $val){
		
		$img = "";
		
		if ($val["DETAIL_PICTURE"] > 0){
			$img = $val["DETAIL_PICTURE"];
		}
		elseif ($val["PREVIEW_PICTURE"] > 0){
			$img = $val["PREVIEW_PICTURE"];
		}

		if(empty($img)){
			$parentProduct = CCatalogSku::GetProductInfo($val["PRODUCT_ID"], $val["IBLOCK_ID"]);
			if(!empty($parentProduct["ID"])){
				$arGetParentToGetImage[$parentProduct["ID"]] = $parentProduct["ID"];
				$arTableParentToSkuProduct[$parentProduct["ID"]] = $val["PRODUCT_ID"];
			}elseif(CCatalogSKU::IsExistOffers($val["PRODUCT_ID"])){
				if(empty($SKU_INFO)){
					$SKU_INFO = CCatalogSKU::GetInfoByProductIBlock($val["IBLOCK_ID"]);
				}
				if (is_array($SKU_INFO)){
					$arOffersFilter = array("IBLOCK_ID" => $SKU_INFO["IBLOCK_ID"], "PROPERTY_".$SKU_INFO["SKU_PROPERTY_ID"] => $val["PRODUCT_ID"], "ACTIVE" => "Y");
					if($OPTION_ADD_CART == N){
						$arOffersFilter[">CATALOG_QUANTITY"] = 0;
					}
					$rsOffers = CIBlockElement::GetList(array("sort" => "desc"), $arOffersFilter, false, false, array("ID", "IBLOCK_ID", "DETAIL_PAGE_URL", "DETAIL_PICTURE", "NAME", "CATALOG_QUANTITY")); 
					while($arSku = $rsOffers->GetNextElement()){
						$arSkuFields = $arSku->GetFields();
						$arSkuProperties = $arSku->GetProperties();

						$arSkuFields["PRICE"] = CCatalogProduct::GetOptimalPrice($arSkuFields["ID"], 1, $USER->GetUserGroupArray());
						$val["SKU_PRODUCT"][] = array_merge($arSkuFields, array("PROPERTIES" => $arSkuProperties));
						$val["SKU_PRICES"][] = $arSkuPrice["DISCOUNT_PRICE"];

					}

					$val["ADDSKU"] = $OPTION_ADD_CART === "Y" ? true : $val["CATALOG_QUANTITY"] > 0;
					$val["SKU_INFO"] = $SKU_INFO;
				}

				if(!empty($val["SKU_PRODUCT"]) && !empty($arResult["PROPERTIES"])){
					$val["SKU_PROPERTIES"] = $arResult["PROPERTIES"];
					foreach ($val["SKU_PROPERTIES"] as $ip => $arProp) {
						foreach ($arProp["VALUES"] as $ipv => $arPropValue) {
							$find = false;;
							foreach ($val["SKU_PRODUCT"] as $ipo => $arOffer) {
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
								unset($val["SKU_PROPERTIES"][$ip]["VALUES"][$ipv]);
							}
						}
					}

					// first display

					$arPropClean = array();
					$iter = 0;

					foreach ($val["SKU_PROPERTIES"] as $ip => $arProp) {
						if(!empty($arProp["VALUES"])){
							$arKeys = array_keys($arProp["VALUES"]);
							$selectedUse = false;
							if($iter === 0){
								$val["SKU_PROPERTIES"][$ip]["VALUES"][$arKeys[0]]["SELECTED"] = Y;
								$arPropClean[$ip] = array(
									"PROPERTY" => $ip,
									"VALUE"    => $arKeys[0],
									"HIGHLOAD" => $arProp["HIGHLOAD"]
								);
							}else{
								foreach ($arKeys as $key => $keyValue) {
									$disabled = true;
									$checkValue = $val["SKU_PROPERTIES"][$ip]["VALUES"][$keyValue]["VALUE"];

									foreach ($val["SKU_PRODUCT"] as $io => $arOffer) {
										if($arOffer["PROPERTIES"][$ip]["VALUE"] == $checkValue){
											$disabled = false; $selected = true;
											foreach ($arPropClean as $ic => $arNextClean) {
												if($arOffer["PROPERTIES"][$arNextClean["PROPERTY"]]["VALUE_XML_ID"] != $arNextClean["VALUE"]){
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
												$val["SKU_PROPERTIES"][$ip]["VALUES"][$keyValue]["SELECTED"] = Y;
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
										$val["SKU_PROPERTIES"][$ip]["VALUES"][$keyValue]["DISABLED"] = "Y";
									}
								}
							}
							$iter++;
						}
					}

					foreach ($val["SKU_PRODUCT"] as $ir => $arOffer) {
						$active = true;
						foreach ($arPropClean as $ic => $arNextClean) {
							if($arNextClean["HIGHLOAD"] == "Y" || $arResult["PROPERTIES"][$ic]["TYPE"] == "E"){
								if($arOffer["PROPERTIES"][$arNextClean["PROPERTY"]]["VALUE"] != $arNextClean["VALUE"]){
									$active = false;
									break(1);
								}
							}else{
								if($arOffer["PROPERTIES"][$arNextClean["PROPERTY"]]["VALUE_XML_ID"] != $arNextClean["VALUE"]){
									$active = false;
									break(1);
								}
							}
						}
						if($active){

							if(!empty($arOffer["DETAIL_PICTURE"])){
								$img = $arOffer["DETAIL_PICTURE"];
							}

							if(!empty($arOffer["NAME"])){
								$val["NAME"] = $arOffer["NAME"];
							}

							if(!empty($arOffer["PROPERTIES"]["MORE_PHOTO"]["VALUE"])){
								foreach ($arOffer["PROPERTIES"]["MORE_PHOTO"]["VALUE"] as $impr => $arMorePhoto) {
									$val["MORE_PHOTO"][] = CFile::ResizeImageGet($arMorePhoto, array("width" => 40, "height" => 50), BX_RESIZE_IMAGE_PROPORTIONAL, false, false, false, 80);
								}
							}

							$val["~ID"] = $val["ID"];
							$val["ID"] = $arOffer["ID"];
							$val["ARRAY_PRICE"]   = CCatalogProduct::GetOptimalPrice($arOffer["ID"], 1, $USER->GetUserGroupArray());
							$val["IBLOCK_ID"] = $arOffer["IBLOCK_ID"];
							$val["CATALOG_QUANTITY"] = $arOffer["CATALOG_QUANTITY"];
							$val["CAN_BUY"] = $OPTION_ADD_CART == "Y" ? true : false;

						}
					}

				}

			}

		}

		$file = CFile::ResizeImageGet($img, array('width' => $arParams["VIEWED_IMG_WIDTH"], 'height' => $arParams["VIEWED_IMG_HEIGHT"]), BX_RESIZE_IMAGE_PROPORTIONAL, false);
		$file["src"] = !empty($file["src"]) ? $file["src"] : SITE_TEMPLATE_PATH."/images/empty.png";
		$val["PICTURE"] = $file;

		//комплекты
		$val["COMPLECT"] = array();
		$arComplectID = array();

		$rsComplect = CCatalogProductSet::getList(
			array("SORT" => "ASC"),
			array(
				"TYPE" => 1,
				"OWNER_ID" => $val["PRODUCT_ID"],
				"!ITEM_ID" => $val["PRODUCT_ID"]
			),
			false,
			false,
			array("*")
		);

		while ($arComplectItem = $rsComplect->Fetch()) {
			$val["COMPLECT"]["ITEMS"][$arComplectItem["ITEM_ID"]] = $arComplectItem;
			$arComplectID[$arComplectItem["ITEM_ID"]] = $arComplectItem["ITEM_ID"];
		}

		if(!empty($arComplectID)){

			$val["COMPLECT"]["RESULT_PRICE"] = 0;
			$val["COMPLECT"]["RESULT_BASE_DIFF"] = 0;
			$val["COMPLECT"]["RESULT_BASE_PRICE"] = 0;

			$arSelect = Array("ID", "IBLOCK_ID", "NAME", "DETAIL_PICTURE", "DETAIL_PAGE_URL", "CATALOG_MEASURE");
			$arFilter = Array("ID" => $arComplectID, "ACTIVE_DATE" => "Y", "ACTIVE" => "Y");
			$rsComplectProducts = CIBlockElement::GetList(Array(), $arFilter, false, false, $arSelect);
			while($obComplectProducts = $rsComplectProducts->GetNextElement()){
				
				$complectProductFields = $obComplectProducts->GetFields();
				$complectProductFields["PRICE"] = CCatalogProduct::GetOptimalPrice($complectProductFields["ID"], 1, $USER->GetUserGroupArray());
				$complectProductFields["PRICE"]["DISCOUNT_PRICE"] = $complectProductFields["PRICE"]["DISCOUNT_PRICE"] * $val["COMPLECT"]["ITEMS"][$complectProductFields["ID"]]["QUANTITY"];
				$complectProductFields["PRICE"]["DISCOUNT_PRICE"] -= $complectProductFields["PRICE"]["DISCOUNT_PRICE"] * $val["COMPLECT"]["ITEMS"][$complectProductFields["ID"]]["DISCOUNT_PERCENT"] / 100;
				$complectProductFields["PRICE"]["RESULT_PRICE"]["BASE_PRICE"] = $complectProductFields["PRICE"]["RESULT_PRICE"]["BASE_PRICE"] * $val["COMPLECT"]["ITEMS"][$complectProductFields["ID"]]["QUANTITY"];
				$complectProductFields["PRICE"]["PRICE_DIFF"] = $complectProductFields["PRICE"]["RESULT_PRICE"]["BASE_PRICE"] - $complectProductFields["PRICE"]["DISCOUNT_PRICE"];
				$complectProductFields["PRICE"]["BASE_PRICE_FORMATED"] = CurrencyFormat($complectProductFields["PRICE"]["RESULT_PRICE"]["BASE_PRICE"], $OPTION_CURRENCY);
				$complectProductFields["PRICE"]["PRICE_FORMATED"] = CurrencyFormat($complectProductFields["PRICE"]["DISCOUNT_PRICE"], $OPTION_CURRENCY);
				$val["COMPLECT"]["RESULT_PRICE"] += $complectProductFields["PRICE"]["DISCOUNT_PRICE"];
				$val["COMPLECT"]["RESULT_BASE_PRICE"] += $complectProductFields["PRICE"]["RESULT_PRICE"]["BASE_PRICE"];
				$val["COMPLECT"]["RESULT_BASE_DIFF"] += $complectProductFields["PRICE"]["PRICE_DIFF"];

				$complectProductFields = array_merge(
					$val["COMPLECT"]["ITEMS"][$complectProductFields["ID"]], 
					$complectProductFields
				);
				
				$val["COMPLECT"]["ITEMS"][$complectProductFields["ID"]] = $complectProductFields;

			}

			$val["COMPLECT"]["RESULT_PRICE_FORMATED"] = CurrencyFormat($val["COMPLECT"]["RESULT_PRICE"], $OPTION_CURRENCY);
			$val["COMPLECT"]["RESULT_BASE_DIFF_FORMATED"] = CurrencyFormat($val["COMPLECT"]["RESULT_BASE_DIFF"], $OPTION_CURRENCY);
			$val["COMPLECT"]["RESULT_BASE_PRICE_FORMATED"] = CurrencyFormat($val["COMPLECT"]["RESULT_BASE_PRICE"], $OPTION_CURRENCY); 

			//set price
			$val["ARRAY_PRICE"]["DISCOUNT_PRICE"] = $val["COMPLECT"]["RESULT_PRICE"];
			if($val["COMPLECT"]["RESULT_BASE_DIFF"] > 0){
				$val["ARRAY_PRICE"]["DISCOUNT"] = $val["COMPLECT"]["RESULT_BASE_DIFF"];
				$val["ARRAY_PRICE"]["RESULT_PRICE"]["BASE_PRICE"] = $val["COMPLECT"]["RESULT_BASE_PRICE"];
			}

		}
		
		if(!empty($val["ID"])){
			$arResult["ITEMS"][$val["PRODUCT_ID"]] = $val;
			$arElementsID[$val["PRODUCT_ID"]] = $val["PRODUCT_ID"];
		}

	}
}

if(!empty($arElementsID)){
	$arSelect = Array("ID", "IBLOCK_ID", "*");
	$arFilter = Array("ID" => $arElementsID, "ACTIVE_DATE" => "Y", "ACTIVE" => "Y");
	$res = CIBlockElement::GetList(Array(), $arFilter, false, false, $arSelect);
	while($ob = $res->GetNextElement()){ 
		$arFields = $ob->GetFields();  
		$arResult["ITEMS"][$arFields["ID"]]["PROPERTIES"] = $ob->GetProperties();
		if(empty($arResult["ITEMS"][$arFields["ID"]]["ARRAY_PRICE"])){
			$arResult["ITEMS"][$arFields["ID"]]["ARRAY_PRICE"] = CCatalogProduct::GetOptimalPrice($arFields["ID"], 1, $USER->GetUserGroupArray());
		}
	}
}

if(!empty($arGetParentToGetImage)){
	$arSelect = Array("ID", "DETAIL_PICTURE");
	$arFilter = Array("ID" => $arGetParentToGetImage, "ACTIVE_DATE" => "Y", "ACTIVE" => "Y");
	$res = CIBlockElement::GetList(Array(), $arFilter, false, false, $arSelect);
	while($ob = $res->GetNextElement()){ 
		$arFields = $ob->GetFields();
		if(!empty($arFields["DETAIL_PICTURE"])){
			$arResult["ITEMS"][$arTableParentToSkuProduct[$arFields["ID"]]]["PICTURE"] = CFile::ResizeImageGet($arFields["DETAIL_PICTURE"], array('width' => $arParams["VIEWED_IMG_WIDTH"], 'height' => $arParams["VIEWED_IMG_HEIGHT"]), BX_RESIZE_IMAGE_PROPORTIONAL, false);
		}
		
	}
}

?>