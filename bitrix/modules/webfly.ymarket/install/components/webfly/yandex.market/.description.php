<?
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true) die();

$arComponentDescription = array(
	"NAME" => GetMessage("WF_TEMPLATE_NAME"),
	"DESCRIPTION" => GetMessage("WF_TEMPLATE_DESCRIPTION"),
	"ICON" => "/images/comp.gif",
	"CACHE_PATH" => "Y",
	"SORT" => 30,
	"PATH" => array(
		"ID" => "webfly",
		"NAME" => GetMessage("WF_COMPONENTS"),
		"CHILD" => array(
			"ID" => "wf_catalog",
			"NAME" => GetMessage("WF_DESC_CATALOG"),
			"SORT" => 30
		)
	)
);

?>