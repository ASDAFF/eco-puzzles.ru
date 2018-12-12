<?
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true) die();
if(CModule::IncludeModule("iblock") && CModule::IncludeModule("sale") && CModule::IncludeModule("catalog")){

	$IBLOCKS     = array();
	$IBLOCK_TYPE = array();
	$PROPERTIES  = array();
	$PROP_VALUES = array();

	$SORT_VALUES = array(
		"id" => "id",
		"section" => "section",
		"name" => "name",
		"code" => "code",
		"active" => "active",
		"left_margin" => "left_margin",
		"depth_level" => "depth_level",
		"sort" => "sort",
		"created" => "created",
		"created_by" => "created_by",
		"modified_by" => "modified_by",
		"element_cnt" => "element_cnt",
		"timestamp_x" => "timestamp_x"
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
			"ELEMENTS_COUNT" => array(
			     "PARENT" => "FILTER",
			     "NAME" => GetMessage("ELEMENTS_COUNT"),
		         "TYPE" => "STRING",
		         "DEFAULT" => "20"
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
			"CACHE_TIME" => Array("DEFAULT" => "3600000"),
		)
	);

}
?>