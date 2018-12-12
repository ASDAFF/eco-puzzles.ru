<?
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/header.php");
$APPLICATION->SetTitle("Yandex Market");
//global $ymFilter; $ymFilter = array("!PROPERTY_WF_SALE" => false);
?> 
<?$APPLICATION->IncludeComponent(
	"webfly:yandex.market", 
	".default", 
	array(
		"IBLOCK_TYPE" => "",
		"IBLOCK_ID_IN" => array(
			0 => "15",
		),
		"IBLOCK_ID_EX" => array(
			0 => "0",
		),
		"IBLOCK_SECTION" => array(
			0 => "254",
			1 => "263",
			2 => "265",
			3 => "283",
			4 => "284",
			5 => "312",
		),
		"SITE" => "eco-puzzles.ru",
		"COMPANY" => "Eco Puzzles",
		"FILTER_NAME" => "ymFilter",
		"MORE_PHOTO" => "MORE_PHOTO",
		"CACHE_TYPE" => "A",
		"CACHE_TIME" => "86400",
		"CACHE_FILTER" => "Y",
		"PRICE_CODE" => array(
			0 => "Основное соглашение",
		),
		"IBLOCK_ORDER" => "Y",
		"CURRENCY" => "RUR",
		"LOCAL_DELIVERY_COST" => "150",
		"COMPONENT_TEMPLATE" => ".default",
		"AGENT_CHECK" => "Y",
		"IBLOCK_TYPE_LIST" => array(
			0 => "catalog",
		),
		"SAVE_IN_FILE" => "Y",
		"IBLOCK_CATALOG" => "Y",
		"DONT_USE_SKU" => "Y",
		"CATEGORY_NAME_PROPERTY" => "",
		"DO_NOT_INCLUDE_SUBSECTIONS" => "N",
		"IBLOCK_AS_CATEGORY" => "N",
		"ECHO_ADMIN_INFO" => "N",
		"BIG_CATALOG_PROP" => "300",
		"HTTPS_CHECK" => "Y",
		"USE_SITE" => "N",
		"FILTER_NAME_SKU" => "arrFilterSku",
		"URL_PROPERTY_CHECK" => "N",
		"USE_ONLY_PROP_PICTURE" => "N",
		"GET_OVER_FIELDS_ANONCE" => "Y",
		"PHOTO_CHECK" => "N",
		"CACHE_NON_MANAGED" => "N",
		"RECOMMENDATION" => "0",
		"COMPOSITE_FRAME_MODE" => "A",
		"COMPOSITE_FRAME_TYPE" => "AUTO",
		"SALES_NOTES" => "0",
		"SALES_NOTES_TEXT" => "",
		"SKU_NAME" => "PRODUCT_AND_SKU_NAME",
		"SKU_PROPERTY" => "PROPERTY_CML2_LINK",
		"SKU_SHOW_GROUP_ID" => "Y",
		"OLD_PRICE" => "N",
		"DONT_CHECK_PRICE_RIGHTS" => "N",
		"DISCOUNTS" => "DISCOUNT_CUSTOM",
		"PURCHASE_PRICE_CODE" => "WEBFLY_PURCHASE_PRICE",
		"AVAILABLE_ALGORITHM" => "BITRIX_ALGORITHM",
		"CURRENCIES_CONVERT" => "NOT_CONVERT",
		"PRICE_ROUND" => "N",
		"MINIMUM_PRICE_ROUND" => "0",
		"TYPE_PRICE_ROUND" => "MATH",
		"ACCURACY_PRICE_ROUND" => "9",
		"DELIVERY_OPTIONS_SHOP_EX" => "",
		"LOCAL_DELIVERY_COST_OFFER" => "0",
		"DELIVERY_OPTIONS_EX" => "",
		"DELIVERY_TO_AVAILABLE" => "N",
		"STORE_OFFER" => "",
		"STORE_PICKUP" => "",
		"STORE_DELIVERY" => "",
		"OUTLETS" => "",
		"NAME_PROP" => "0",
		"NAME_PROP_COMPILE" => "",
		"NAME_CUT" => "",
		"PREFIX_PROP" => "0",
		"AGE_CATEGORY" => "0",
		"AGE_CATEGORY_UNIT" => "year",
		"ADULT_ALL" => "N",
		"ADULT" => "",
		"EXPIRY" => "0",
		"WEIGHT" => "WEBFLY_WEIGHT",
		"DIMENSIONS" => "GABARITY",
		"BARCODE" => "0",
		"PARAMS" => array(
			0 => "ATT_BRAND",
			1 => "DLINA",
			2 => "MATERIAL",
			3 => "POL",
			4 => "SERIYA",
			5 => "SERTIFIKAT",
			6 => "SHIRINA",
			7 => "STRANA",
			8 => "VES",
			9 => "VOZRAST",
			10 => "VYSOTA",
		),
		"COND_PARAMS" => array(
			0 => "0",
		),
		"NO_DESCRIPTION" => "N",
		"PROPDUCT_PROP" => array(
		),
		"OFFER_PROP" => array(
		),
		"DESCRIPTION" => "0",
		"DETAIL_TEXT_PRIORITET" => "Y",
		"DESCRIPTION_XHTML" => "Y",
		"UTM_CHECK" => "N",
		"UTM_SOURCE" => "YandexMarket",
		"UTM_CAMPAIGN" => "",
		"UTM_MEDIUM" => "cpc",
		"UTM_TERM" => "",
		"MODEL" => "0",
		"MARKET_CATEGORY_PROP" => "",
		"DEVELOPER" => "ATT_BRAND",
		"VENDOR_CODE" => "CML2_ARTICLE_",
		"COUNTRY" => "STRANA",
		"MANUFACTURER_WARRANTY" => "",
		"YM_PROMO_FLASH_DISCOUNT" => "N"
	),
	false
);?>
<?require($_SERVER["DOCUMENT_ROOT"]."/bitrix/footer.php");?>
