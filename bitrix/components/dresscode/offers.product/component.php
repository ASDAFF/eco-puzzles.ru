<?
	if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true)
		die();

		use Bitrix\Highloadblock as HL; 
		use Bitrix\Main\Entity;

		if(!CModule::IncludeModule("iblock") || !CModule::IncludeModule('highloadblock') || !CModule::IncludeModule("catalog") || !CModule::IncludeModule("sale"))
			return false;

		if(!isset($arParams["CACHE_TIME"])){
			$arParams["CACHE_TIME"] = 1285912;
		}

		if(!isset($arParams["CATALOG_ITEM_TEMPLATE"])){
			$arParams["CATALOG_ITEM_TEMPLATE"] = ".default";
		}

		$arPriceCodes = array();
		$cacheID = $USER->GetGroups();

		if(!empty($arParams["PAGE"])){
			$cacheID .= "/".$arParams["PAGE"];
			$arParams["ELEMENTS_COUNT"] = $arParams["ELEMENTS_COUNT"] * $arParams["PAGE"];
		}

		if(!empty($arParams["GROUP_ID"])){

			$cacheID .= "/".$arParams["GROUP_ID"];
			$arParams["PROP_VALUE"] = array(
				$arParams["GROUP_ID"]
			);

		}

		if(!empty($arParams["AJAX"])){
			$cacheID .= "/".$arParams["AJAX"];
		}

		if(empty($arParams["PICTURE_WIDTH"])){
			$arParams["PICTURE_WIDTH"] = 200;
		}

		if(empty($arParams["PICTURE_HEIGHT"])){
			$arParams["PICTURE_HEIGHT"] = 180;
		}

		if(!empty($arParams["PRODUCT_PRICE_CODE"])){
			$cacheID .= implode("", $arParams["PRODUCT_PRICE_CODE"]);
		}

		if($this->StartResultCache($arParams["CACHE_TIME"], $cacheID)){

			if(!empty($arParams["PROP_VALUE"])){

				$firstIter = 1;

				foreach ($arParams["PROP_VALUE"] as $ipr => $PROP_ID){

					$PROP_ID = str_replace("_", "", $PROP_ID);
					$arSelect = Array("ID", "IBLOCK_ID");
					$arFilter = Array("IBLOCK_TYPE" => $arParams["IBLOCK_TYPE"], "IBLOCK_ID" => $arParams["IBLOCK_ID"], "PROPERTY_".$arParams["PROP_NAME"] => $PROP_ID, "ACTIVE_DATE" => "Y", "ACTIVE" => "Y");
						
					//hide not available
					if ($arParams["HIDE_NOT_AVAILABLE"] == "Y"){
						$arFilter[] = array(
							array(
								"LOGIC" => "OR",
								array(
							    	"=ID" => CIBlockElement::SubQuery("PROPERTY_CML2_LINK", array("=CATALOG_AVAILABLE" => "Y", "ACTIVE_DATE" => "Y", "ACTIVE" => "Y"))
							    ),
								array(
									"LOGIC" => "AND",
									array("!ID" => CIBlockElement::SubQuery("PROPERTY_CML2_LINK", array("!ID" => false))),
									array("=CATALOG_AVAILABLE" => "Y"),
								),
							)
						);
					}

					$res = CIBlockElement::GetList(array($arParams["SORT_PROPERTY_NAME"] => $arParams["SORT_VALUE"]), $arFilter, false, array("nPageSize" => $arParams["ELEMENTS_COUNT"]), $arSelect);

					$PROP_ID = is_array($PROP_ID) ? "all" : $PROP_ID; //

					while($ob = $res->GetNextElement()){
						$arFields = $ob->GetFields();
						$arResult["GROUPS"][$PROP_ID]["ITEMS"][] = $arFields;
					}

					if(!empty($arResult["GROUPS"][$PROP_ID]["ITEMS"])){
		
						$db_enum_list = CIBlockProperty::GetPropertyEnum($arParams["PROP_NAME"], Array(), Array("IBLOCK_ID" => $arParams["IBLOCK_ID"], "ID" => $PROP_ID));
						if($ar_enum_list = $db_enum_list->GetNext()){

							if($firstIter == 1){

								$ar_enum_list["SELECTED"] = "Y";
								$arResult["FIRST_ITEMS_COUNT"] = count($arResult["GROUPS"][$PROP_ID]["ITEMS"]);
								$arResult["FIRST_ITEMS_GROUP_ID"] = $PROP_ID;
								$arResult["FIRST_ITEMS_ALL_COUNT"] = $res->SelectedRowsCount();

								if($arResult["FIRST_ITEMS_ALL_COUNT"] > $arParams["ELEMENTS_COUNT"]){
									$arParams["~ELEMENTS_COUNT"] = $arParams["ELEMENTS_COUNT"] -1;
								}else{
									$arParams["~ELEMENTS_COUNT"] = $arParams["ELEMENTS_COUNT"];
								}
							
								$arParams["NEXT_ELEMENTS_COUNT"] = $arResult["FIRST_ITEMS_ALL_COUNT"] - $arParams["~ELEMENTS_COUNT"];

								if($arParams["NEXT_ELEMENTS_COUNT"] > $arParams["~ELEMENTS_COUNT"]){
									$arParams["NEXT_ELEMENTS_COUNT"] = $arParams["~ELEMENTS_COUNT"];
								}
		
							}
							
							$ar_enum_list["PROP_NAME"] = $arParams["PROP_NAME"];
							$arResult["PROPERTY_ENUM"][$ar_enum_list["ID"]] = $ar_enum_list; 
							$arResult["GROUPS"][$PROP_ID]["PROPERTY"] = $ar_enum_list;

						}

						$firstIter++;

					}
				
				}

				$rsProperty = CIBlockProperty::GetList(
					Array(), Array(
						"ACTIVE" => "Y",
						"IBLOCK_ID" => $arParams["IBLOCK_ID"],
						"CODE" => $arParams["PROP_NAME"]
					)
				);

				if($hdProperty = $rsProperty->GetNext()){
					$arResult["PROPERTY"] = $hdProperty;
				}

			}

			if($arResult["FIRST_ITEMS_COUNT"] == $arParams["ELEMENTS_COUNT"]){
				$arResult["HIDE_LAST_ELEMENT"] = "Y";
				array_pop($arResult["GROUPS"][$arResult["FIRST_ITEMS_GROUP_ID"]]["ITEMS"]);
			}
			
			$this->setResultCacheKeys(array());
			$this->IncludeComponentTemplate();
		}


?>