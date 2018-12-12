<?require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_before.php");?>
<?
	if(!empty($_GET["act"])){

		if($_GET["act"] == "getSubscribeItems"){

			//include modules
			Bitrix\Main\Loader::includeModule("sale");
			Bitrix\Main\Loader::includeModule("iblock");
			Bitrix\Main\Loader::includeModule("catalog");
			Bitrix\Main\Loader::includeModule("currency");

			//globals
			global $USER;

			//vars
			$userId = false;
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

			if(!empty($arResult["ITEMS"])){
				echo \Bitrix\Main\Web\Json::encode($arResult["ITEMS"]);
			}

			else{
				echo \Bitrix\Main\Web\Json::encode(array("EMPTY"=> "Y"));
			}

		}
		
	}
?>
