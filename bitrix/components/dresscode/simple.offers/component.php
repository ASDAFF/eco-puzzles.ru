<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)	die();?>
<?
	if (CModule::IncludeModule("catalog")){

		global $APPLICATION;
		global $arrFilter;
		
		if(!isset($arParams["CATALOG_ITEM_TEMPLATE"])){
			$arParams["CATALOG_ITEM_TEMPLATE"] = ".default";
		}

		if(empty($arParams["DISABLE_SELECT_CATEGORY"])){
			$arParams["DISABLE_SELECT_CATEGORY"] = "N";
		}

		if(!empty($arParams["PROP_NAME"]) && $arParams["PROP_VALUE"]){

			$arResult["MENU_SECTIONS"] = array();
			$arResult["ITEMS"] = array();

			$arrFilter["PROPERTY_".$arParams["PROP_NAME"]] = $arParams["PROP_VALUE"];

			$arParams["FILTER_NAME"] = "arrFilter";

			$arParams["FILTER_ADD_PROPERTY_NAME"] = $arParams["PROP_NAME"];
			$arParams["FILTER_ADD_PROPERTY_VALUE"] = $arParams["PROP_VALUE"];

			// if(empty($arParams["CURRENCY_ID"])){
			// 	$arParams["CURRENCY_ID"] = CCurrency::GetBaseCurrency();
			// 	$arParams["CONVERT_CURRENCY"] = "Y";
			// }
			
			$arFilter = Array(
				"IBLOCK_TYPE" => $arParams["IBLOCK_TYPE"],
				"IBLOCK_ID" => $arParams["IBLOCK_ID"],
				"INCLUDE_SUBSECTIONS" => "Y",
				"ACTIVE" => "Y",
			);

			$arFilter["PROPERTY_".$arParams["PROP_NAME"]] = $arParams["PROP_VALUE"];

			if($arParams["HIDE_NOT_AVAILABLE"] == "Y"){
				$arFilter["CATALOG_AVAILABLE"] = "Y";
			}

			if(!empty($_REQUEST["SECTION_ID"])){
				$arrFilter["SECTION_ID"] = intval($_REQUEST["SECTION_ID"]);
			}

			$res = CIBlockElement::GetList(array(), $arFilter, false, false, array("ID"));
			while($nextElement = $res->GetNext()){
				if($arParams["DISABLE_SELECT_CATEGORY"] != "Y"){
					$resGroup = CIBlockElement::GetElementGroups($nextElement["ID"], false);
					while($arGroup = $resGroup->Fetch()){
					    $IBLOCK_SECTION_ID = $arGroup["ID"];
					}

					$arSections[$IBLOCK_SECTION_ID] = $IBLOCK_SECTION_ID;
					$arSectionCount[$IBLOCK_SECTION_ID] = !empty($arSectionCount[$IBLOCK_SECTION_ID]) ? $arSectionCount[$IBLOCK_SECTION_ID] + 1 : 1;
				}
				$arResult["ITEMS"][] = $nextElement;
			}

			if($arParams["DISABLE_SELECT_CATEGORY"] != "Y"){
				if(!empty($arSections)){
					$arFilter = array("ID" => $arSections, "CNT_ACTIVE" => "Y", "ELEMENT_SUBSECTIONS" => "Y", "CNT_ALL" => "N");
					$rsSections = CIBlockSection::GetList(array("SORT" => "DESC"), $arFilter);
					while ($arSection = $rsSections->Fetch()){
						$searchParam = "SECTION_ID=".$arSection["ID"];
						$searchID = intval($_REQUEST["SECTION_ID"]);
						$arSection["SELECTED"] = $arSection["ID"] == $searchID ? "Y" : "N";
						$arSection["FILTER_LINK"] = $APPLICATION->GetCurPageParam($searchParam , array("SECTION_ID"));
						$arSection["ELEMENTS_COUNT"] = $arSectionCount[$arSection["ID"]];
						array_push($arResult["MENU_SECTIONS"], $arSection);
					}
				}
			}

		}

		else{
			$arResult["SHOW_TEMPLATE"] = false;
		}

		$this->IncludeComponentTemplate();
	}
?>

