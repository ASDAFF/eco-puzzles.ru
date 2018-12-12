<?require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_before.php");?>
<?CBitrixComponent::includeComponentClass("dresscode:fast.calculate.delivery");?>
<?if(!empty($_REQUEST["act"])){

	//vars
	$arResult = array();

	//check act
	if($_REQUEST["act"] == "getCalculatedItems"){

		//check product id
		if(!empty($_REQUEST["PARAMS"]["PRODUCT_ID"])){

			if(!empty($_REQUEST["quantity"])){
				$_REQUEST["PARAMS"]["PRODUCT_QUANTITY"] = floatval($_REQUEST["quantity"]);
			}

			if(!empty($_REQUEST["calcAllItems"])){
				$_REQUEST["PARAMS"]["CALC_ALL_PRODUCTS"] = htmlspecialcharsbx($_REQUEST["calcAllItems"]);
			}
			
			//get delivery data
			$arResult["DELIVERY_ITEMS"] = CDelivery::getCalculatedItems($_REQUEST["PARAMS"]);
			$arResult["MEASURE_RATIO"] = CDelivery::getMeasureRatio($_REQUEST["PARAMS"]["PRODUCT_ID"]);

			//show items
			if(!empty($arResult["DELIVERY_ITEMS"])){
				//buffer component html
				ob_start();
				include_once(__DIR__."/include/delivery_items.php");
				//save buffer
				$arResult["HTML_DATA"] = ob_get_contents();
				//end buffer
				ob_end_clean();	

			}

			//empty template
			else{
				//buffer component html
				ob_start();
				include_once(__DIR__."/include/empty.php");
				//save buffer
				$arResult["HTML_DATA"] = ob_get_contents();
				//end buffer
				ob_end_clean();	
			}

			echo \Bitrix\Main\Web\Json::encode(
				array("HTML_DATA" => $arResult["HTML_DATA"], "SUCCESS" => "Y")
			);

		}

		//error
		else{
			echo \Bitrix\Main\Web\Json::encode(
				array("ERROR" => "Y", "PRODUCT_ID" => "EMPTY")
			);
		}

	}
}
?>