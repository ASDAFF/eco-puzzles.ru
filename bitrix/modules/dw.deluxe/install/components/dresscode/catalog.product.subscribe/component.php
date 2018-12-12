<?
	if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true)
		die();

	//check modules
	if (CModule::IncludeModule("sale") &&
		CModule::IncludeModule("catalog") &&
		CModule::IncludeModule("iblock")
	){

		//globals
		global $USER;

		//get user id
		if($USER && is_object($USER) && $USER->isAuthorized()){
			$arResult["USER_ID"] = $USER->getId();
			$arResult["USER_EMAIL"] = $USER->getEmail();
		}

		//show template
		$this->IncludeComponentTemplate();
		
	}

?>