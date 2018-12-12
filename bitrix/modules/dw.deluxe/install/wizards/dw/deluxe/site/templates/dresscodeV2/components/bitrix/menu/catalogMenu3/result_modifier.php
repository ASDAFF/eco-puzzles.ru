<?
	$obCache = new CPHPCache();
	if($obCache->InitCache(3600000000, SITE_ID."v2", "/")){
	   $arResult = $obCache->GetVars();
	}
	elseif($obCache->StartDataCache()){
		if(!empty($arResult)){
			
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
						"BIG_PICTURE" => $arElement["PARAMS"]["BIG_PICTURE"],
						"DETAIL_PICTURE" => $arElement["PARAMS"]["DETAIL_PICTURE"],
						"IBLOCK_ID" => $arElement["PARAMS"]["IBLOCK_ID"],
						"ELEMENT_CNT" => $arElement["PARAMS"]["ELEMENT_CNT"]
					);
				}

				elseif($arElement["DEPTH_LEVEL"] == 2){
					$b++;
					if($arElement["PARAMS"]["FROM_IBLOCK"] <= 50){
						$from = 1;
					}elseif($arElement["PARAMS"]["FROM_IBLOCK"] <= 100){
						$from = 2;
					}elseif($arElement["PARAMS"]["FROM_IBLOCK"] <= 150){
						$from = 3;
					}elseif($arElement["PARAMS"]["FROM_IBLOCK"] <= 200){
						$from = 4;
					}else{
						$from = 1;
					}
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
		}
	   $obCache->EndDataCache($arResult);
	}
	


?>
