<?if(!\Bitrix\Main\Loader::includeModule("iblock"))
	return;

$boolCatalog = \Bitrix\Main\Loader::includeModule("catalog");
$boolForms = Bitrix\Main\Loader::includeModule("form");

$arIBlockType = CIBlockParameters::GetIBlockTypes();

//reviews
$arIBlock = array();
$rsIBlock = CIBlock::GetList(array("sort" => "asc"), array("TYPE" => $arCurrentValues["IBLOCK_TYPE"], "ACTIVE" => "Y"));
while($arr=$rsIBlock->Fetch()){
	$arIBlock[$arr["ID"]] = "[".$arr["ID"]."] ".$arr["NAME"];
}

if($arCurrentValues["USE_REVIEW"] == "Y"){
	$arIBlockReview = array();
	$rsIBlockReview = CIBlock::GetList(array("sort" => "asc"), array("TYPE" => $arCurrentValues["REVIEW_IBLOCK_TYPE"], "ACTIVE" => "Y"));
	while($arRew = $rsIBlockReview->Fetch()){
		$arIBlockReview[$arRew["ID"]] = "[".$arRew["ID"]."] ".$arRew["NAME"];
	}
}

//services
if($arCurrentValues["SHOW_SERVICES"] == "Y"){
	$arIBlockServices = array();
	$rsIBlockServices = CIBlock::GetList(array("sort" => "asc"), array("TYPE" => $arCurrentValues["SERVICES_IBLOCK_TYPE"], "ACTIVE" => "Y"));
	while($arService = $rsIBlockServices->Fetch()){
		$arIBlockServices[$arService["ID"]] = "[".$arService["ID"]."] ".$arService["NAME"];
	}
}

$arTemplateParameters["HIDE_AVAILABLE_TAB"] = array(
	"NAME" => GetMessage("HIDE_AVAILABLE_TAB"),
	"TYPE" => "CHECKBOX",
	"PARENT" => "BASE",
	"DEFAULT" =>"N",
	"VALUE" => "Y"
);

$arTemplateParameters["FILE"] = array(
  "PARENT" => "ADDITIONAL_SETTINGS",
  "NAME" => 12312312,
  "MULTIPLE" => "Y",
  "TYPE" => "FILE",
  "FD_TARGET" => "F",
  "FD_EXT" => "png,gif,jpg,jpeg",
  "FD_UPLOAD" => true,
  "FD_USE_MEDIALIB" => true,
  "FD_MEDIALIB_TYPES" => Array(
     'image',
  ),
  "DEFAULT" => ""
);

//sales
$saleIBlock = array();
$rsSaleIBlock = CIBlock::GetList(array("sort" => "asc"), array("TYPE" => $arCurrentValues["SALE_IBLOCK_TYPE"], "ACTIVE" => "Y"));
while($arNextIblock = $rsSaleIBlock->Fetch()){
	$saleIBlock[$arNextIblock["ID"]] = "[".$arNextIblock["ID"]."] ".$arNextIblock["NAME"];
}

//reviews
if($arCurrentValues["USE_REVIEW"] == "Y"){
	$arTemplateParameters["REVIEW_IBLOCK_TYPE"] = array(
		"PARENT" => "REVIEW_SETTINGS",
		"NAME" => GetMessage("IBLOCK_TYPE"),
		"TYPE" => "LIST",
		"VALUES" => $arIBlockType,
		"REFRESH" => "Y",
	);

	$arTemplateParameters["REVIEW_IBLOCK_ID"] = array(
		"PARENT" => "REVIEW_SETTINGS",
		"NAME" => GetMessage("IBLOCK_IBLOCK"),
		"TYPE" => "LIST",
		"ADDITIONAL_VALUES" => "Y",
		"VALUES" => $arIBlockReview,
		"REFRESH" => "Y",
	);
}

//services
$arTemplateParameters["SHOW_SERVICES"] = array(
	"PARENT" => "BASE",
	"NAME" => GetMessage("DISPLAY_SERVICES"),
	"DEFAULT" => "Y",
	"TYPE" => "CHECKBOX",
	"REFRESH" => "Y"
);

if($arCurrentValues["SHOW_SERVICES"] == "Y"){
	$arTemplateParameters["SERVICES_IBLOCK_TYPE"] = array(
		"PARENT" => "BASE",
		"NAME" => GetMessage("SERVICES_IBLOCK_TYPE"),
		"TYPE" => "LIST",
		"VALUES" => $arIBlockType,
		"REFRESH" => "Y",
	);

	$arTemplateParameters["SERVICES_IBLOCK_ID"] = array(
		"PARENT" => "BASE",
		"NAME" => GetMessage("SERVICES_IBLOCK_ID"),
		"TYPE" => "LIST",
		"ADDITIONAL_VALUES" => "Y",
		"VALUES" => $arIBlockServices,
		"REFRESH" => "Y",
	);
}

//sales
$arTemplateParameters["SALE_IBLOCK_TYPE"] = array(
	"PARENT" => "BASE",
	"NAME" => GetMessage("SALE_IBLOCK_TYPE"),
	"TYPE" => "LIST",
	"VALUES" => $arIBlockType,
	"REFRESH" => "Y",
);

$arTemplateParameters["SALE_IBLOCK_ID"] = array(
	"PARENT" => "BASE",
	"NAME" => GetMessage("SALE_IBLOCK_ID"),
	"TYPE" => "LIST",
	"ADDITIONAL_VALUES" => "Y",
	"VALUES" => $saleIBlock,
	"REFRESH" => "Y",
);

$arTemplateParameters["DISPLAY_OFFERS_TABLE"] = array(
	"PARENT" => "BASE",
	"NAME" => GetMessage("DISPLAY_OFFERS_TABLE"),
	"DEFAULT" => "Y",
	"TYPE" => "CHECKBOX",
	"REFRESH" => "Y"
);

if($arCurrentValues["DISPLAY_OFFERS_TABLE"] == "Y"){
	
	$arTemplateParameters["OFFERS_TABLE_PAGER_COUNT"] = array(
		"PARENT" => "BASE",
		"NAME" => GetMessage("OFFERS_TABLE_PAGER_COUNT"),
		"DEFAULT" => "10",
		"TYPE" => "STRING",
		"REFRESH" => "Y"
	);

	$arTemplateParameters["OFFERS_TABLE_DISPLAY_PICTURE_COLUMN"] = array(
		"PARENT" => "BASE",
		"NAME" => GetMessage("OFFERS_TABLE_DISPLAY_PICTURE_COLUMN"),
		"DEFAULT" => "Y",
		"TYPE" => "CHECKBOX",
		"REFRESH" => "Y"
	);

}

$arTemplateParameters["HIDE_MEASURES"] = array(
	"PARENT" => "BASE",
	"NAME" => GetMessage("HIDE_MEASURES"),
	"TYPE" => "CHECKBOX",
	"REFRESH" => "Y"
);

$arTemplateParameters["DISPLAY_CHEAPER"] = array(
	"PARENT" => "BASE",
	"NAME" => GetMessage("DISPLAY_CHEAPER"),
	"TYPE" => "CHECKBOX",
	"REFRESH" => "Y"
);

$arTemplateParameters["HIDE_DELIVERY_CALC"] = array(
	"PARENT" => "BASE",
	"NAME" => GetMessage("HIDE_DELIVERY_CALC"),
	"TYPE" => "CHECKBOX",
	"REFRESH" => "Y"
);

if($arCurrentValues["DISPLAY_CHEAPER"] == "Y"){

	$arFormsId = array();
	$rsForms = CForm::GetList($by = "s_sort", $order = "desc", array(), $is_filtered);
	while ($arForm = $rsForms->Fetch()){
	    $arFormsId[$arForm["ID"]] = $arForm["NAME"]." (id: ".$arForm["ID"].")";
	}

	$arTemplateParameters["CHEAPER_FORM_ID"] = array(
		"PARENT" => "BASE",
		"NAME" => GetMessage("CHEAPER_FORM_ID"),
		"TYPE" => "LIST",
		"VALUES" => $arFormsId,
		"REFRESH" => "Y",
	);

}

$arTemplateParameters["SHOW_SECTION_BANNER"] = array(
	"PARENT" => "BASE",
	"NAME" => GetMessage("SHOW_SECTION_BANNER"),
	"TYPE" => "CHECKBOX",
	"REFRESH" => "Y"
);


?>