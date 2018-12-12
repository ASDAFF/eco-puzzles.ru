<?
	if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)
		die();
?>
<?
	if(CModule::IncludeModule("iblock") && CModule::IncludeModule("catalog") && CModule::IncludeModule("sale") && CModule::IncludeModule("search")){

		if(!empty($_REQUEST["q"]) && strlen($_REQUEST["q"]) > 1){

			global $APPLICATION;
			global $arrFilter;

			$arParams["FILTER_NAME"] = "arrFilter";

			if(empty($arParams["CURRENCY_ID"])){
				$arParams["CURRENCY_ID"] = CCurrency::GetBaseCurrency();
				$arParams['CONVERT_CURRENCY'] = "Y";
			}

			if(isset($_REQUEST["ajax"]) && $_REQUEST["ajax"] == "y"){
				$_REQUEST["q"] = BX_UTF != 1 ? iconv("UTF-8", "windows-1251//ignore", $_REQUEST["q"]) : $_REQUEST["q"];
			}

			$arResult["ITEMS"] = array();
			$arResult["QUERY"] = $arResult["~QUERY"] = trim($_REQUEST["q"]);

			if(!empty($arParams["CONVERT_CASE"]) && $arParams["CONVERT_CASE"] == "Y"){
				$arLang = CSearchLanguage::GuessLanguage($arResult["QUERY"]);
				if(is_array($arLang) && $arLang["from"] != $arLang["to"]){
	  				$arResult["QUERY"] = CSearchLanguage::ConvertKeyboardLayout($arResult["QUERY"], $arLang["from"], $arLang["to"]);
	  				$arResult["QUERY_REPLACE"] = true;
				}
			}

			$arResult["QUERY_TITLE"] = GetMessage("SEARCH_RESULT")." - &laquo;".trim(htmlspecialcharsbx($arResult["QUERY"])."&raquo;");

			$APPLICATION->SetTitle(
				$arResult["QUERY_TITLE"]
			);

			$APPLICATION->AddChainItem(
				trim(htmlspecialcharsbx($arResult["QUERY"]))
			);

			$arResult["SEARCH_PROPERTIES"] = array();

			$rsSearchProperties = CIBlockProperty::GetList(Array("sort" => "asc", "name" => "asc"), Array('IBLOCK_TYPE' => $arParams["IBLOCK_TYPE"], 'IBLOCK_ID' => $arParams["IBLOCK_ID"],  "ACTIVE" => "Y", "SEARCHABLE" => "Y"));
			while ($nextSearchProperty = $rsSearchProperties->GetNext()){
				$arResult["SEARCH_PROPERTIES"][$nextSearchProperty["ID"]] = $nextSearchProperty;
			}

			$arAppendFilter["LOGIC"] = "OR";
			$arAppendFilter["?NAME"] = $arResult["QUERY"];
			$arAppendFilter["?PROPERTY_CML2_ARTICLE"] = $arResult["QUERY"];

			foreach ($arResult["SEARCH_PROPERTIES"] as $index => $arNextProp){
				if($arNextProp["PROPERTY_TYPE"] == "L"){
					$arAppendFilter["?PROPERTY_".$arNextProp["CODE"]."_VALUE"] = $arResult["QUERY"];
				}else{
					$arAppendFilter["?PROPERTY_".$arNextProp["CODE"]] = $arResult["QUERY"];
				}
			}

			// $arrFilter[] = array(
			// 	"LOGIC" => "OR",
			// 	"?NAME" => htmlspecialcharsbx($arResult["QUERY"]),
			// 	"?PROPERTY_CML2_ARTICLE" => htmlspecialcharsbx($arResult["QUERY"])
			// );

			$arrFilter[] = $arAppendFilter;

			if(!empty($_REQUEST["where"])){
				$arrFilter["SUBSECTION"] = intval($_REQUEST["where"]);
			}

			$arFilter = Array(
				"?NAME" => htmlspecialcharsbx($arResult["QUERY"]),
				"IBLOCK_ID" => $arParams["IBLOCK_ID"],
				"IBLOCK_TYPE" => $arParams["IBLOCK_TYPE"],
				"ACTIVE" => "Y"

			);

			if(!empty($_REQUEST["where"])){
				$arFilter["SUBSECTION"] = intval($_REQUEST["where"]);
			}

			$rsSec = CIBlockSection::GetList(Array("sort" => "desc"), $arFilter, false);
			while($arRes = $rsSec->GetNext()){
				$arResult["SECTIONS"][] = $arRes;
			}

			$arResult["MENU_SECTIONS"] = array();

			$arFilter = Array(
				"IBLOCK_TYPE" => $arParams["IBLOCK_TYPE"],
				"IBLOCK_ID" => $arParams["IBLOCK_ID"],
				"INCLUDE_SUBSECTIONS" => "Y",
				"ACTIVE" => "Y",
			);

			if($arParams["HIDE_NOT_AVAILABLE"] == "Y"){
				$arFilter["CATALOG_AVAILABLE"] = "Y";
			}

			if(!empty($_REQUEST["SECTION_ID"])){
				$arFilter["SECTION_ID"] = intval($_REQUEST["SECTION_ID"]);
			}

			if(empty($_REQUEST["where"])){

				// $arFilter[] = array(
				// 	"LOGIC" => "OR",
				// 	"?NAME" => htmlspecialcharsbx($arResult["QUERY"]),
				// 	"?PROPERTY_CML2_ARTICLE" => htmlspecialcharsbx($arResult["~QUERY"])
				// );

				$arFilter[] = $arAppendFilter;

			}else{

				$arXAppendFilter = $arAppendFilter;
				unset($arXAppendFilter["?NAME"]);

				$arFilter[] = $arXAppendFilter;
				$arFilter[] = array(
					"LOGIC" => "AND",
					"?NAME" => htmlspecialcharsbx($arResult["QUERY"]),
					"SUBSECTION" => intval($_REQUEST["where"])
				);

			}

			$arFilter["SECTION_ID"] = array();
			$res = CIBlockElement::GetList(array(), $arFilter, false, false, array("ID"));
			while($nextElement = $res->GetNext()){
				$resGroup = CIBlockElement::GetElementGroups($nextElement["ID"], false);
				while($arGroup = $resGroup->Fetch()){
				    $IBLOCK_SECTION_ID = $arGroup["ID"];
				}

				$arSections[$IBLOCK_SECTION_ID] = $IBLOCK_SECTION_ID;
				$arSectionCount[$IBLOCK_SECTION_ID] = !empty($arSectionCount[$IBLOCK_SECTION_ID]) ? $arSectionCount[$IBLOCK_SECTION_ID] + 1 : 1;
				$arResult["ITEMS"][] = $nextElement;
			}

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

	if(!empty($arResult["ITEMS"]) && count($arResult["ITEMS"]) == 1){
		if(!empty($arResult["ITEMS"][0]["ID"])){
			if($gLastProduct = CIBlockElement::GetByID($arResult["ITEMS"][0]["ID"])){
				$arLastProduct = $gLastProduct->GetNext();
				if(!empty($arLastProduct["DETAIL_PAGE_URL"])){
					LocalRedirect($arLastProduct["DETAIL_PAGE_URL"]);
				}
			}
		}
	}

$this->IncludeComponentTemplate();

?>

