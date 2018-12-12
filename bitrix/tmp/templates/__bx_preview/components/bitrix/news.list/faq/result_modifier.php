<?
	if(!empty($arResult["ITEMS"])){
		
		//vars
		$arSections = array();
		$arSectionsId = array();
		foreach ($arResult["ITEMS"] as $ii => $arNextElement){
			if(!empty($arNextElement["IBLOCK_SECTION_ID"])){
				$arSections[$arNextElement["IBLOCK_SECTION_ID"]]["ITEMS"][$arNextElement["ID"]] = $arNextElement;
				$arSectionsId[$arNextElement["IBLOCK_SECTION_ID"]] = $arNextElement["IBLOCK_SECTION_ID"];
			}
		}

		//get sections data
		if(!empty($arSections) && !empty($arSectionsId)){
			$arFilter = Array("IBLOCK_ID" => $arParams["IBLOCK_ID"], "ID" => $arSectionsId);
			$obSections = CIBlockSection::GetList(Array("sort" => "asc"), $arFilter, false);
			while($arNextSection = $obSections->GetNext()){
				$arNextSection["ITEMS"] = $arSections[$arNextSection["ID"]]["ITEMS"];
				$arResult["SECTIONS"][$arNextSection["ID"]] = $arNextSection;
			}
		}

	}

?>