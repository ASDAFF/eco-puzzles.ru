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
<div id="footerTabsCaption">
	<div class="wrapper">
		<div class="items">
			<?$APPLICATION->ShowViewContent("catalog_top_view_content_tab");?>
			<?$APPLICATION->ShowViewContent("sale_viewed_product_view_content_tab");?>
		</div>
	</div>
</div>
<div id="footerTabs">
	<div class="wrapper">
		<div class="items">
			<?$APPLICATION->IncludeComponent(
	"dresscode:products.by.filter", 
	".default", 
	array(
		"COMPONENT_TEMPLATE" => ".default",
		"IBLOCK_TYPE" => "catalog",
		"IBLOCK_ID" => $catalogIblockId,
		"HIDE_NOT_AVAILABLE" => "N",
		"HIDE_MEASURES" => "N",
		"PROP_NAME" => "OFFERS",
		"ELEMENTS_COUNT" => "20",
		"SORT_PROPERTY_NAME" => "RAND",
		"SORT_VALUE" => "DESC",
		"PICTURE_WIDTH" => "220",
		"PICTURE_HEIGHT" => "200",
		"PRODUCT_PRICE_CODE" => $arPriceCodes,
		"CONVERT_CURRENCY" => "N",
		"CACHE_TYPE" => "A",
		"CACHE_TIME" => "36000000",
		"FILTER_TYPE" => "BESTSELLERS",
		"PROP_VALUE" => "540",
		"SECTION_ID" => "437",
		"COMPONENT_TITLE" => "",
		"CURRENCY_ID" => "RUB",
		"ADAPTIVE_VERSION" => "V2"
	),
	false,
	array(
		"ACTIVE_COMPONENT" => "Y"
	)
);?>
			<?$APPLICATION->IncludeComponent(
	"dresscode:catalog.viewed.product", 
	".default", 
	array(
		"COMPONENT_TEMPLATE" => ".default",
		"VIEWED_COUNT" => "10",
		"VIEWED_NAME" => "Y",
		"VIEWED_IMAGE" => "Y",
		"VIEWED_PRICE" => "Y",
		"VIEWED_CURRENCY" => "default",
		"VIEWED_CANBUY" => "N",
		"VIEWED_CANBASKET" => "N",
		"VIEWED_IMG_HEIGHT" => "150",
		"VIEWED_IMG_WIDTH" => "150",
		"BASKET_URL" => "/personal/basket.php",
		"ACTION_VARIABLE" => "action",
		"PRODUCT_ID_VARIABLE" => "id",
		"SET_TITLE" => "N",
		"IBLOCK_TYPE" => "catalog",
		"IBLOCK_ID" => $catalogIblockId,
		"HIDE_NOT_AVAILABLE" => "N",
		"HIDE_MEASURES" => "N",
		"PROP_NAME" => "OFFERS",
		"ELEMENTS_COUNT" => "10",
		"SORT_PROPERTY_NAME" => "timestamp_x",
		"SORT_VALUE" => "DESC",
		"PICTURE_WIDTH" => "220",
		"PICTURE_HEIGHT" => "200",
		"PRODUCT_PRICE_CODE" => $arPriceCodes,
		"CONVERT_CURRENCY" => "N",
		"CACHE_TYPE" => "A",
		"CACHE_TIME" => "12",
		"CURRENCY_ID" => "RUB",
		"ADAPTIVE_VERSION" => "V2"
	),
	false,
	array(
		"ACTIVE_COMPONENT" => "Y"
	)
);?>

		</div>
	</div>
</div>

<?$APPLICATION->IncludeComponent(
	"bitrix:catalog.bigdata.products", 
	".default", 
	array(
		"COMPONENT_TEMPLATE" => ".default",
		"RCM_TYPE" => "personal",
		"ID" => $_REQUEST["PRODUCT_ID"],
		"IBLOCK_TYPE" => "catalog",
		"IBLOCK_ID" => $catalogIblockId,
		"SHOW_FROM_SECTION" => "N",
		"SECTION_ID" => "",
		"SECTION_CODE" => "",
		"SECTION_ELEMENT_ID" => "",
		"SECTION_ELEMENT_CODE" => "",
		"DEPTH" => "",
		"HIDE_NOT_AVAILABLE" => "N",
		"SHOW_DISCOUNT_PERCENT" => "Y",
		"PRODUCT_SUBSCRIPTION" => "N",
		"SHOW_NAME" => "Y",
		"SHOW_IMAGE" => "Y",
		"MESS_BTN_BUY" => "",
		"MESS_BTN_DETAIL" => "",
		"MESS_BTN_SUBSCRIBE" => "",
		"PAGE_ELEMENT_COUNT" => "10",
		"LINE_ELEMENT_COUNT" => "3",
		"TEMPLATE_THEME" => "blue",
		"DETAIL_URL" => "",
		"CACHE_TYPE" => "Y",
		"CACHE_TIME" => "36000000",
		"CACHE_GROUPS" => "Y",
		"SHOW_OLD_PRICE" => "N",
		"PRICE_CODE" => $arPriceCodes,
		"SHOW_PRICE_COUNT" => "1",
		"PRICE_VAT_INCLUDE" => "Y",
		"CONVERT_CURRENCY" => "Y",
		"BASKET_URL" => "/personal/basket.php",
		"ACTION_VARIABLE" => "action_cbdp",
		"PRODUCT_ID_VARIABLE" => "id",
		"PRODUCT_QUANTITY_VARIABLE" => "",
		"ADD_PROPERTIES_TO_BASKET" => "Y",
		"PRODUCT_PROPS_VARIABLE" => "prop",
		"PARTIAL_PRODUCT_PROPERTIES" => "N",
		"USE_PRODUCT_QUANTITY" => "N",
		"SHOW_PRODUCTS_17" => "Y",
		"PROPERTY_CODE_17" => array(
			0 => "",
			1 => "",
		),
		"CART_PROPERTIES_17" => array(
			0 => "",
			1 => "",
		),
		"ADDITIONAL_PICT_PROP_17" => "MORE_PHOTO",
		"LABEL_PROP_17" => "-",
		"PROPERTY_CODE_18" => array(
			0 => "",
			1 => "",
		),
		"CART_PROPERTIES_18" => array(
			0 => "",
			1 => "",
		),
		"ADDITIONAL_PICT_PROP_18" => "MORE_PHOTO",
		"OFFER_TREE_PROPS_18" => array(
		),
		"CURRENCY_ID" => "RUB"
	),
	false
);?>