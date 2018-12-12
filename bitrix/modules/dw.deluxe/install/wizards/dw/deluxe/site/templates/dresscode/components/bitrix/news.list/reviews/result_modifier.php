<?if(!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED!==true)die();
global $USER;
$cacheID = array($USER->GetGroups());
$this->__component->arResultCacheKeys = array_merge($this->__component->arResultCacheKeys, $cacheID);
?>
<?
	if(!empty($arResult["ITEMS"])){

		$arResult["COUNT_RATING_ITEMS"] = 0;
		$arResult["RATING_SUM"] = 0;

		$arSelect = Array("ID", "NAME", "PROPERTY_RATING");
		$arFilter = Array("IBLOCK_TYPE" => $arParams["IBLOCK_TYPE"], "IBLOCK_ID" => $arParams["IBLOCK_ID"], "ACTIVE_DATE" => "Y", "ACTIVE" => "Y");
		$res = CIBlockElement::GetList(Array(), $arFilter, false, false, $arSelect);

		while($arNextElement = $res->GetNext()){
			if(!empty($arNextElement["PROPERTY_RATING_VALUE"])){
				$arResult["RATING_SUM"] += $arNextElement["PROPERTY_RATING_VALUE"];
				$arResult["COUNT_RATING_ITEMS"]++;
			}
		}

		$arResult["RATING_SUM"] = round($arResult["RATING_SUM"] / $arResult["COUNT_RATING_ITEMS"]);

		foreach ($arResult["ITEMS"] as $inx => $arNextElement) {
			if(empty($arNextElement["PROPERTIES"]["USER_NAME"]["VALUE"])){
				$arResult["ITEMS"][$inx]["PROPERTIES"]["USER_NAME"]["VALUE"] = GetMessage("SHOP_REVIEW_AUTHOR_DEFAULT");
			}
			if(!empty($arNextElement["PROPERTIES"]["DATE"]["VALUE"])){
				$arResult["ITEMS"][$inx]["DATE_CREATE"] = $arNextElement["PROPERTIES"]["DATE"]["VALUE"];
			}
		}
	}

	$rsUser = CUser::GetByID($USER->GetID());
	$arResult["USER"] = $rsUser->Fetch();

?>