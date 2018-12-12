<?
if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();

$arServices = Array(
	"main" => Array(
		"NAME" => GetMessage("SERVICE_MAIN_SETTINGS"),
		"STAGES" => Array(
			"files.php", // Copy bitrix files
			"search.php", // Indexing files
			"template.php", // Install template
			"theme.php", // Install theme
			"menu.php", // Install menu
			"settings.php",
		),
	),
	"iblock" => Array(
		"NAME" => GetMessage("SERVICE_IBLOCK_DEMO_DATA"),
		"STAGES" => Array(
			"types.php", //IBlock types
			"brands.php",
			"banners.php",
			"blog.php",
			"faq.php",
			"landing.php",
			"sales.php",
			"slider.php",
			"slider_bottom.php",
			"slider_content.php",
			"collection.php",
			"services.php",
			"reviews.php",
			"reviewsMagazine.php",
			"news.php",
			"catalog.php",
			"catalog2.php",
			"catalog3.php",
		),
	),
	"sale" => Array(
		"NAME" => GetMessage("SERVICE_SALE_DEMO_DATA"),
		"STAGES" => Array(
			"locations.php",
			"step1.php",
			"step2.php",
			"step3.php"
		),
	),
	"catalog" => Array(
		"NAME" => GetMessage("SERVICE_CATALOG_SETTINGS"),
		"STAGES" => Array(
			"index.php"
		),
	),
);
?>