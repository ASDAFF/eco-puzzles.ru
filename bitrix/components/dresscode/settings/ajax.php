<?define("STOP_STATISTICS", true);?>
<?define("NO_AGENT_CHECK", true);?>
<?require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_before.php");?>
<?
	//load modules
	if( !\Bitrix\Main\Loader::includeModule("sale") ||
		!\Bitrix\Main\Loader::includeModule("catalog") ||
		!\Bitrix\Main\Loader::includeModule("iblock") ||
		!\Bitrix\Main\Loader::includeModule("dw.deluxe") ||
		!\Bitrix\Main\Loader::includeModule("highloadblock")){

		showError("dresscode:settings - check modules");
		die();

	}

	//globals
	global $USER;

	//check admin
	if(!$USER->IsAdmin()){
		//404 error
		LocalRedirect("/404.php", "404 Not Found");
	}

	if(!empty($_REQUEST["act"])){

		if($_REQUEST["act"] == "saveSettings"){

			//vars
			$returnArray = array();

			//remove additionals
			unset($_REQUEST["act"], $_POST["act"]);

			//create object
			$dwSettings = DwSettings::getInstance();

			//save settings
			if($saveResult = $dwSettings->saveSettings($_POST, $_FILES)){
				$returnArray = array("SUCCESS" => "Y");
			}

			else{
				$returnArray = array("ERROR" => "Y", "SAVE_SETTINGS" => false);
			}

			//return json result
			echo \Bitrix\Main\Web\Json::encode($returnArray);

		}

		elseif($_REQUEST["act"] == "getPropertiesByIblock"){

			//vars
			$returnArray = array();

			//check iblock id
			if(!empty($_REQUEST["iblockId"])){

				//remove additionals
				unset($_REQUEST["act"]);

				//create object
				$dwSettings = DwSettings::getInstance();

				//get properties
				$arProperties = $dwSettings->getPropertyByIblock(
					intval($_REQUEST["iblockId"])
				);

				//check
				if(!empty($arProperties)){
					//set flag
					$returnArray = array("SUCCESS" => "Y");
					$returnArray["PROPERTIES"] = $arProperties;
				}

				else{
					//empty properties
					$returnArray = array("HIDE_BLOCK" => "Y");		
				}

			}

			else{
				//error
				$returnArray = array("ERROR" => "Y", "IBLOCK_EMPTY" => true);					
			}

			//return json result
			echo \Bitrix\Main\Web\Json::encode($returnArray);

		}

		elseif($_REQUEST["act"] == "createProductProperties"){

			//vars
			$returnArray = array();
			$iblockId = intval($_REQUEST["iblockId"]);
			$productConfigPath = __DIR__."/configs/productProperties.cfg";
			$sectionConfigPath = __DIR__."/configs/productSectionProperties.cfg";
			
			//check iblock id
			if(!empty($_REQUEST["iblockId"])){

				//create object
				$dwSettings = DwSettings::getInstance();

				//[create product properties]

				//read properties config
				$arConfigProperties = $dwSettings->readConfigFile($productConfigPath);

				//check data
				if(!empty($arConfigProperties)){

					//create property into iblock by id and config file
					$arPropResult = $dwSettings->createPropertiesByArray($arConfigProperties, $iblockId);

					//check prop result
					if(!empty($arPropResult)){
						$returnArray["PRODUCT_PROPERTIES"] = array("SUCCESS" => "Y", "PROPERTY_RESULT" => $arPropResult);
					}

				}

				//error
				else{
					$returnArray["PRODUCT_PROPERTIES"] = array("ERROR" => "Y", "READ_CONFIG_FILE" => false);					
				}

				//[create section properties]

				//read properties config
				$arConfigSectionProperties = $dwSettings->readConfigFile($sectionConfigPath);

				//check data
				if(!empty($arConfigSectionProperties)){

					//create property into iblock by id and config file
					$arSectionPropResult = $dwSettings->createUserTypePropertiesByArray($arConfigSectionProperties, $iblockId);

					//check prop result
					if(!empty($arSectionPropResult)){
						$returnArray["SECTION_PROPERTIES"] = array("SUCCESS" => "Y", "PROPERTY_RESULT" => $arSectionPropResult);
					}

				}

				//error
				else{
					$returnArray["SECTION_PROPERTIES"] = array("ERROR" => "Y", "READ_CONFIG_FILE" => false);					
				}

			}

			//error
			else{
				$returnArray = array("ERROR" => "Y", "IBLOCK_ID_EMPTY" => true);					
			}


			//return json result
			echo \Bitrix\Main\Web\Json::encode($returnArray);

		}
		elseif($_REQUEST["act"] == "createSkuProperties"){

			//vars
			$returnArray = array();
			$iblockId = intval($_REQUEST["iblockId"]);
			$skuConfigPath = __DIR__."/configs/skuProperties.cfg";
			
			//check iblock id
			if(!empty($_REQUEST["iblockId"])){

				//create object
				$dwSettings = DwSettings::getInstance();

				//[create product properties]

				//read properties config
				$arConfigProperties = $dwSettings->readConfigFile($skuConfigPath);

				//check data
				if(!empty($arConfigProperties)){

					//create property into iblock by id and config file
					$arPropResult = $dwSettings->createPropertiesByArray($arConfigProperties, $iblockId);

					//check prop result
					if(!empty($arPropResult)){
						$returnArray = array("SUCCESS" => "Y", "PROPERTY_RESULT" => $arPropResult);
					}

				}

				//error
				else{
					$returnArray = array("ERROR" => "Y", "READ_CONFIG_FILE" => false);					
				}

			}

			//error
			else{
				$returnArray = array("ERROR" => "Y", "IBLOCK_ID_EMPTY" => true);					
			}


			//return json result
			echo \Bitrix\Main\Web\Json::encode($returnArray);

		}
	}

?>