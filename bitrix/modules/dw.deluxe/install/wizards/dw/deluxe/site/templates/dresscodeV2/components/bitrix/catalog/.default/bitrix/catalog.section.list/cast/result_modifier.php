<?
	if(!empty($arResult["SECTIONS"])){
		
		$i = 0;
		$b = 0;

		foreach ($arResult["SECTIONS"] as $is => $arSection) {
			if($arSection["DEPTH_LEVEL"] == 1){ 				$i++;
				$arFilter = Array("IBLOCK_ID" => $arParams["IBLOCK_ID"], "ACTIVE" => "Y", "GLOBAL_ACTIVE" => "Y", "ID" => $arSection["ID"]);
  			    $markerQuery = CIBlockSection::GetList(array(), $arFilter, false, array("ID", "IBLOCK_ID", "UF_MARKER"))->GetNext();
				if(!empty($markerQuery)){
					$arSection["UF_MARKER"] = $markerQuery["UF_MARKER"];
				}
				$arResult["ITEMS"][$i] = $arSection;
			}
			elseif($arSection["DEPTH_LEVEL"] == 2){				$b++;
				$arResult["ITEMS"][$i]["ELEMENTS"][$b] = $arSection;
			}elseif($arSection["DEPTH_LEVEL"] == 3){
				$arResult["ITEMS"][$i]["ELEMENTS"][$b]["ELEMENTS"][] = $arSection;
			}
		}

	}
?>