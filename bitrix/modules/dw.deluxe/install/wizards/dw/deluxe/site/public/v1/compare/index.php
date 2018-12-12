<?
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/header.php");
$APPLICATION->SetTitle("Сравнение товаров");
?>
<?
	//include module
	\Bitrix\Main\Loader::includeModule("dw.deluxe");
	//get template settings
	$arTemplateSettings = DwSettings::getInstance()->getCurrentSettings();
	if(!empty($arTemplateSettings)){
		$catalogIblockId = $arTemplateSettings["TEMPLATE_PRODUCT_IBLOCK_ID"];
	}
?>
<h1>Список сравнения</h1>
<?$APPLICATION->IncludeComponent("dresscode:catalog.compare", ".default", Array(
		"IBLOCK_TYPE" => "#CATALOG_IBLOCK_TYPE#",
		"IBLOCK_ID" => $catalogIblockId,
		"CACHE_TYPE" => "A",	// Тип кеширования
		"CACHE_TIME" => "360000",	// Время кеширования (сек.)
	),
	false
);?><br><?require($_SERVER["DOCUMENT_ROOT"]."/bitrix/footer.php");?>