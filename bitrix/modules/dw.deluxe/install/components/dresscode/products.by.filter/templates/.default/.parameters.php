<?if(!\Bitrix\Main\Loader::includeModule("iblock"))
	return;

	$arAdapriveValues = array(
		"V1" => GetMessage("APAPTIVE_VERSION_V1"),
		"V2" => GetMessage("APAPTIVE_VERSION_V2")
	);

	$arTemplateParameters["ADAPTIVE_VERSION"] = array(
		"PARENT" => "BASE",
		"NAME" => GetMessage("APAPTIVE_VERSION"),
		"TYPE" => "LIST",
		"VALUES" => $arAdapriveValues,
		"REFRESH" => "Y",
	);

?>