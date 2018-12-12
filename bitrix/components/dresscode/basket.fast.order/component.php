<?
	if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true)
		die();

	//check modules
	if (CModule::IncludeModule("sale") &&
		CModule::IncludeModule("catalog") &&
		CModule::IncludeModule("iblock")
	){
		//show template
		$this->IncludeComponentTemplate();	
	}

?>