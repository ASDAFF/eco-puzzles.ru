<?$APPLICATION->IncludeComponent(
	"dresscode:landing.page", 
	".default", 
	array(
		"CACHE_TYPE" => "Y",
		"CACHE_TIME" => "3600000",
		"IBLOCK_TYPE" => "#LANDING_IBLOCK_TYPE#",
		"IBLOCK_ID" => "#LANDING_IBLOCK_ID#",
		"COMPONENT_TEMPLATE" => ".default"
	),
	false,
	array(
		"ACTIVE_COMPONENT" => "Y"
	)
);?>