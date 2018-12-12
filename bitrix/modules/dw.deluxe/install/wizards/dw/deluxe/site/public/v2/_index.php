<?define("INDEX_PAGE", "Y");?>
<?define("MAIN_PAGE", true);?>
<?require($_SERVER["DOCUMENT_ROOT"]."/bitrix/header.php");
$APPLICATION->SetPageProperty("keywords", "DELUXE");
$APPLICATION->SetPageProperty("description", "DELUXE");
$APPLICATION->SetTitle("DELUXE");?>
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
<?$APPLICATION->IncludeComponent(
	"dresscode:slider", 
	"promoSlider", 
	array(
		"IBLOCK_TYPE" => "#SLIDER_IBLOCK_TYPE#",
		"IBLOCK_ID" => "#SLIDER_IBLOCK_ID#",
		"CACHE_TYPE" => "Y",
		"CACHE_TIME" => "3600000",
		"PICTURE_WIDTH" => "1920",
		"PICTURE_HEIGHT" => "1080",
		"COMPONENT_TEMPLATE" => ".default"
	),
	false
);?>

<?$APPLICATION->IncludeComponent("bitrix:news.list", "indexBanners", array(
	"ACTIVE_DATE_FORMAT" => "d.m.Y",
		"ADD_SECTIONS_CHAIN" => "N",
		"AJAX_MODE" => "N",
		"AJAX_OPTION_ADDITIONAL" => "",
		"AJAX_OPTION_HISTORY" => "N",
		"AJAX_OPTION_JUMP" => "N",
		"AJAX_OPTION_STYLE" => "Y",
		"CACHE_FILTER" => "N",
		"CACHE_GROUPS" => "Y",
		"CACHE_TIME" => "36000000",
		"CACHE_TYPE" => "A",
		"CHECK_DATES" => "Y",
		"DETAIL_URL" => "",
		"DISPLAY_BOTTOM_PAGER" => "N",
		"DISPLAY_DATE" => "Y",
		"DISPLAY_NAME" => "Y",
		"DISPLAY_PICTURE" => "Y",
		"DISPLAY_PREVIEW_TEXT" => "Y",
		"DISPLAY_TOP_PAGER" => "N",
		"FIELD_CODE" => array(
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
		"FILTER_NAME" => "",
		"HIDE_LINK_WHEN_NO_DETAIL" => "N",
		"IBLOCK_ID" => "#BANNERS_IBLOCK_ID#",
		"IBLOCK_TYPE" => "#BANNERS_IBLOCK_TYPE#",
		"INCLUDE_IBLOCK_INTO_CHAIN" => "N",
		"INCLUDE_SUBSECTIONS" => "Y",
		"MESSAGE_404" => "",
		"NEWS_COUNT" => "6",
		"PAGER_BASE_LINK_ENABLE" => "N",
		"PAGER_DESC_NUMBERING" => "N",
		"PAGER_DESC_NUMBERING_CACHE_TIME" => "36000",
		"PAGER_SHOW_ALL" => "N",
		"PAGER_SHOW_ALWAYS" => "N",
		"PAGER_TEMPLATE" => ".default",
		"PAGER_TITLE" => "Банеры",
		"PARENT_SECTION" => "",
		"PARENT_SECTION_CODE" => "",
		"PREVIEW_TRUNCATE_LEN" => "",
		"PROPERTY_CODE" => array(
			0 => "",
			1 => "",
		),
		"SET_BROWSER_TITLE" => "N",
		"SET_LAST_MODIFIED" => "N",
		"SET_META_DESCRIPTION" => "N",
		"SET_META_KEYWORDS" => "N",
		"SET_STATUS_404" => "N",
		"SET_TITLE" => "N",
		"SHOW_404" => "N",
		"SORT_BY1" => "SORT",
		"SORT_BY2" => "SORT",
		"SORT_ORDER1" => "ASC",
		"SORT_ORDER2" => "ASC",
		"STRICT_SECTION_CHECK" => "N",
		"COMPONENT_TEMPLATE" => "indexBanners"
	),
	false,
	array(
	"ACTIVE_COMPONENT" => "Y"
	)
);?>
	
<?$APPLICATION->IncludeComponent(
	"dresscode:offers.product", 
	".default", 
	array(
		"CACHE_TYPE" => "Y",
		"CACHE_TIME" => "3600000",
		"PROP_NAME" => "OFFERS",
		"IBLOCK_TYPE" => "#CATALOG_IBLOCK_TYPE#",
		"IBLOCK_ID" => $catalogIblockId,
		"PRODUCT_PRICE_CODE" => $arPriceCodes,
		"PICTURE_WIDTH" => "220",
		"PICTURE_HEIGHT" => "200",
		"PROP_VALUE" => #CATALOG_PROP_VALUES#,
		"ELEMENTS_COUNT" => "10",
		"SORT_PROPERTY_NAME" => "SORT",
		"SORT_VALUE" => "ASC",
		"COMPONENT_TEMPLATE" => ".default"
	),
	false,
	array(
		"ACTIVE_COMPONENT" => "Y"
	)
);?>	
<?$APPLICATION->IncludeComponent(
	"dresscode:pop.section", 
	".default", 
	array(
		"CACHE_TYPE" => "Y",
		"CACHE_TIME" => "3600000",
		"PROP_NAME" => "UF_POPULAR",
		"IBLOCK_TYPE" => "#CATALOG_IBLOCK_TYPE#",
		"IBLOCK_ID" => $catalogIblockId,
		"PICTURE_WIDTH" => "120",
		"PICTURE_HEIGHT" => "100",
		"PROP_VALUE" => "Y",
		"ELEMENTS_COUNT" => "10",
		"SORT_PROPERTY_NAME" => "7",
		"SORT_VALUE" => "DESC",
		"SELECT_FIELDS" => array(
			0 => "NAME",
			1 => "SECTION_PAGE_URL",
			2 => "DETAIL_PICTURE",
			3 => "UF_IMAGES",
			4 => "UF_MARKER",
			5 => "",
		),
		"POP_LAST_ELEMENT" => "Y",
		"COMPONENT_TEMPLATE" => ".default"
	),
	false,
	array(
		"ACTIVE_COMPONENT" => "Y"
	)
);?>

<?$APPLICATION->IncludeComponent(
	"dresscode:brands.list", 
	".default", 
	array(
		"IBLOCK_TYPE" => "#BRANDS_IBLOCK_TYPE#",
		"IBLOCK_ID" => "#BRANDS_IBLOCK_ID#",
		"SELECT_FIELDS" => array(
			0 => "",
			1 => "*",
			2 => "",
		),
		"PROP_NAME" => "",
		"PROP_VALUE" => "",
		"ELEMENTS_COUNT" => "15",
		"SORT_PROPERTY_NAME" => "7",
		"SORT_VALUE" => "ASC",
		"PICTURE_WIDTH" => "150",
		"PICTURE_HEIGHT" => "120",
		"CACHE_TYPE" => "A",
		"CACHE_TIME" => "360000",
	),
	false,
	array(
		"ACTIVE_COMPONENT" => "Y"
	)
);?>

<div id="infoTabsCaption">
	<div class="limiter">
		<div class="items">
			<?$APPLICATION->ShowViewContent("main_news_view_content_tab");?>
			<?$APPLICATION->ShowViewContent("main_collection_view_content_tab");?>
			<?$APPLICATION->ShowViewContent("main_service_view_content_tab");?>
		</div>
	</div>
</div>
<div id="infoTabs">
	<div class="items">
		<?$APPLICATION->IncludeComponent(
			"bitrix:news.list", 
			"mainNews", 
			array(
				"IBLOCK_TYPE" => "#NEWS_IBLOCK_TYPE#",
				"IBLOCK_ID" => "#NEWS_IBLOCK_ID#",
				"NEWS_COUNT" => "6",
				"SORT_BY1" => "ACTIVE_FROM",
				"SORT_ORDER1" => "DESC",
				"SORT_BY2" => "SORT",
				"SORT_ORDER2" => "ASC",
				"FILTER_NAME" => "",
				"FIELD_CODE" => array(
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
				"PROPERTY_CODE" => array(
					0 => "",
					1 => "",
				),
				"CHECK_DATES" => "Y",
				"DETAIL_URL" => "",
				"AJAX_MODE" => "N",
				"AJAX_OPTION_JUMP" => "N",
				"AJAX_OPTION_STYLE" => "Y",
				"AJAX_OPTION_HISTORY" => "N",
				"CACHE_TYPE" => "Y",
				"CACHE_TIME" => "36000000",
				"CACHE_FILTER" => "N",
				"CACHE_GROUPS" => "Y",
				"PREVIEW_TRUNCATE_LEN" => "",
				"ACTIVE_DATE_FORMAT" => "d.m.Y",
				"SET_TITLE" => "N",
				"SET_BROWSER_TITLE" => "N",
				"SET_META_KEYWORDS" => "N",
				"SET_META_DESCRIPTION" => "N",
				"SET_STATUS_404" => "N",
				"INCLUDE_IBLOCK_INTO_CHAIN" => "N",
				"ADD_SECTIONS_CHAIN" => "N",
				"HIDE_LINK_WHEN_NO_DETAIL" => "N",
				"PARENT_SECTION" => "",
				"PARENT_SECTION_CODE" => "",
				"INCLUDE_SUBSECTIONS" => "Y",
				"DISPLAY_DATE" => "Y",
				"DISPLAY_NAME" => "Y",
				"DISPLAY_PICTURE" => "Y",
				"DISPLAY_PREVIEW_TEXT" => "Y",
				"PAGER_TEMPLATE" => ".default",
				"DISPLAY_TOP_PAGER" => "N",
				"DISPLAY_BOTTOM_PAGER" => "Y",
				"PAGER_TITLE" => "Новости",
				"PAGER_SHOW_ALWAYS" => "N",
				"PAGER_DESC_NUMBERING" => "N",
				"PAGER_DESC_NUMBERING_CACHE_TIME" => "36000",
				"PAGER_SHOW_ALL" => "N",
				"AJAX_OPTION_ADDITIONAL" => "",
				"SET_LAST_MODIFIED" => "N",
				"PAGER_BASE_LINK_ENABLE" => "N",
				"SHOW_404" => "N",
				"MESSAGE_404" => ""
			),
			false
		);?>

		<?$APPLICATION->IncludeComponent(
			"bitrix:news.list", 
			"mainCollection", 
			array(
				"IBLOCK_TYPE" => "#COLLECTION_IBLOCK_TYPE#",
				"IBLOCK_ID" => "#COLLECTION_IBLOCK_ID#",
				"NEWS_COUNT" => "6",
				"SORT_BY1" => "ACTIVE_FROM",
				"SORT_ORDER1" => "DESC",
				"SORT_BY2" => "SORT",
				"SORT_ORDER2" => "ASC",
				"FILTER_NAME" => "",
				"FIELD_CODE" => array(
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
				"PROPERTY_CODE" => array(
					0 => "",
					1 => "",
				),
				"CHECK_DATES" => "Y",
				"DETAIL_URL" => "",
				"AJAX_MODE" => "N",
				"AJAX_OPTION_JUMP" => "N",
				"AJAX_OPTION_STYLE" => "Y",
				"AJAX_OPTION_HISTORY" => "N",
				"CACHE_TYPE" => "Y",
				"CACHE_TIME" => "36000000",
				"CACHE_FILTER" => "N",
				"CACHE_GROUPS" => "Y",
				"PREVIEW_TRUNCATE_LEN" => "",
				"ACTIVE_DATE_FORMAT" => "d.m.Y",
				"SET_TITLE" => "N",
				"SET_BROWSER_TITLE" => "N",
				"SET_META_KEYWORDS" => "N",
				"SET_META_DESCRIPTION" => "N",
				"SET_STATUS_404" => "N",
				"INCLUDE_IBLOCK_INTO_CHAIN" => "N",
				"ADD_SECTIONS_CHAIN" => "N",
				"HIDE_LINK_WHEN_NO_DETAIL" => "N",
				"PARENT_SECTION" => "",
				"PARENT_SECTION_CODE" => "",
				"INCLUDE_SUBSECTIONS" => "Y",
				"DISPLAY_DATE" => "Y",
				"DISPLAY_NAME" => "Y",
				"DISPLAY_PICTURE" => "Y",
				"DISPLAY_PREVIEW_TEXT" => "Y",
				"PAGER_TEMPLATE" => ".default",
				"DISPLAY_TOP_PAGER" => "N",
				"DISPLAY_BOTTOM_PAGER" => "Y",
				"PAGER_TITLE" => "Коллекции",
				"PAGER_SHOW_ALWAYS" => "N",
				"PAGER_DESC_NUMBERING" => "N",
				"PAGER_DESC_NUMBERING_CACHE_TIME" => "36000",
				"PAGER_SHOW_ALL" => "N",
				"AJAX_OPTION_ADDITIONAL" => "",
				"SET_LAST_MODIFIED" => "N",
				"PAGER_BASE_LINK_ENABLE" => "N",
				"SHOW_404" => "N",
				"MESSAGE_404" => ""
			),
			false
		);?>

		<?$APPLICATION->IncludeComponent(
			"bitrix:news.list", 
			"mainService", 
			array(
				"IBLOCK_TYPE" => "#SERVICE_IBLOCK_TYPE#",
				"IBLOCK_ID" => "#SERVICE_IBLOCK_ID#",
				"NEWS_COUNT" => "6",
				"SORT_BY1" => "ACTIVE_FROM",
				"SORT_ORDER1" => "DESC",
				"SORT_BY2" => "SORT",
				"SORT_ORDER2" => "ASC",
				"FILTER_NAME" => "",
				"FIELD_CODE" => array(
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
				"PROPERTY_CODE" => array(
					0 => "",
					1 => "",
				),
				"CHECK_DATES" => "Y",
				"DETAIL_URL" => "",
				"AJAX_MODE" => "N",
				"AJAX_OPTION_JUMP" => "N",
				"AJAX_OPTION_STYLE" => "Y",
				"AJAX_OPTION_HISTORY" => "N",
				"CACHE_TYPE" => "Y",
				"CACHE_TIME" => "36000000",
				"CACHE_FILTER" => "N",
				"CACHE_GROUPS" => "Y",
				"PREVIEW_TRUNCATE_LEN" => "",
				"ACTIVE_DATE_FORMAT" => "d.m.Y",
				"SET_TITLE" => "N",
				"SET_BROWSER_TITLE" => "N",
				"SET_META_KEYWORDS" => "N",
				"SET_META_DESCRIPTION" => "N",
				"SET_STATUS_404" => "N",
				"INCLUDE_IBLOCK_INTO_CHAIN" => "N",
				"ADD_SECTIONS_CHAIN" => "N",
				"HIDE_LINK_WHEN_NO_DETAIL" => "N",
				"PARENT_SECTION" => "",
				"PARENT_SECTION_CODE" => "",
				"INCLUDE_SUBSECTIONS" => "Y",
				"DISPLAY_DATE" => "Y",
				"DISPLAY_NAME" => "Y",
				"DISPLAY_PICTURE" => "Y",
				"DISPLAY_PREVIEW_TEXT" => "Y",
				"PAGER_TEMPLATE" => ".default",
				"DISPLAY_TOP_PAGER" => "N",
				"DISPLAY_BOTTOM_PAGER" => "Y",
				"PAGER_TITLE" => "Новости",
				"PAGER_SHOW_ALWAYS" => "N",
				"PAGER_DESC_NUMBERING" => "N",
				"PAGER_DESC_NUMBERING_CACHE_TIME" => "36000",
				"PAGER_SHOW_ALL" => "N",
				"AJAX_OPTION_ADDITIONAL" => "",
				"SET_LAST_MODIFIED" => "N",
				"PAGER_BASE_LINK_ENABLE" => "N",
				"SHOW_404" => "N",
				"MESSAGE_404" => ""
			),
			false
		);?>

	</div>
</div>

<?$APPLICATION->IncludeComponent(
	"dresscode:slider", 
	"middle", 
	array(
		"IBLOCK_TYPE" => "#MIDDLE_SLIDER_IBLOCK_TYPE#",
		"IBLOCK_ID" => "#MIDDLE_SLIDER_IBLOCK_ID#",
		"CACHE_TYPE" => "Y",
		"CACHE_TIME" => "3600000",
		"PICTURE_WIDTH" => "1480",
		"PICTURE_HEIGHT" => "200",
		"COMPONENT_TEMPLATE" => "middle"
	),
	false
);?>

<?$APPLICATION->IncludeComponent(
	"bitrix:main.include", 
	".default", 
	array(
		"COMPONENT_TEMPLATE" => ".default",
		"AREA_FILE_SHOW" => "sect",
		"AREA_FILE_SUFFIX" => "simplyText",
		"AREA_FILE_RECURSIVE" => "Y",
		"EDIT_TEMPLATE" => ""
	),
	false
);?>
<?require($_SERVER["DOCUMENT_ROOT"]."/bitrix/footer.php");?>