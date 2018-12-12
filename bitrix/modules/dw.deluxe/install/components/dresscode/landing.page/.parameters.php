<?
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true) die();
if(CModule::IncludeModule("iblock") && CModule::IncludeModule("sale") && CModule::IncludeModule("catalog")){

	//get data
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

	//params
	$arComponentParameters = array(
		"PARAMETERS" => array(
			"CACHE_TIME" => Array("DEFAULT" => "1285912"),
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
		)
	);
}
?>