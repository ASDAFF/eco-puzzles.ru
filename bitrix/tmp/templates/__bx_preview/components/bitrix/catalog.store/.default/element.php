<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();?>
<?$APPLICATION->IncludeComponent(
	"dresscode:catalog.store.detail",
	"",
	Array(
		"CACHE_TIME" => $arParams["CACHE_TIME"],
		"STORE" => $arResult["STORE"],
		"TITLE" => $arParams["TITLE"],
		"PATH_TO_ELEMENT" => $arResult["PATH_TO_ELEMENT"],
		"PATH_TO_LISTSTORES" => $arResult["PATH_TO_LISTSTORES"],
		"SET_TITLE" => $arParams["SET_TITLE"],
		"MAP_TYPE" => $arParams["MAP_TYPE"],
	),
	$component
);?>
<?$APPLICATION->IncludeComponent(
	"dresscode:catalog.store.list",
	".detail",
	Array(
		"CACHE_TIME" => $arParams["CACHE_TIME"],
		"PHONE" => $arParams["PHONE"],
		"EMAIL" => $arParams["EMAIL"],
		"SCHEDULE" => $arParams["SCHEDULE"],
		"MIN_AMOUNT" => $arParams["MIN_AMOUNT"],
		"TITLE" => $arParams["TITLE"],
		"SET_TITLE" => $arParams["SET_TITLE"],
		"PATH_TO_ELEMENT" => $arResult["PATH_TO_ELEMENT"],
		"PATH_TO_LISTSTORES" => $arResult["PATH_TO_LISTSTORES"],
		"MAP_TYPE" => $arParams["MAP_TYPE"],
	),
	$component
);?>