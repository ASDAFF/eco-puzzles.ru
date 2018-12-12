<?
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true) die();
if(CModule::IncludeModule("iblock") && CModule::IncludeModule("sale") && CModule::IncludeModule("catalog")){

	$IBLOCKS     = array();
	$IBLOCK_TYPE = array();
	$PROPERTIES  = array();
	$PROP_VALUES = array();

	$SORT_VALUES = array(
		"id",
		"section",
		"name",
		"code",
		"active",
		"left_margin",
		"depth_level",
		"sort",
		"created",
		"created_by",
		"modified_by",
		"element_cnt",
		"timestamp_x"
	);

	$SELECT_FIELDS = array(
		"ID" => GetMessage("FIELD_ID"),
		"CODE" => GetMessage("FIELD_CODE"),
		"XML_ID" => GetMessage("FIELD_XML_ID"),
		"EXTERNAL_ID" => GetMessage("FIELD_EXTERNAL_ID"),
		"IBLOCK_ID" => GetMessage("FIELD_IBLOCK_ID"),
		"IBLOCK_SECTION_ID" => GetMessage("FIELD_IBLOCK_SECTION_ID"),
		"TIMESTAMP_X" => GetMessage("FIELD_TIMESTAMP_X"),
		"SORT" => GetMessage("FIELD_SORT"), 
		"NAME" => GetMessage("FIELD_NAME"),
		"ACTIVE" => GetMessage("FIELD_ACTIVE"),
		"GLOBAL_ACTIVE" => GetMessage("FIELD_GLOBAL_ACTIVE"),
		"PICTURE" => GetMessage("FIELD_PICTURE"),
		"DESCRIPTION" => GetMessage("FIELD_DESCRIPTION"),
		"DESCRIPTION_TYPE" => GetMessage("FIELD_DESCRIPTION_TYPE"),
		"LEFT_MARGIN" => GetMessage("FIELD_LEFT_MARGIN"),
		"RIGHT_MARGIN" => GetMessage("FIELD_RIGHT_MARGIN"),
		"DEPTH_LEVEL" => GetMessage("FIELD_DEPTH_LEVEL"),
		"SEARCHABLE_CONTENT" => GetMessage("FIELD_SEARCHABLE_CONTENT"),
		"SECTION_PAGE_URL" => GetMessage("FIELD_SECTION_PAGE_URL"),
		"MODIFIED_BY" => GetMessage("FIELD_MODIFIED_BY"),
		"DATE_CREATE" => GetMessage("FIELD_DATE_CREATE"),
		"CREATED_BY" => GetMessage("FIELD_CREATED_BY"),
		"DETAIL_PICTURE" => GetMessage("FIELD_DETAIL_PICTURE")
	);

	$res = CIBlockType::GetList();
	while($arRes = $res->Fetch()){
		$IBLOCK_TYPE[$arRes["ID"]] = $arRes["ID"];
	}

	$res = CIBlock::GetList(
	    Array(),
	    Array('TYPE' => $arCurrentValues["IBLOCK_TYPE"])
	);

	while($arRes = $res->Fetch()){
		$IBLOCKS[$arRes["ID"]] = $arRes["NAME"];
	}

	$arComponentParameters = array(
		"GROUPS" => array(
			"PICTURE" => array(
				"NAME" => GetMessage("PICTURE"),
				"SORT" => "200"
			),
			"FILTER" => array(
				"NAME" => GetMessage("FILTER"),
				"SORT" => "180"
			),
			"SELECT" => array(
				"NAME" => GetMessage("SELECT"),
				"SORT" => "180"
			),
			"SORT" => array(
				"NAME" => GetMessage("SORT"),
				"SORT" => "190"
			),			
		),
		"PARAMETERS" => array(
			"IBLOCK_TYPE" => array(
		         "PARENT" => "BASE",
		         "NAME" => GetMessage("IBLOCK_TYPE"),
		         "TYPE" => "LIST",
		          "VALUES" => $IBLOCK_TYPE,
		          "REFRESH" => "Y"
			),
			"IBLOCK_ID" => array(
		         "PARENT" => "BASE",
		         "NAME" => GetMessage("IBLOCK"),
		         "TYPE" => "LIST",
		          "VALUES" => $IBLOCKS,
		          "REFRESH" => "Y"
			),
			"PROP_NAME" => array(
			     "PARENT" => "FILTER",
			     "NAME" => GetMessage("OFFERS_PROP"),
		         "TYPE" => "STRING"
			),
			"PROP_VALUE" => array(
			     "PARENT" => "FILTER",
			     "NAME" => GetMessage("OFFERS_PROP_VALUE"),
		         "TYPE" => "STRING"
			),
			"ELEMENTS_COUNT" => array(
			     "PARENT" => "FILTER",
			     "NAME" => GetMessage("ELEMENTS_COUNT"),
		         "TYPE" => "STRING",
		         "DEFAULT" => "20"
			),
			"POP_LAST_ELEMENT" => array(
			     "PARENT" => "FILTER",
			     "NAME" => GetMessage("POP_LAST_ELEMENT"),
		         "TYPE" => "LIST",
		         "DEFAULT" => "Y",
		         "VALUES" => array(
		         	"Y" => GetMessage("YES"),
		         	"N" => GetMessage("NO")
		         )
			),
			"SORT_PROPERTY_NAME" => array(
				"PARENT" => "SORT",
				"NAME" => GetMessage("SORT_PROPERTY_NAME"),
				"TYPE" => "LIST",
				"VALUES" => $SORT_VALUES,
				"DEFAULT" => "sort",
				"ADDITIONAL_VALUES" => "Y"
			),
			"SORT_VALUE" => array(
			     "PARENT" => "SORT",
			     "NAME" => GetMessage("SORT_VALUE"),
		         "TYPE" => "LIST",
		         "VALUES" => array(
		         	"ASC" => GetMessage("ASC"),
		         	"DESC" => GetMessage("DESC")
		         ),
		         "DEFAULT" => "DESC"
			),
			"SELECT_FIELDS" => array(
			     "PARENT" => "SELECT",
			     "NAME" => GetMessage("SELECT_FIELDS"),
		         "TYPE" => "LIST",
		         "VALUES" => $SELECT_FIELDS,
		         "DEFAULT" => "*",
		         "ADDITIONAL_VALUES" => "Y",
		         "MULTIPLE" => "Y"
			),
			"PICTURE_WIDTH" => array(
		         "PARENT" => "PICTURE",
		         "NAME" => GetMessage("PICTURE_WIDTH"),
		         "TYPE" => "STRING",
		         "DEFAULT" => "200"
			),
			"PICTURE_HEIGHT" => array(
		         "PARENT" => "PICTURE",
		         "NAME" => GetMessage("PICTURE_HEIGHT"),
		         "TYPE" => "STRING",
		         "DEFAULT" => "140"
			),
			"CACHE_TIME" => Array("DEFAULT" => "360000"),
		)
	);

}
?>