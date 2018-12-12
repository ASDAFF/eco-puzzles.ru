<?
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true) die();
if(CModule::IncludeModule("iblock") && CModule::IncludeModule("sale") && CModule::IncludeModule("catalog")){
	$arComponentParameters = array(
		"PARAMETERS" => array(
			"GEO_IP_PARAMS" => array(
		         "PARENT" => "BASE",
		         "NAME" => GetMessage("GEO_IP_PARAMS"),
		         "TYPE" => "LIST",
		          "VALUES" => array(
		          	"SUPEXGEO" => GetMessage("GEO_IP_PARAMS_SYPEXGEO"),
		          	"YANDEX" => GetMessage("GEO_IP_PARAMS_YANDEX")
		          ),
		          "REFRESH" => "Y",
		          "DEFAULT" => "SUPEXGEO",
			),
			"CACHE_TIME" => Array("DEFAULT" => "1285912"),
		)
	);
}
?>