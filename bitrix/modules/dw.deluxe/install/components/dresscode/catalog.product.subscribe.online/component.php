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

		//vars
		$userId = false;
		$userEmail = false;
		$arResult["ITEMS"] = array();

		if(!empty($_SESSION["SUBSCRIBE"]["EMAIL"])){
			$userEmail = addslashes($_SESSION["SUBSCRIBE"]["EMAIL"]);
		}

		//get user id
		if($USER && is_object($USER) && $USER->isAuthorized()){
			$userId = $USER->getId();
			$userEmail = $USER->getEmail();
		}

		//get subscribe for current user
		$resultObject = \Bitrix\Catalog\SubscribeTable::getList(
			array(
				"select" => array(
					"ID",
					"ITEM_ID",
					"TYPE" => "PRODUCT.TYPE",
					"IBLOCK_ID" => "IBLOCK_ELEMENT.IBLOCK_ID",
				),
				"filter" => array(
					"USER_CONTACT" => $userEmail,
					"SITE_ID" => SITE_ID,
					"USER_ID" => $userId,
				),
			)
		);

		//if no exist subscribe
		while($subscribeItem = $resultObject->fetch()){
			$arResult["ITEMS"][$subscribeItem["ID"]] = $subscribeItem["ITEM_ID"];
		}

		//show template
		$this->IncludeComponentTemplate();
		
	}

?>