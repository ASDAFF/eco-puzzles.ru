<?		
	global $USER;

	CModule::IncludeModule('highloadblock');
	use Bitrix\Highloadblock as HL; 
	use Bitrix\Main\Entity;

	if(!isset($arParams["CACHE_TIME"])){
		$arParams["CACHE_TIME"] = 360000000;
	}

	$obCache = new CPHPCache();
	if($obCache->InitCache($arParams["CACHE_TIME"], $USER->GetGroups().SITE_ID."v1", "/")){
	   $arResult = $obCache->GetVars();
	}
	elseif($obCache->StartDataCache()){
		if(!empty($arResult)){

			$arMeasureProductsID = array();
			$i = 0;
			$b = 0;

			foreach($arResult as $arElement){

				if($arElement["DEPTH_LEVEL"] == 1){
					$i++;
					$sectionID = $arElement["PARAMS"]["ID"];
					$IBLOCK_ID = $arElement["PARAMS"]["IBLOCK_ID"];
					$arResult["SECTIONS"][$sectionID] = $sectionID;
					$arResult["ITEMS"][$i] = array(
						"TEXT" => $arElement["TEXT"],
						"LINK" => $arElement["LINK"],
						"ID" => $arElement["PARAMS"]["ID"],
						"SELECTED" => $arElement["SELECTED"],
						"PICTURE" => $arElement["PARAMS"]["PICTURE"],
						"IBLOCK_ID" => $arElement["PARAMS"]["IBLOCK_ID"],
						"ELEMENT_CNT" => $arElement["PARAMS"]["ELEMENT_CNT"]
					);
				}

				elseif($arElement["DEPTH_LEVEL"] == 2){
					$b++;
					$from = $arElement["PARAMS"]["FROM_IBLOCK"] <= 100 ? 1 : 2;
					$arResult["SECTIONS"][$arElement["PARAMS"]["ID"]] = $sectionID;
					$arResult["ITEMS"][$i]["ELEMENTS"][$from][$b] = array(
						"TEXT" => $arElement["TEXT"],
						"LINK" => $arElement["LINK"],
						"SELECTED" => $arElement["SELECTED"],
						"PICTURE" => $arElement["PARAMS"]["PICTURE"],
						"ELEMENT_CNT" => $arElement["PARAMS"]["ELEMENT_CNT"]
					);
				}elseif($arElement["DEPTH_LEVEL"] == 3){
					$arResult["SECTIONS"][$arElement["PARAMS"]["ID"]] = $sectionID;
					$arResult["ITEMS"][$i]["ELEMENTS"][$from][$b]["ELEMENTS"][] = array(
						"TEXT" => $arElement["TEXT"],
						"LINK" => $arElement["LINK"],
						"SELECTED" => $arElement["SELECTED"],
						"ELEMENT_CNT" => $arElement["PARAMS"]["ELEMENT_CNT"]
					);
				}

			}

			//get menu product's from property SHOW_MENU

			if(CModule::IncludeModule("iblock") && CModule::IncludeModule("catalog")){

				$arFilter = array(
					"ACTIVE" => "Y",
					"!PROPERTY_SHOW_MENU_VALUE" => false,
					"PROPERTY_SHOW_MENU_VALUE" => "Y"
				);

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

				$arSelect = array(
					"ID",
					"NAME",
					"IBLOCK_ID",
					"IBLOCK_TYPE",
					"DETAIL_PAGE_URL",
					"IBLOCK_SECTION_ID",
				);
				
				//28 - max product for display in left menu
				$res = CIBlockElement::GetList(array(), $arFilter, false, array("nPageSize" => 28), $arSelect);

				while($arProduct = $res->GetNext()){
					$arResult["PRODUCTS"][$arResult["SECTIONS"][$arProduct["IBLOCK_SECTION_ID"]]][] = $arProduct;
				}
			
			}

		}

	   $obCache->EndDataCache($arResult);

	}
	


?>
