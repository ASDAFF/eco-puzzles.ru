<?
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true) die();
if(CModule::IncludeModule("iblock") && CModule::IncludeModule("sale") && CModule::IncludeModule("catalog")){
	$arComponentParameters = array(
		"PARAMETERS" => array(
			"CACHE_TIME" => Array("DEFAULT" => "1285912"),
			"SITE_ID" => Array(
				"DEFAULT" => "s1",
				"NAME" => GetMessage("SITE_ID"),
				"TYPE" => "STRING",
			)
		)
	);
}
?>