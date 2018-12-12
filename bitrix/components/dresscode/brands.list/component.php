<?
	if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)
		die();


		if(!CModule::IncludeModule("iblock"))
			return;

		if (!isset($arParams["CACHE_TIME"])){
			$arParams["CACHE_TIME"] = 1285912;
		}
		
		$arParams["ELEMENTS_COUNT_ORIGINAL"] = $arParams["ELEMENTS_COUNT"];

		$cacheID = $USER->GetUserGroupString();
		
		if(!empty($arParams["PAGE"])){
			$cacheID .= "/".$arParams["PAGE"];
			$arParams["ELEMENTS_COUNT"] = $arParams["ELEMENTS_COUNT"] * $arParams["PAGE"];
		}

		if(!empty($arParams["AJAX"])){
			$cacheID .= "/".$arParams["AJAX"];
		}

		if ($this->StartResultCache($arParams["CACHE_TIME"], $cacheID)){
			
			$arSort = array(
				$arParams["SORT_PROPERTY_NAME"] => $arParams["SORT_VALUE"]
			);

	        $arFilter = array(
			    "ACTIVE" => Y,
			    "GLOBAL_ACTIVE" => Y,
			    "IBLOCK_TYPE" => $arParams["IBLOCK_TYPE"],
			    "IBLOCK_ID" => $arParams["IBLOCK_ID"],
			    "!DETAIL_PICTURE" => false
			);

			$arNavStartParams = array("nPageSize" => $arParams["ELEMENTS_COUNT"]);
			$arSelect = Array("ID", "IBLOCK_ID", "NAME", "*");

			$res = CIBlockElement::GetList($arSort, $arFilter, false, $arNavStartParams, $arSelect);
			$countElements = $res->SelectedRowsCount();
			
			while($ob = $res->GetNextElement()){
				
				$arFields = $ob->GetFields();
				
				$arButtons = CIBlock::GetPanelButtons(
					$arFields["IBLOCK_ID"],
					$arFields["ID"],
					$arFields["ID"],
					array("SECTION_BUTTONS" => false, 
						  "SESSID" => false, 
						  "CATALOG" => true
					)
				);

				$arFields["EDIT_LINK"] = $arButtons["edit"]["edit_element"]["ACTION_URL"];
				$arFields["DELETE_LINK"] = $arButtons["edit"]["delete_element"]["ACTION_URL"];

				$arResult["ITEMS"][] = array_merge(
					$arFields, array(
						"PROPERTIES" => $ob->GetProperties()
					)
				);
			}

			if($arParams["ELEMENTS_COUNT"] < $countElements){
				$arResult["HIDE_LAST_ELEMENT"] = true;
				$arParams["ELEMENTS_COUNT"] = $arParams["ELEMENTS_COUNT"] - 1;
				array_pop($arResult["ITEMS"]);
			}

			if($countElements >= $arParams["ELEMENTS_COUNT"] + $arParams["ELEMENTS_COUNT_ORIGINAL"]){
				$arResult["NEXT_ELEMENTS_COUNT"] = $arParams["ELEMENTS_COUNT_ORIGINAL"];
			}else{
				$arResult["NEXT_ELEMENTS_COUNT"] = $countElements - $arParams["ELEMENTS_COUNT"];
			}


			if($countElements < $arParams["ELEMENTS_COUNT"]){
				$arParams["ELEMENTS_COUNT"] = $countElements;
			}

			$arResult["ITEMS_ALL_COUNT"] = $countElements;
			$arResult["ELEMENTS_COUNT_SHOW"] = $arParams["ELEMENTS_COUNT"];
			$arParams["ELEMENTS_COUNT"] = $arParams["ELEMENTS_COUNT_ORIGINAL"];

			$this->setResultCacheKeys(array());
			$this->IncludeComponentTemplate();

		}

?>