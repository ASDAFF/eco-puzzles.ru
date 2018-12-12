<?
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/header.php");
$APPLICATION->SetTitle("Сравнение товаров");
?><h1>Список сравнения</h1>
<?
	//include module
	\Bitrix\Main\Loader::includeModule("dw.deluxe");

	//vars
	$catalogIblockId = null;
	$arPriceCodes = array();

	//get template settings
	$arTemplateSettings = DwSettings::getInstance()->getCurrentSettings();
	if(!empty($arTemplateSettings)){
		$catalogIblockId = $arTemplateSettings["TEMPLATE_PRODUCT_IBLOCK_ID"];
		$arPriceCodes = explode(", ", $arTemplateSettings["TEMPLATE_PRICE_CODES"]);
	}
?>
<?$APPLICATION->IncludeComponent("dresscode:catalog.compare", ".default", Array(
		"IBLOCK_TYPE" => "#CATALOG_IBLOCK_TYPE#",
		"IBLOCK_ID" => $catalogIblockId,
		"CACHE_TYPE" => "A",	// Тип кеширования
		"CACHE_TIME" => "360000",	// Время кеширования (сек.)
	),
	false
);?><br><?require($_SERVER["DOCUMENT_ROOT"]."/bitrix/footer.php");?>