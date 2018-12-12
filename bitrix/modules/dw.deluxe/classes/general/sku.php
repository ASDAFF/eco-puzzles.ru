<?
use Bitrix\Highloadblock as HL;
use Bitrix\Main\Entity;

class DwSKU {

    public static function getSkuPropertiesByIblockID($IBLOCK_ID){

    	if(!empty($IBLOCK_ID)){

	    	$arResult["PROPERTIES"] = array();

			$SKU_INFO = CCatalogSKU::GetInfoByProductIBlock($IBLOCK_ID);

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
						$prop_fields["TYPE"] = "L";
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
									"DISPLAY_VALUE" => $row_requests["UF_NAME"],
									"SELECTED" => N,
									"DISABLED" => N,
									"UF_XML_ID" => $row_requests["UF_XML_ID"],
									"IMAGE" => $row_requests["UF_FILE"],
									"NAME" => $row_requests["UF_NAME"],
									"HIGHLOAD" => "Y"
								);

							}
							$prop_fields["TYPE"] = "H";
							$prop_fields["HIGHLOAD"] = "Y";
							$arResult["PROPERTIES"][$prop_fields["CODE"]] = array_merge(
								$prop_fields, array("VALUES" => $propValues)
							);


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

    	return $arResult["PROPERTIES"];

    	}


    }

    public static function getSkuByProductID($PRODUCT_ID, $IBLOCK_ID, $arParams = array(), $arPrices = array()){

		global $USER;

		if(!empty($PRODUCT_ID)){

			$arResult["OFFERS"] = array();
			$arResult["EXIST_SKU"] = CCatalogSKU::IsExistOffers($PRODUCT_ID);

			if($arResult["EXIST_SKU"]){

				$SKU_INFO = CCatalogSKU::GetInfoByProductIBlock($IBLOCK_ID);

				if (is_array($SKU_INFO)){

					$arOffersFilter = array("IBLOCK_ID" => $SKU_INFO["IBLOCK_ID"], "PROPERTY_".$SKU_INFO["SKU_PROPERTY_ID"] => $PRODUCT_ID, "ACTIVE" => "Y");

					if($arParams["HIDE_NOT_AVAILABLE"] == "Y"){
						$arOffersFilter[">CATALOG_QUANTITY"] = 0;
					}

					$rsOffers = CIBlockElement::GetList(array(), $arOffersFilter, false, false, array("ID", "IBLOCK_ID", "DETAIL_PAGE_URL", "DETAIL_PICTURE", "NAME", "CATALOG_CAN_BUY_ZERO", "CATALOG_MEASURE_RATIO", "CATALOG_QUANTITY_TRACE", "CATALOG_QUANTITY", "CATALOG_MEASURE"));

					while($arSku = $rsOffers->GetNextElement()){

						$arSkuFields = $arSku->GetFields();
						$arSkuProperties = $arSku->GetProperties();

						if(!empty($arPrices["PRODUCT_PRICE_ALLOW"])){
							$arPriceCodes = array();
							foreach($arPrices["PRODUCT_PRICE_ALLOW"] as $ipc => $arNextAllowPrice){
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

						if(!empty($arPrices["PRODUCT_PRICE_ALLOW"]) && !empty($arPriceCodes) || empty($arPrices["PARAMS_PRICE_CODE"]))
							$arSkuFields["PRICE"] = CCatalogProduct::GetOptimalPrice($arSkuFields["ID"], 1, $USER->GetUserGroupArray(), "N", $arPriceCodes);

						//price count
						$arPriceFilter = array("PRODUCT_ID" => $arSkuFields["ID"], "CAN_ACCESS" => "Y");
						if(!empty($arPrices["PRODUCT_PRICE_ALLOW_FILTER"])){
							$arPriceFilter["CATALOG_GROUP_ID"] = $arPrices["PRODUCT_PRICE_ALLOW_FILTER"];
						}

						$dbPrice = CPrice::GetList(
					        array(),
					        $arPriceFilter,
					        false,
					        false,
					        array("ID")
					    );

						$arSkuFields["COUNT_PRICES"] = $dbPrice->SelectedRowsCount();
						$arResult["OFFERS"][$arSkuFields["ID"]] = array_merge($arSkuFields, array("PROPERTIES" => $arSkuProperties));

					}

					$arResult["ADDSKU"] = $arRarams["OPTION_ADD_CART"] === "Y" ? true : $arFields["CATALOG_QUANTITY"] > 0;
				}
			}

			return $arResult;

		}

    }

    public static function setSkuActiveProperties($arOffers, $arProperties, $offerID){

		$COLOR_PROPERTY_NANE = "COLOR";

		if(!empty($arOffers) && !empty($arProperties)){

			$arResult["SKU_PROPERTIES"] = $arProperties;

			foreach ($arResult["SKU_PROPERTIES"] as $ip => $arProp) {
				foreach ($arProp["VALUES"] as $ipv => $arPropValue) {
					$find = false;;
					foreach ($arOffers as $ipo => $arOffer) {
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
						unset($arResult["SKU_PROPERTIES"][$ip]["VALUES"][$ipv]);
					}
				}
			}

			$arResult["CLEAN_PROPERTIES"] = array();
			$iter = 0;

			foreach ($arResult["SKU_PROPERTIES"] as $ip => $arProp) {
				if(!empty($arProp["VALUES"])){
					$arKeys = array_keys($arProp["VALUES"]);
					$selectedUse = false;
					if($iter === 0 && empty($offerID)){
						$arResult["SKU_PROPERTIES"][$ip]["VALUES"][$arKeys[0]]["SELECTED"] = Y;
						$arResult["CLEAN_PROPERTIES"][$ip] = array(
							"PROPERTY" => $ip,
							"VALUE"    => $arKeys[0],
							"HIGHLOAD" => $arProp["HIGHLOAD"]
						);
					}else{
						foreach ($arKeys as $key => $keyValue) {
							$disabled = true;
							$checkValue = $arResult["SKU_PROPERTIES"][$ip]["VALUES"][$keyValue]["VALUE"];

							foreach ($arOffers as $io => $arOffer) {
								if($arOffer["PROPERTIES"][$ip]["VALUE"] == $checkValue){
									$disabled = false; $selected = true;
									foreach ($arResult["CLEAN_PROPERTIES"] as $ic => $arNextClean) {
										if($arNextClean["HIGHLOAD"] == "Y" && $arOffer["PROPERTIES"][$arNextClean["PROPERTY"]]["VALUE"] != $arNextClean["VALUE"] || $arNextClean["HIGHLOAD"] != "Y" && $arOffer["PROPERTIES"][$arNextClean["PROPERTY"]]["VALUE"] != $arNextClean["VALUE"]){
											if($ic == $ip){
												break(2);
											}
											$disabled = true;
											$selected = false;
											break(1);
										}
									}
									if($selected && !$selectedUse && empty($offerID) || $io == $offerID){
										$selectedUse = true;
										$arResult["SKU_PROPERTIES"][$ip]["VALUES"][$keyValue]["SELECTED"] = Y;
										$arResult["CLEAN_PROPERTIES"][$ip] = array(
											"PROPERTY" => $ip,
											"VALUE"    => $keyValue,
											"HIGHLOAD" => $arProp["HIGHLOAD"]
										);
										break(1);
									}
								}
							}
							if($disabled){
								$arResult["SKU_PROPERTIES"][$ip]["VALUES"][$keyValue]["DISABLED"] = "Y";
							}
						}
					}
					$iter++;
				}
			}

			if(!empty($arResult["SKU_PROPERTIES"][$COLOR_PROPERTY_NANE])){
				foreach ($arResult["SKU_PROPERTIES"][$COLOR_PROPERTY_NANE]["VALUES"] as $ic => $arProperty) {
					foreach ($arOffers as $io => $arOffer) {
						if($arOffer["PROPERTIES"][$COLOR_PROPERTY_NANE]["VALUE"] == $arProperty["VALUE"]){
							if(!empty($arOffer["DETAIL_PICTURE"])){
								$arPropertyFile = CFile::GetFileArray($arOffer["DETAIL_PICTURE"]);
								$arPropertyImage = CFile::ResizeImageGet($arPropertyFile, array('width' => 30, 'height' => 30), BX_RESIZE_IMAGE_PROPORTIONAL, false, false, false, 80);
								$arResult["SKU_PROPERTIES"][$COLOR_PROPERTY_NANE]["VALUES"][$ic]["IMAGE"] = $arPropertyImage;
								break(1);
							}
						}
					}
				}
			}

		}

		return $arResult;

	}


	public static function setSkuActiveOffer($arOffers, $arCleanProperties, $offerID){

		if(!empty($arOffers) && !empty($arCleanProperties)){

			$arResult = array();

			if(!empty($offerID)){
				foreach ($arOffers as $ir => $arOffer) {
					if($ir == $offerID){
						$arResult["SKU_ACTIVE_OFFER"] = $arOffer;
					}
				}
			}else{

				foreach ($arOffers as $ir => $arOffer) {
					$active = true;
					foreach ($arCleanProperties as $ic => $arNextClean) {
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
						$arResult["SKU_ACTIVE_OFFER"] = $arOffer;
					}
				}

			}

			return $arResult;
		}

	}
}
?>