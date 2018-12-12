<?require($_SERVER["DOCUMENT_ROOT"]."/bitrix/header.php");
$APPLICATION->SetTitle("Новинки");?>
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
<h1>Уцененные товары</h1><?$APPLICATION->IncludeComponent(
	"bitrix:menu", 
	"personal", 
	array(
		"COMPONENT_TEMPLATE" => "personal",
		"ROOT_MENU_TYPE" => "left2",
		"MENU_CACHE_TYPE" => "A",
		"MENU_CACHE_TIME" => "3600000",
		"MENU_CACHE_USE_GROUPS" => "Y",
		"MENU_CACHE_GET_VARS" => array(
		),
		"MAX_LEVEL" => "1",
		"CHILD_MENU_TYPE" => "",
		"USE_EXT" => "N",
		"DELAY" => "N",
		"ALLOW_MULTI_SELECT" => "N"
	),
	false
);?><?$APPLICATION->IncludeComponent(
	"dresscode:simple.offers", 
	".default", 
	array(
		"COMPONENT_TEMPLATE" => ".default",
		"IBLOCK_TYPE" => "#CATALOG_IBLOCK_TYPE#",
		"IBLOCK_ID" => $catalogIblockId,
		"PRICE_CODE" => $arPriceCodes,
		"CACHE_TYPE" => "A",
		"CACHE_TIME" => "3600000",
		"PROP_NAME" => "OFFERS",
		"PROP_VALUE" => "604",
		"CONVERT_CURRENCY" => "Y",
		"PROPERTY_CODE" => array(
			0 => "OFFERS",
			1 => "ATT_BRAND",
			2 => "COLOR",
			3 => "ZOOM2",
			4 => "BATTERY_LIFE",
			5 => "SWITCH",
			6 => "GRAF_PROC",
			7 => "LENGTH_OF_CORD",
			8 => "DISPLAY",
			9 => "LOADING_LAUNDRY",
			10 => "FULL_HD_VIDEO_RECORD",
			11 => "INTERFACE",
			12 => "COMPRESSORS",
			13 => "Number_of_Outlets",
			14 => "MAX_RESOLUTION_VIDEO",
			15 => "MAX_BUS_FREQUENCY",
			16 => "MAX_RESOLUTION",
			17 => "FREEZER",
			18 => "POWER_SUB",
			19 => "POWER",
			20 => "HARD_DRIVE_SPACE",
			21 => "MEMORY",
			22 => "OS",
			23 => "ZOOM",
			24 => "PAPER_FEED",
			25 => "SUPPORTED_STANDARTS",
			26 => "VIDEO_FORMAT",
			27 => "SUPPORT_2SIM",
			28 => "MP3",
			29 => "ETHERNET_PORTS",
			30 => "MATRIX",
			31 => "CAMERA",
			32 => "PHOTOSENSITIVITY",
			33 => "DEFROST",
			34 => "SPEED_WIFI",
			35 => "SPIN_SPEED",
			36 => "PRINT_SPEED",
			37 => "SOCKET",
			38 => "IMAGE_STABILIZER",
			39 => "GSM",
			40 => "SIM",
			41 => "TYPE",
			42 => "MEMORY_CARD",
			43 => "TYPE_BODY",
			44 => "TYPE_MOUSE",
			45 => "TYPE_PRINT",
			46 => "CONNECTION",
			47 => "TYPE_OF_CONTROL",
			48 => "TYPE_DISPLAY",
			49 => "TYPE2",
			50 => "REFRESH_RATE",
			51 => "RANGE",
			52 => "AMOUNT_MEMORY",
			53 => "MEMORY_CAPACITY",
			54 => "VIDEO_BRAND",
			55 => "DIAGONAL",
			56 => "RESOLUTION",
			57 => "TOUCH",
			58 => "CORES",
			59 => "LINE_PROC",
			60 => "PROCESSOR",
			61 => "CLOCK_SPEED",
			62 => "TYPE_PROCESSOR",
			63 => "PROCESSOR_SPEED",
			64 => "HARD_DRIVE",
			65 => "HARD_DRIVE_TYPE",
			66 => "Number_of_memory_slots",
			67 => "MAXIMUM_MEMORY_FREQUENCY",
			68 => "TYPE_MEMORY",
			69 => "BLUETOOTH",
			70 => "FM",
			71 => "GPS",
			72 => "HDMI",
			73 => "SMART_TV",
			74 => "USB",
			75 => "WIFI",
			76 => "FLASH",
			77 => "ROTARY_DISPLAY",
			78 => "SUPPORT_3D",
			79 => "SUPPORT_3G",
			80 => "WITH_COOLER",
			81 => "FINGERPRINT",
			82 => "COLLECTION",
			83 => "TOTAL_OUTPUT_POWER",
			84 => "HTML",
			85 => "VID_ZASTECHKI",
			86 => "VID_SUMKI",
			87 => "VIDEO",
			88 => "PROFILE",
			89 => "VYSOTA_RUCHEK",
			90 => "GAS_CONTROL",
			91 => "WARRANTY",
			92 => "GRILL",
			93 => "MORE_PROPERTIES",
			94 => "GENRE",
			95 => "OTSEKOV",
			96 => "CONVECTION",
			97 => "INTAKE_POWER",
			98 => "NAZNAZHENIE",
			99 => "BULK",
			100 => "PODKLADKA",
			101 => "SURFACE_COATING",
			102 => "brand_tyres",
			103 => "SEASON",
			104 => "SEASONOST",
			105 => "DUST_COLLECTION",
			106 => "REF",
			107 => "COUNTRY_BRAND",
			108 => "DRYING",
			109 => "REMOVABLE_TOP_COVER",
			110 => "CONTROL",
			111 => "FINE_FILTER",
			112 => "FORM_FAKTOR",
			113 => "SKU_COLOR",
			114 => "CML2_ARTICLE",
			115 => "DELIVERY",
			116 => "PICKUP",
			117 => "USER_ID",
			118 => "BLOG_POST_ID",
			119 => "BLOG_COMMENTS_CNT",
			120 => "VOTE_COUNT",
			121 => "SHOW_MENU",
			122 => "SIMILAR_PRODUCT",
			123 => "RATING",
			124 => "RELATED_PRODUCT",
			125 => "VOTE_SUM",
			126 => "MARKER_PHOTO",
			127 => "NEW",
			128 => "DELIVERY_DESC",
			129 => "SALE",
			130 => "MARKER",
			131 => "POPULAR",
			132 => "WEIGHT",
			133 => "HEIGHT",
			134 => "DEPTH",
			135 => "WIDTH",
			136 => "",
		),
		"CURRENCY_ID" => "RUB"
	),
	false,
	array(
		"ACTIVE_COMPONENT" => "Y"
	)
);?><?require($_SERVER["DOCUMENT_ROOT"]."/bitrix/footer.php");?>