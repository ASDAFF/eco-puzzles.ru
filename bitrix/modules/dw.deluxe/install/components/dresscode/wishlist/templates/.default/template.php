<?if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true) die();?>
<?$this->setFrameMode(true);?>
<?if($arResult["SHOW_TEMPLATE"]  !== false):?>
<?global $APPLICATION;?>

<?$BASE_PRICE = CCatalogGroup::GetBaseGroup();?>
<?$arSortFields = array(
	"SHOWS" => array(
		"ORDER"=> "DESC",
		"CODE" => "SHOWS",
		"NAME" => GetMessage("CATALOG_SORT_FIELD_SHOWS")
	),	
	"NAME" => array(
		"ORDER"=> "ASC",
		"CODE" => "NAME",
		"NAME" => GetMessage("CATALOG_SORT_FIELD_NAME")
	),
	"PRICE_ASC"=> array(
		"ORDER"=> "ASC",
		"CODE" => "CATALOG_PRICE_".$BASE_PRICE["ID"],
		"NAME" => GetMessage("CATALOG_SORT_FIELD_PRICE_ASC")
	),
	"PRICE_DESC" => array(
		"ORDER"=> "DESC",
		"CODE" => "CATALOG_PRICE_".$BASE_PRICE["ID"],
		"NAME" => GetMessage("CATALOG_SORT_FIELD_PRICE_DESC")
	)
);?>

<?if(!empty($_REQUEST["SORT_FIELD"]) && !empty($arSortFields[$_REQUEST["SORT_FIELD"]])){

	setcookie("CATALOG_SORT_FIELD", $_REQUEST["SORT_FIELD"], time() + 60 * 60 * 24 * 30 * 12 * 2, "/");

	$arParams["ELEMENT_SORT_FIELD"] = $arSortFields[$_REQUEST["SORT_FIELD"]]["CODE"];
	$arParams["ELEMENT_SORT_ORDER"] = $arSortFields[$_REQUEST["SORT_FIELD"]]["ORDER"];	

	$arSortFields[$_REQUEST["SORT_FIELD"]]["SELECTED"] = "Y";

}elseif(!empty($_COOKIE["CATALOG_SORT_FIELD"]) && !empty($arSortFields[$_COOKIE["CATALOG_SORT_FIELD"]])){ // COOKIE
	
	$arParams["ELEMENT_SORT_FIELD"] = $arSortFields[$_COOKIE["CATALOG_SORT_FIELD"]]["CODE"];
	$arParams["ELEMENT_SORT_ORDER"] = $arSortFields[$_COOKIE["ORDER"]];
	
	$arSortFields[$_COOKIE["CATALOG_SORT_FIELD"]]["SELECTED"] = "Y";
}
?>

<?$arSortProductNumber = array(
	30 => array("NAME" => 30), 
	60 => array("NAME" => 60), 
	90 => array("NAME" => 90)
);?>

<?if(!empty($_REQUEST["SORT_TO"]) && $arSortProductNumber[$_REQUEST["SORT_TO"]]){
	setcookie("CATALOG_SORT_TO", $_REQUEST["SORT_TO"], time() + 60 * 60 * 24 * 30 * 12 * 2, "/");
	$arSortProductNumber[$_REQUEST["SORT_TO"]]["SELECTED"] = "Y";
	$arParams["PAGE_ELEMENT_COUNT"] = $_REQUEST["SORT_TO"];
}elseif (!empty($_COOKIE["CATALOG_SORT_TO"]) && $arSortProductNumber[$_COOKIE["CATALOG_SORT_TO"]]){
	$arSortProductNumber[$_COOKIE["CATALOG_SORT_TO"]]["SELECTED"] = "Y";
	$arParams["PAGE_ELEMENT_COUNT"] = $_COOKIE["CATALOG_SORT_TO"];
}?>

<?$arTemplates = array(
	"SQUARES" => array(
		"CLASS" => "squares"
	),
	"LINE" => array(
		"CLASS" => "line"
	),
	"TABLE" => array(
		"CLASS" => "table"
	)	
);?>

<?if(!empty($_REQUEST["VIEW"]) && $arTemplates[$_REQUEST["VIEW"]]){
	setcookie("CATALOG_VIEW", $_REQUEST["VIEW"], time() + 60 * 60 * 24 * 30 * 12 * 2);
	$arTemplates[$_REQUEST["VIEW"]]["SELECTED"] = "Y";
	$arParams["CATALOG_TEMPLATE"] = $_REQUEST["VIEW"];
}elseif (!empty($_COOKIE["CATALOG_VIEW"]) && $arTemplates[$_COOKIE["CATALOG_VIEW"]]){
	$arTemplates[$_COOKIE["CATALOG_VIEW"]]["SELECTED"] = "Y";
	$arParams["CATALOG_TEMPLATE"] = $_COOKIE["CATALOG_VIEW"];
}else{
	$arTemplates[key($arTemplates)]["SELECTED"] = "Y";
}
?>
<div id="catalog">
	<div id="catalogLine">
		<?if(!empty($arSortFields)):?>
			<div class="column">
				<div class="label">
					<?=GetMessage("CATALOG_SORT_LABEL");?>
				</div>
				<select name="sortFields" id="selectSortParams">
					<?foreach ($arSortFields as $arSortFieldCode => $arSortField):?>
						<option value="<?=$APPLICATION->GetCurPageParam("SORT_FIELD=".$arSortFieldCode, array("SORT_FIELD"));?>"<?if($arSortField["SELECTED"] == "Y"):?> selected<?endif;?>><?=$arSortField["NAME"]?></option>
					<?endforeach;?>
				</select>
			</div>
		<?endif;?>
		<?if(!empty($arSortProductNumber)):?>
			<div class="column">
				<div class="label">
					<?=GetMessage("CATALOG_SORT_TO_LABEL");?>
				</div>
				<select name="countElements" id="selectCountElements">
					<?foreach ($arSortProductNumber as $arSortNumberElementId => $arSortNumberElement):?>
						<option value="<?=$APPLICATION->GetCurPageParam("SORT_TO=".$arSortNumberElementId, array("SORT_TO"));?>"<?if($arSortNumberElement["SELECTED"] == "Y"):?> selected<?endif;?>><?=$arSortNumberElement["NAME"]?></option>
					<?endforeach;?>
				</select>
			</div>
		<?endif;?>
		<?if(!empty($arTemplates)):?>
			<div class="column">
				<div class="label">
					<?=GetMessage("CATALOG_VIEW_LABEL");?>
				</div>
				<div class="viewList">
					<?foreach ($arTemplates as $arTemplatesCode => $arNextTemplate):?>
						<div class="element"><a<?if($arNextTemplate["SELECTED"] != "Y"):?> href="<?=$APPLICATION->GetCurPageParam("VIEW=".$arTemplatesCode, array("VIEW"));?>"<?endif;?> class="<?=$arNextTemplate["CLASS"]?><?if($arNextTemplate["SELECTED"] == "Y"):?> selected<?endif;?>"></a></div>
					<?endforeach;?>
				</div>
			</div>
		<?endif;?>
	</div>
	<?reset($arTemplates);?>
	<?global $arrFilter;?>
		<?$APPLICATION->IncludeComponent(
			"bitrix:catalog.section", 
			!empty($arParams["CATALOG_TEMPLATE"]) ? strtolower($arParams["CATALOG_TEMPLATE"]) : strtolower(key($arTemplates)),
			array(
				"IBLOCK_TYPE" => $arParams["IBLOCK_TYPE"],
				"IBLOCK_ID" => $arParams["IBLOCK_ID"],
				"ELEMENT_SORT_FIELD" => $arParams["ELEMENT_SORT_FIELD"],
				"ELEMENT_SORT_ORDER" => $arParams["ELEMENT_SORT_ORDER"],
				"PROPERTY_CODE" => $arParams["PROPERTY_CODE"],
				"PAGE_ELEMENT_COUNT" => $arParams["PAGE_ELEMENT_COUNT"],
				"PRICE_CODE" => $arParams["PRICE_CODE"],
				"PAGER_TEMPLATE" => "round",
				'CONVERT_CURRENCY' => $arParams['CONVERT_CURRENCY'],
				'CURRENCY_ID' => $arParams['CURRENCY_ID'],
				"FILTER_NAME" => $arParams["FILTER_NAME"],
				"HIDE_MEASURES" => $arParams["HIDE_MEASURES"],
				"ADD_SECTIONS_CHAIN" => "N",
				"SHOW_ALL_WO_SECTION" => "Y"
			),
			false
		);?>
</div>
<?else:?>
	Пока нет ничего
<?endif;?>
