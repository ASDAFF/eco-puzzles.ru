<?require($_SERVER["DOCUMENT_ROOT"]."/bitrix/header.php");?>
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
<?$APPLICATION->SetTitle("Блог");?><?$APPLICATION->IncludeComponent(
	"bitrix:news", 
	"blog", 
	array(
		"ADD_ELEMENT_CHAIN" => "Y",
		"ADD_SECTIONS_CHAIN" => "N",
		"AJAX_MODE" => "N",
		"AJAX_OPTION_ADDITIONAL" => "",
		"AJAX_OPTION_HISTORY" => "N",
		"AJAX_OPTION_JUMP" => "N",
		"AJAX_OPTION_STYLE" => "Y",
		"BROWSER_TITLE" => "-",
		"CACHE_FILTER" => "N",
		"CACHE_GROUPS" => "Y",
		"CACHE_TIME" => "36000000",
		"CACHE_TYPE" => "A",
		"CHECK_DATES" => "Y",
		"DETAIL_ACTIVE_DATE_FORMAT" => "d.M.Y",
		"DETAIL_DISPLAY_BOTTOM_PAGER" => "Y",
		"DETAIL_DISPLAY_TOP_PAGER" => "N",
		"DETAIL_FIELD_CODE" => array(
			0 => "ID",
			1 => "CODE",
			2 => "XML_ID",
			3 => "NAME",
			4 => "TAGS",
			5 => "SORT",
			6 => "PREVIEW_TEXT",
			7 => "PREVIEW_PICTURE",
			8 => "DETAIL_TEXT",
			9 => "DETAIL_PICTURE",
			10 => "DATE_ACTIVE_FROM",
			11 => "ACTIVE_FROM",
			12 => "DATE_ACTIVE_TO",
			13 => "ACTIVE_TO",
			14 => "SHOW_COUNTER",
			15 => "SHOW_COUNTER_START",
			16 => "IBLOCK_TYPE_ID",
			17 => "IBLOCK_ID",
			18 => "IBLOCK_CODE",
			19 => "IBLOCK_NAME",
			20 => "IBLOCK_EXTERNAL_ID",
			21 => "DATE_CREATE",
			22 => "CREATED_BY",
			23 => "CREATED_USER_NAME",
			24 => "TIMESTAMP_X",
			25 => "MODIFIED_BY",
			26 => "USER_NAME",
			27 => "",
		),
		"DETAIL_PAGER_SHOW_ALL" => "Y",
		"DETAIL_PAGER_TEMPLATE" => "",
		"DETAIL_PAGER_TITLE" => "Страница",
		"DETAIL_PROPERTY_CODE" => array(
			0 => "",
			1 => "",
		),
		"DETAIL_SET_CANONICAL_URL" => "N",
		"DISPLAY_BOTTOM_PAGER" => "Y",
		"DISPLAY_NAME" => "Y",
		"DISPLAY_TOP_PAGER" => "N",
		"HIDE_LINK_WHEN_NO_DETAIL" => "N",
		"IBLOCK_ID" => "#BLOG_IBLOCK_ID#",
		"IBLOCK_TYPE" => "#BLOG_IBLOCK_TYPE#",
		"INCLUDE_IBLOCK_INTO_CHAIN" => "N",
		"LIST_ACTIVE_DATE_FORMAT" => "d.M.Y",
		"LIST_FIELD_CODE" => array(
			0 => "ID",
			1 => "CODE",
			2 => "XML_ID",
			3 => "NAME",
			4 => "TAGS",
			5 => "SORT",
			6 => "PREVIEW_TEXT",
			7 => "PREVIEW_PICTURE",
			8 => "DETAIL_TEXT",
			9 => "DETAIL_PICTURE",
			10 => "DATE_ACTIVE_FROM",
			11 => "ACTIVE_FROM",
			12 => "DATE_ACTIVE_TO",
			13 => "ACTIVE_TO",
			14 => "SHOW_COUNTER",
			15 => "SHOW_COUNTER_START",
			16 => "IBLOCK_TYPE_ID",
			17 => "IBLOCK_ID",
			18 => "IBLOCK_CODE",
			19 => "IBLOCK_NAME",
			20 => "IBLOCK_EXTERNAL_ID",
			21 => "DATE_CREATE",
			22 => "CREATED_BY",
			23 => "CREATED_USER_NAME",
			24 => "TIMESTAMP_X",
			25 => "MODIFIED_BY",
			26 => "USER_NAME",
			27 => "",
		),
		"LIST_PROPERTY_CODE" => array(
			0 => "",
			1 => "",
		),
		"MESSAGE_404" => "",
		"META_DESCRIPTION" => "-",
		"META_KEYWORDS" => "-",
		"NEWS_COUNT" => "15",
		"PAGER_BASE_LINK_ENABLE" => "N",
		"PAGER_DESC_NUMBERING" => "N",
		"PAGER_DESC_NUMBERING_CACHE_TIME" => "3600000",
		"PAGER_SHOW_ALL" => "N",
		"PAGER_SHOW_ALWAYS" => "N",
		"PAGER_TEMPLATE" => "round",
		"PAGER_TITLE" => "Блог",
		"PREVIEW_TRUNCATE_LEN" => "",
		"SEF_FOLDER" => "#SITE_DIR#blog/",
		"SEF_MODE" => "Y",
		"SET_LAST_MODIFIED" => "Y",
		"SET_STATUS_404" => "N",
		"SET_TITLE" => "Y",
		"SHOW_404" => "N",
		"SORT_BY1" => "ID",
		"SORT_BY2" => "ID",
		"SORT_ORDER1" => "ASC",
		"SORT_ORDER2" => "ASC",
		"STRICT_SECTION_CHECK" => "N",
		"USE_CATEGORIES" => "N",
		"USE_FILTER" => "N",
		"USE_PERMISSIONS" => "N",
		"USE_RATING" => "N",
		"USE_REVIEW" => "N",
		"USE_RSS" => "N",
		"USE_SEARCH" => "N",
		"COMPONENT_TEMPLATE" => "blog",
		"HIDE_NOT_AVAILABLE" => "N",
		"PRODUCT_IBLOCK_TYPE" => "#CATALOG_IBLOCK_TYPE#",
		"PRODUCT_IBLOCK_ID" => $catalogIblockId,
		"PRODUCT_FILTER_NAME" => "arrFilter",
		"PRODUCT_PROPERTY_CODE" => array(
			0 => "OFFERS",
			1 => "ATT_BRAND",
			2 => "PRODUCT_DAY",
			3 => "COLOR",
			4 => "TIMER_START_DATE",
			5 => "TIMER_DATE",
			6 => "TIMER_LOOP",
			7 => "ZOOM2",
			8 => "BATTERY_LIFE",
			9 => "SWITCH",
			10 => "GRAF_PROC",
			11 => "LENGTH_OF_CORD",
			12 => "DISPLAY",
			13 => "LOADING_LAUNDRY",
			14 => "FULL_HD_VIDEO_RECORD",
			15 => "INTERFACE",
			16 => "COMPRESSORS",
			17 => "Number_of_Outlets",
			18 => "MAX_RESOLUTION_VIDEO",
			19 => "MAX_BUS_FREQUENCY",
			20 => "MAX_RESOLUTION",
			21 => "FREEZER",
			22 => "POWER_SUB",
			23 => "POWER",
			24 => "HARD_DRIVE_SPACE",
			25 => "MEMORY",
			26 => "OS",
			27 => "ZOOM",
			28 => "PAPER_FEED",
			29 => "SUPPORTED_STANDARTS",
			30 => "VIDEO_FORMAT",
			31 => "SUPPORT_2SIM",
			32 => "MP3",
			33 => "ETHERNET_PORTS",
			34 => "MATRIX",
			35 => "CAMERA",
			36 => "PHOTOSENSITIVITY",
			37 => "DEFROST",
			38 => "SPEED_WIFI",
			39 => "SPIN_SPEED",
			40 => "PRINT_SPEED",
			41 => "SOCKET",
			42 => "IMAGE_STABILIZER",
			43 => "GSM",
			44 => "SIM",
			45 => "TYPE",
			46 => "MEMORY_CARD",
			47 => "TYPE_BODY",
			48 => "TYPE_MOUSE",
			49 => "TYPE_PRINT",
			50 => "CONNECTION",
			51 => "TYPE_OF_CONTROL",
			52 => "TYPE_DISPLAY",
			53 => "TYPE2",
			54 => "REFRESH_RATE",
			55 => "RANGE",
			56 => "AMOUNT_MEMORY",
			57 => "MEMORY_CAPACITY",
			58 => "VIDEO_BRAND",
			59 => "DIAGONAL",
			60 => "RESOLUTION",
			61 => "TOUCH",
			62 => "CORES",
			63 => "LINE_PROC",
			64 => "PROCESSOR",
			65 => "CLOCK_SPEED",
			66 => "TYPE_PROCESSOR",
			67 => "PROCESSOR_SPEED",
			68 => "HARD_DRIVE",
			69 => "HARD_DRIVE_TYPE",
			70 => "Number_of_memory_slots",
			71 => "MAXIMUM_MEMORY_FREQUENCY",
			72 => "TYPE_MEMORY",
			73 => "BLUETOOTH",
			74 => "FM",
			75 => "GPS",
			76 => "HDMI",
			77 => "SMART_TV",
			78 => "USB",
			79 => "WIFI",
			80 => "FLASH",
			81 => "ROTARY_DISPLAY",
			82 => "SUPPORT_3D",
			83 => "SUPPORT_3G",
			84 => "WITH_COOLER",
			85 => "FINGERPRINT",
			86 => "VOZRAST",
			87 => "ENERGOPOTREB",
			88 => "OBOROTY",
			89 => "MINI_BAR",
			90 => "SIZES_PRODUCT",
			91 => "DISPLAY_TYPE",
			92 => "TIP_ELEMENTOV_PITANIA",
			93 => "BELKI",
			94 => "ZHIRY",
			95 => "CALORIES",
			96 => "COLLECTION",
			97 => "UGLEVODY",
			98 => "TOTAL_OUTPUT_POWER",
			99 => "VID_ZASTECHKI",
			100 => "VID_SUMKI",
			101 => "PROFILE",
			102 => "VYSOTA_RUCHEK",
			103 => "GAS_CONTROL",
			104 => "WARRANTY",
			105 => "GRILL",
			106 => "MORE_PROPERTIES",
			107 => "GENRE",
			108 => "OTSEKOV",
			109 => "CONVECTION",
			110 => "MATERIAL",
			111 => "INTAKE_POWER",
			112 => "NAZNAZHENIE",
			113 => "BULK",
			114 => "PODKLADKA",
			115 => "SURFACE_COATING",
			116 => "brand_tyres",
			117 => "SEASON",
			118 => "SEASONOST",
			119 => "DUST_COLLECTION",
			120 => "REF",
			121 => "COUNTRY_BRAND",
			122 => "DRYING",
			123 => "REMOVABLE_TOP_COVER",
			124 => "TEST_TEST",
			125 => "CONTROL",
			126 => "FINE_FILTER",
			127 => "FORM_FAKTOR",
			128 => "SKU_COLOR",
			129 => "TESTING",
			130 => "CML2_ARTICLE",
			131 => "DELIVERY",
			132 => "PICKUP",
			133 => "USER_ID",
			134 => "BLOG_POST_ID",
			135 => "VIDEO",
			136 => "BLOG_COMMENTS_CNT",
			137 => "VOTE_COUNT",
			138 => "SHOW_MENU",
			139 => "SIMILAR_PRODUCT",
			140 => "RATING",
			141 => "RELATED_PRODUCT",
			142 => "VOTE_SUM",
			143 => "MAXIMUM_PRICE",
			144 => "MINIMUM_PRICE",
			145 => "",
		),
		"PRODUCT_PRICE_CODE" => $arPriceCodes,
		"HIDE_MEASURES" => "N",
		"PRODUCT_CONVERT_CURRENCY" => "Y",
		"PRODUCT_CURRENCY_ID" => "RUB",
		"SHOW_BLOG_COMMENTS" => "Y",
		"SEF_URL_TEMPLATES" => array(
			"news" => "",
			"section" => "",
			"detail" => "#ELEMENT_CODE#/",
		)
	),
	false
);?><?require($_SERVER["DOCUMENT_ROOT"]."/bitrix/footer.php");?>