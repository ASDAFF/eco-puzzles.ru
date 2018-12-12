<?
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true) die();

$arComponentDescription = array(
	"NAME" => GetMessage("BOXBERRY_COMP_NAME"),
	"DESCRIPTION" => GetMessage("BOXBERRY_COMP_DESCR"),
	"CACHE_PATH" => "Y",
	"SORT" => 40,
	"PATH" => array(
		"ID" => "up",
		"CHILD" => array(
			"ID" => "bberry",
			"NAME" => GetMessage("BOXBERRY_GROUP"),
			"SORT" => 30,
			"CHILD" => array(
				"ID" => "up.boxberrydelivery",
			),
		),
	),
);
?>