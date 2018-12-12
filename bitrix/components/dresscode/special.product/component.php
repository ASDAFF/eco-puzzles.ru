<?
	if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true)
		die();

		use Bitrix\Highloadblock as HL;
		use Bitrix\Main\Entity;

		if(!CModule::IncludeModule("iblock") || !CModule::IncludeModule('highloadblock') || !CModule::IncludeModule("catalog") || !CModule::IncludeModule("sale") || !CModule::IncludeModule("dw.deluxe"))
			return false;

		if (!isset($arParams["CACHE_TIME"])){
			$arParams["CACHE_TIME"] = 1285912;
		}

		//prop gettr
		if(empty($arParams["PROP_NAME"])){
			$arParams["PROP_NAME"] = "PRODUCT_DAY";
		}

		if(empty($arParams["ELEMENTS_COUNT"])){
			$arParams["ELEMENTS_COUNT"] = 10;
		}

		if(empty($arParams["SORT_PROPERTY_NAME"])){
			$arParams["SORT_PROPERTY_NAME"] = "SORT";
		}

		if(empty($arParams["SORT_VALUE"])){
			$arParams["SORT_VALUE"] = "ASC";
		}

		$cacheID = $USER->GetGroups();
		$cacheID .= SITE_ID;

		if(!empty($arParams["PRODUCT_PRICE_CODE"])){
			$cacheID .= implode("", $arParams["PRODUCT_PRICE_CODE"]);
		}

		if(empty($arParams["PICTURE_WIDTH"])){
			$arParams["PICTURE_WIDTH"] = 200;
		}

		if(empty($arParams["PICTURE_HEIGHT"])){
			$arParams["PICTURE_HEIGHT"] = 220;
		}

		if(!empty($arParams["IBLOCK_ID"])){

			if ($this->StartResultCache($arParams["CACHE_TIME"], $cacheID)){

				//arResult arrays
				$arResult["ITEMS"] = array();
				$arResult["PROPERTY_HEADING"] = array();

				//get property
				$rsProperty = CIBlockProperty::GetList(Array(), Array("ACTIVE" => "Y", "IBLOCK_ID" => $arParams["IBLOCK_ID"], "CODE" => $arParams["PROP_NAME"]));
				if($arProperty = $rsProperty->GetNext()){
					$arResult["PROPERTY_HEADING"] = $arProperty["NAME"];
				}

				if(!empty($arResult["PROPERTY_HEADING"])){

					//get products
					$arSelect = Array("ID", "IBLOCK_ID", "NAME", "DETAIL_PAGE_URL", "IBLOCK_SECTION_ID", "DATE_MODIFY", "*");
					$arFilter = Array("IBLOCK_ID" => $arParams["IBLOCK_ID"], "IBLOCK_TYPE" => $arParams["IBLOCK_TYPE"], "ACTIVE_DATE" => "Y", "ACTIVE" => "Y", "!PROPERTY_".$arParams["PROP_NAME"] => false);

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
	
					$rsProducts = CIBlockElement::GetList(array($arParams["SORT_PROPERTY_NAME"] => $arParams["SORT_VALUE"]), $arFilter, false, Array("nPageSize" => $arParams["ELEMENTS_COUNT"]), $arSelect);
					while($obProducts = $rsProducts->GetNextElement()){

						$arNextElement = array();
						$arNextElement = $obProducts->GetFields();

						//write item
						$arResult["ITEMS"][$arNextElement["ID"]] = $arNextElement;
					}

				}

				$this->setResultCacheKeys(array());
				$this->IncludeComponentTemplate();

			}
		}
?>