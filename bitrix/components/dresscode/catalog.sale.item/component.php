<?
	if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true)
		die();

	//include modules
	if (CModule::IncludeModule("sale") &&
		CModule::IncludeModule("catalog") &&
		CModule::IncludeModule("iblock") &&
		CModule::IncludeModule("dw.deluxe") &&
		CModule::IncludeModule("highloadblock")){

		//check product id
		if(!empty($arParams["PRODUCT_ID"]) && !empty($arParams["IBLOCK_ID"])){

			//default cache_time
			if (!isset($arParams["CACHE_TIME"])){
				$arParams["CACHE_TIME"] = 1285912;
			}

			//default value for sales count param
			if (!isset($arParams["SALES_COUNT"])){
				$arParams["SALES_COUNT"] = 1;
			}

			//global vars
			global $USER;

			//cache id
			$cacheID = $USER->GetGroups()." / ".$arParams["PRODUCT_ID"];

			//start cache
			if ($this->StartResultCache($arParams["CACHE_TIME"], $cacheID)){

				//vars
				$arSales = array();

				//get sale items from iblock
				$arSelect = Array("ID", "NAME", "IBLOCK_ID", "IBLOCK_TYPE", "PREVIEW_TEXT", "DETAIL_TEXT", "PREVIEW_PICTURE", "DETAIL_PICTURE", "DATE_ACTIVE_FROM", "DETAIL_PAGE_URL");
				$arFilter = Array("IBLOCK_ID" => IntVal($arParams["IBLOCK_ID"]), "ACTIVE_DATE" => "Y", "ACTIVE" => "Y", "PROPERTY_PRODUCTS_REFERENCE" => $arParams["PRODUCT_ID"]);
				$rsSales = CIBlockElement::GetList(Array("SORT" => "ASC"), $arFilter, false, Array("nTopCount" => $arParams["SALES_COUNT"]), $arSelect);
				while($oSales = $rsSales->GetNextElement()){
					
					//get fileds
					$arNextSale = $oSales->GetFields();

					//get properties
					$arNextSale["PROPERTIES"] = $oSales->GetProperties();

					//edit buttons
					$arButtons = CIBlock::GetPanelButtons(
						$arNextSale["IBLOCK_ID"],
						$arNextSale["ID"],
						0,
						array("SECTION_BUTTONS" => true,
							  "SESSID" => true,
							  "CATALOG" => true
						)
					);

					$arNextSale["EDIT_LINK"] = $arButtons["edit"]["edit_element"]["ACTION_URL"];
					$arNextSale["PARENT_PRODUCT"]["DELETE_LINK"] = $arButtons["edit"]["delete_element"]["ACTION_URL"];

					//save
					$arResult["ITEMS"][$arNextSale["ID"]] = $arNextSale;

				}

				//clear cache keys
				$this->setResultCacheKeys(array());

				//include template
				$this->IncludeComponentTemplate();

			}

		}
		
	}

?>