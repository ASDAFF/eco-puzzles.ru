<?if(!\Bitrix\Main\Loader::includeModule("iblock"))
	return;

$arTemplateParameters["MODAL_BUTTON_NAME"] = array(
	"PARENT" => "BASE",
	"NAME" => GetMessage("MODAL_BUTTON_NAME"),
	"TYPE" => "STRING"
);

$arTemplateParameters["MODAL_BUTTON_CLASS_NAME"] = array(
	"PARENT" => "BASE",
	"NAME" => GetMessage("MODAL_BUTTON_CLASS_NAME"),
	"TYPE" => "STRING"
);
?>