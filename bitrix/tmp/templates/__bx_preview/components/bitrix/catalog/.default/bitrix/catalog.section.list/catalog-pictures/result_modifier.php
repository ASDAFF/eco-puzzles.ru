<?
if(!empty($arResult["SECTIONS"])){
	foreach ($arResult["SECTIONS"]  as $inc => $arNextSection) {
		if(empty($arNextSection["PICTURE"])){
			unset($arResult["SECTIONS"][$inc]);
		}
	}
}?>