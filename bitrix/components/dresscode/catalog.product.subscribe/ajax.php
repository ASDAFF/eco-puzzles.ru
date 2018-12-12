<?require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_before.php");?>
<?
	if(!empty($_POST["act"])){

		//include modules
		Bitrix\Main\Loader::includeModule("sale");
		Bitrix\Main\Loader::includeModule("iblock");
		Bitrix\Main\Loader::includeModule("catalog");
		Bitrix\Main\Loader::includeModule("currency");

		if($_POST["act"] == "sendSubscribeForm"){

			//globals
			global $USER;

			//vars
			$userId = false;
			$arSysErrors = array();

			//get user id
			if($USER && is_object($USER) && $USER->isAuthorized()){
				$userId = $USER->getId();
			}


			//check fields
			if(empty($_POST["subscribe-form-email"])){
				//save error
				$arSysErrors[] = array(
					"EMAIL" => false
				);
			}

			if(empty($_POST["subscribe-form-product-id"])){
				//save error
				$arSysErrors[] = array(
					"PRODUCT_ID" => false
				);					
			}

			if(empty($_POST["site_id"])){
				//save error
				$arSysErrors[] = array(
					"SITE_ID" => false
				);					
			}


			if(empty($arSysErrors)){

				//convert strings
				foreach ($_POST as $i => $nextPost){
					$_POST[$i] = BX_UTF != 1 ? iconv("UTF-8","windows-1251//IGNORE", htmlspecialcharsbx($_POST[$i])) : htmlspecialcharsbx($_POST[$i]);
				}

				$subscribeManager = new \Bitrix\Catalog\Product\SubscribeManager;
				$contactTypes = $subscribeManager->contactTypes;
				$contactTypeId = key($contactTypes);

				$subscribeData = array(
					"USER_CONTACT" => $_POST["subscribe-form-email"],
					"ITEM_ID" => intval($_POST["subscribe-form-product-id"]),
					"SITE_ID" => $_POST["site_id"],
					"CONTACT_TYPE" => $contactTypeId,
					"USER_ID" => $userId,
				);
				
				//add subscribe
				$subscribeId = $subscribeManager->addSubscribe($subscribeData);
				if($subscribeId){
					echo \Bitrix\Main\Web\Json::encode(array("SUCCESS" => "Y", "SUBSCRIBE_ID" => $subscribeId, "ITEM_ID" => intval($_POST["subscribe-form-product-id"])));
					$_SESSION["SUBSCRIBE"]["EMAIL"] = $_POST["subscribe-form-email"];
				}

				else{

					$errorObject = current($subscribeManager->getErrors());
					if($errorObject){
						$arSysErrors[] = array(
							"SUBSCRIBE" => $errorObject->getMessage()
						);
						
					}

				}


			}

			//return errors
			if(!empty($arSysErrors)){
				//return error
				echo \Bitrix\Main\Web\Json::encode(
					array_merge($arSysErrors, array("ERROR" => "Y"))
				);
			}

		}

	}
?>
