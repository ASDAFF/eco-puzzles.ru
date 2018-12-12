<?
	if(!empty($arResult["STORES"])){
		foreach ($arResult["STORES"] as $ist => $arStore) {
			if($arStore["REAL_AMOUNT"] > 0){
				$arResult["SHOW_STORES"] = "Y";
				break(1);
			}
		}
	}
?>