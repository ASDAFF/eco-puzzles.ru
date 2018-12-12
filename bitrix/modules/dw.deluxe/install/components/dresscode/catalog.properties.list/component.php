<?
	if(!empty($arParams["PRODUCT_ID"])){
		
		if (!isset($arParams["CACHE_TIME"])){
			$arParams["CACHE_TIME"] = 1285912;
		}

		if(empty($arParams["SORT_PARAMS_VALUE"])){
			$arParams["SORT_PARAMS_VALUE"] = 5000;
		}

		if ($this->StartResultCache($arParams["CACHE_TIME"], $arParams["PRODUCT_ID"])){

			$getProductResult = CIBlockElement::GetList(
				Array(), 
				Array(
					"ID" => intval($arParams["PRODUCT_ID"]),
					"ACTIVE_DATE" => "Y",
					"ACTIVE" => "Y"
				),
				false, 
				Array("nPageSize" => 1),
				Array(
					"ID", 
					"IBLOCK_ID"
				)
			);

			if($getProductObject = $getProductResult->GetNextElement()){
				$arProductProperties = $getProductObject->GetProperties(array("sort" => "asc", "name" => "asc"));
			}

			//ID товара по ID предложения
			$arProductSkuExist = CCatalogSku::GetProductInfo(intval($arParams["PRODUCT_ID"]));
			if (is_array($arProductSkuExist)){
				$getSkuProductResult = CIBlockElement::GetList(
					Array(), 
					Array(
						"ID" => intval($arProductSkuExist["ID"]),
						"ACTIVE_DATE" => "Y",
						"ACTIVE" => "Y"
					),
					false, 
					Array("nPageSize" => 1),
					Array(
						"ID", 
						"IBLOCK_ID"
					)
				);

				if($getSkuProductObject = $getSkuProductResult->GetNextElement()){
					$arSkuProductProperties = $getSkuProductObject->GetProperties(array("sort" => "asc", "name" => "asc"));
				}
			}

			$arResult["PROPERTIES"] = $arProductProperties;
			if(!empty($arSkuProductProperties)){
				$arResult["PROPERTIES"] = array_merge($arSkuProductProperties, $arResult["PROPERTIES"]);
			}

			if(!empty($arResult["PROPERTIES"])){
				foreach ($arResult["PROPERTIES"] as $propertyCode => $arNextProperty) {
					$arResult["DISPLAY_PROPERTIES"][$propertyCode] = CIBlockFormatProperties::GetDisplayValue($arResult, $arNextProperty, "catalog_out");
				}
			}

			if(!empty($arResult["DISPLAY_PROPERTIES"])){
				foreach ($arResult["DISPLAY_PROPERTIES"] as $index => $arProp) {
					if($arProp["SORT"] <= $arParams["SORT_PARAMS_VALUE"] && !empty($arProp["VALUE"])){
						if($arProp["CODE"] == "MORE_PROPERTIES"){
							foreach ($arProp["VALUE"] as $i => $arValue) {
								$arResult["DISPLAY_PROPERTIES"][] = array(
									"CODE" => $arProp["PROPERTY_VALUE_ID"][$i],
									"SORT" => $arProp["SORT"],
									"VALUE" => $arProp["DESCRIPTION"][$i],
									"DISPLAY_VALUE" => $arProp["DESCRIPTION"][$i],
									"NAME" => $arValue
								);
							}
							unset($arResult["DISPLAY_PROPERTIES"][$index]);
							continue;
						}elseif($arProp["USER_TYPE"] == "HTML"){
							$arResult["DISPLAY_PROPERTIES"][$index]["VALUE"] = $arProp["~VALUE"]["TEXT"];
						}elseif($arProp["CODE"] == "VIDEO"){
							unset($arResult["DISPLAY_PROPERTIES"][$index]);
						}
					
						if(is_array($arProp["DISPLAY_VALUE"])){
							$arResult["DISPLAY_PROPERTIES"][$index]["DISPLAY_VALUE"] = implode(" / ", $arProp["DISPLAY_VALUE"]);
						}
					}else{
						unset($arResult["DISPLAY_PROPERTIES"][$index]);
					}

				}
			}

			foreach ($arResult["PROPERTIES"] as $index => $arProp) {
				if($arProp["CODE"] == "MORE_PROPERTIES"){
					if(!empty($arProp["VALUE"])){
						foreach ($arProp["VALUE"] as $i => $arValue) {
							$arResult["PROPERTIES"][] = array(
								"CODE" => $arProp["PROPERTY_VALUE_ID"][$i],
								"SORT" => $arParams["SORT_PARAMS_VALUE"],
								"VALUE" => $arProp["DESCRIPTION"][$i],
								"NAME" => $arValue
							);
						}
					}
					unset($arResult["PROPERTIES"][$index]);
				}elseif($arProp["CODE"] == "MORE_PHOTO"){
					unset($arResult["PROPERTIES"][$index]);
				}else if($arProp["PROPERTY_TYPE"] == "F" && $arProp["SORT"] <= $arParams["SORT_PARAMS_VALUE"]){
					if(!empty($arProp["VALUE"])){
						if($arProp["MULTIPLE"] == "Y"){
							foreach ($arProp["VALUE"] as $ifx => $fileID) {
						        $rsFile = CFile::GetByID($fileID);
								$arFile = $rsFile->Fetch();
								$arResult["PROPERTIES"][] = array(
									"CODE" => $arFile["ID"],
									"SORT" => $arParams["SORT_PARAMS_VALUE"],
									"PROPERTY_TYPE" => "F",
									"VALUE" => !empty($arProp["DESCRIPTION"][$ifx]) ? '<a href="'.CFile::GetPath($fileID).'">'.$arProp["DESCRIPTION"][$ifx].'</a> ' : '<a href="'.CFile::GetPath($fileID).'">'.$arFile["FILE_NAME"].'</a> ',
									"NAME" => $arProp["NAME"]
								);
							}
						}else{
						    $rsFile = CFile::GetByID($arProp["VALUE"]);
							$arFile = $rsFile->Fetch();
							$arResult["PROPERTIES"][] = array(
								"CODE" => $arFile["ID"],
								"SORT" => $arParams["SORT_PARAMS_VALUE"],
								"PROPERTY_TYPE" => "F",
								"VALUE" => !empty($arProp["DESCRIPTION"]) ? '<a href="'.CFile::GetPath($fileID).'">'.$arProp["DESCRIPTION"].'</a> ' : '<a href="'.CFile::GetPath($arProp["VALUE"]).'">'.$arFile["FILE_NAME"].'</a> ',
								"NAME" => $arProp["NAME"]
							);
						}
					}
					unset($arResult["PROPERTIES"][$index]);
				}elseif($arProp["USER_TYPE"] == "HTML"){
					$arResult["PROPERTIES"][$index]["VALUE"] = $arProp["~VALUE"]["TEXT"];
				}
			}

			foreach ($arResult["PROPERTIES"] as $pid => $arPropNext) {
				if($arPropNext["PROPERTY_TYPE"] == "F" && $arPropNext["SORT"] <= $arParams["SORT_PARAMS_VALUE"]){
					$arResult["DISPLAY_PROPERTIES"][$pid] = $arPropNext;
				}
			}

			if(!empty($arResult["DISPLAY_PROPERTIES"])){
				usort($arResult["DISPLAY_PROPERTIES"], function($a, $b){
			    	return ($a["SORT"] - $b["SORT"]);
				});
				$arResult["DISPLAY_PROPERTIES_GROUP"] = $arResult["DISPLAY_PROPERTIES"];
			}

			if(empty($arParams["ELEMENT_LAST_SECTION_ID"])){
				
				$db_old_groups = CIBlockElement::GetElementGroups(is_array($arProductSkuExist) ? $arProductSkuExist["ID"] : $arParams["PRODUCT_ID"], false);
				while($ar_group = $db_old_groups->Fetch()){
					$arSection[$ar_group["DEPTH_LEVEL"]] = $ar_group["ID"];
				}

				if(!empty($arSection)){
					$arResult["LAST_SECTION"] = array_slice($arSection, 0, 1);
					$res = CIBlockSection::GetByID($arResult["LAST_SECTION"][0]);
					if($arSec = $res->GetNext()){
						$arResult["LAST_SECTION"] = $arSec;
					}
				}

			}else{
				$res = CIBlockSection::GetByID($arParams["ELEMENT_LAST_SECTION_ID"]);
				if($arSec = $res->GetNext()){
					$arResult["LAST_SECTION"] = $arSec;
				}
			}

			$this->setResultCacheKeys(array());
			$this->IncludeComponentTemplate();

		}
	}



//$arProductProperties
//$arSkuProductProperties
//CIBlockFormatProperties::GetDisplayValue($arResult, $arNextProperty, "catalog_out");

?>