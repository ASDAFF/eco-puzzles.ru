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
			    $arParams["PROP_NAME"] => $arParams["PROP_VALUE"]
			);

			$arSelect = $arParams["SELECT_FIELDS"]; // all fields 
			$arNavStartParams = array("nPageSize" => $arParams["ELEMENTS_COUNT"]);

			$res = CIBlockSection::GetList($arSort, $arFilter, false, $arSelect, $arNavStartParams);
			$countElements = $res->SelectedRowsCount();

			while ($arSection = $res->GetNext()){
				$arResult["ITEMS"][] = $arSection;
			}

			if($arParams["ELEMENTS_COUNT"] < $countElements){
				$arResult["HIDE_LAST_ELEMENT"] = true;
				if($arParams["POP_LAST_ELEMENT"] == Y){
					$arParams["ELEMENTS_COUNT"] = $arParams["ELEMENTS_COUNT"] - 1;
					array_pop($arResult["ITEMS"]);
				}
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