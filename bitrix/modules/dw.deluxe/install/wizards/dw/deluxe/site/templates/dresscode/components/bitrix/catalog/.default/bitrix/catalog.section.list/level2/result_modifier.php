<?if(empty($arResult["SECTIONS"][0])){
		$arFilter = Array(
			"IBLOCK_ID" => $arResult["SECTION"]["IBLOCK_ID"],
			"GLOBAL_ACTIVE" => "Y",
			"ACTIVE" => "Y",
			"SECTION_ID" => $arResult["SECTION"]["PATH"][count($arResult["SECTION"]["PATH"])-2]["ID"],
			"CNT_ACTIVE" => "Y"
		);
		$db_list = CIBlockSection::GetList(array("left_margin"=>"asc"), $arFilter, true);
		while($ar_result = $db_list->GetNext()){
			$arResult["SECTIONS"][] = array(
				"ID" => $ar_result["ID"],
				"SELECTED" => ($arResult["SECTION"]["ID"] == $ar_result["ID"] ? true : false), 
				"SECTION_PAGE_URL" => $ar_result["SECTION_PAGE_URL"], 
				"NAME" => $ar_result["NAME"], 
				"ELEMENT_CNT" => $ar_result["ELEMENT_CNT"]
			);
		}

	}
?>