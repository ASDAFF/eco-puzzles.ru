<?require($_SERVER["DOCUMENT_ROOT"]."/bitrix/header.php");
$APPLICATION->SetTitle("Наши магазины");?><h1><?$APPLICATION->ShowTitle(true)?></h1><?$APPLICATION->IncludeComponent(
	"bitrix:catalog.store", 
	".default", 
	array(
		"CACHE_TIME" => "360000",
		"CACHE_TYPE" => "A",
		"COMPONENT_TEMPLATE" => ".default",
		"MAP_TYPE" => "0",
		"PHONE" => "Y",
		"SCHEDULE" => "Y",
		"EMAIL" => "Y",
		"SEF_FOLDER" => "/stores/",
		"SEF_MODE" => "Y",
		"SET_TITLE" => "N",
		"TITLE" => "Список складов с подробной информацией",
		"SEF_URL_TEMPLATES" => array(
			"liststores" => "",
			"element" => "#store_id#/",
		)
	),
	false
);?><?require($_SERVER["DOCUMENT_ROOT"]."/bitrix/footer.php");?>