<?require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_before.php");?>
<?include("lang/".LANGUAGE_ID."/template.php");?>
<?if(!empty($_GET["act"]) && CModule::IncludeModule("catalog") && CModule::IncludeModule("sale")){

	if($_GET["act"] == "upd"){
		echo CSaleBasket::Update(intval($_GET['id']), array(
		   "QUANTITY" => intval($_GET["q"]),
		   "DELAY" => "N"
		));
	}elseif($_GET["act"] == "del"){
		echo CSaleBasket::Delete(intval($_GET['id']));
	}
	elseif($_GET["act"] == "emp"){
		echo CSaleBasket::DeleteAll(CSaleBasket::GetBasketUserID());
	}

	##### ORDER #####

	elseif($_GET["act"] == "location" && !empty($_GET["q"])){

		$LOCATIONS = array();
		$CITY_NAME = (BX_UTF === true) ? $_GET["q"] : iconv("UTF-8", "CP1251//IGNORE", $_GET["q"]);

		$dbLocations = CSaleLocation::GetList(
			array(
				"SORT" => "ASC",
				"COUNTRY_NAME_LANG" => "ASC",
				"CITY_NAME_LANG" => "ASC"
			),
			array(
				"LID" => LANGUAGE_ID,
				"%CITY_NAME" => $CITY_NAME
			),
			false,
			Array(
				"nPageSize" => 5,
			),
			array()
		);
		while ($arLoc = $dbLocations->Fetch()){
			if(!empty($arLoc["CITY_NAME"])){
				$arLoc["REGION_NAME"] = !empty($arLoc["REGION_NAME"]) ? $arLoc["REGION_NAME"].", " : "";
				$LOCATIONS[$arLoc["ID"]] = $arLoc["COUNTRY_NAME"].", ".$arLoc["REGION_NAME"].$arLoc["CITY_NAME"];
			}
		}
		echo jsonEn($LOCATIONS);
	}

	##### ORDER MAKE #####

	elseif ($_GET["act"] == "orderMake") {
		
		global $USER;
		
		$OPTION_CURRENCY  = CCurrency::GetBaseCurrency();
		$db_props = CSaleOrderProps::GetList(
	        array("SORT" => "ASC"),
	        array(
	                "PERSON_TYPE_ID" => intval($_GET["PERSON_TYPE"]),
	                "IS_EMAIL" => "Y",
	                "CODE" => "EMAIL"
	            ),
	        false,
	        false,
	        array()
	    );

		if ($props = $db_props->Fetch()){
			if($props["REQUIED"] == "Y"){
				$OPTION_REGISTER = "Y";
			}
		}

		if(!$USER->IsAuthorized()){
			if($OPTION_REGISTER == "Y"){
				$arResult = $USER->SimpleRegister($_GET["email"]);
				if($arResult["TYPE"] == "ERROR"){
					exit(
						jsonEn(
							array(
								"ERROR" => $arResult["MESSAGE"]
							)
						)
					);
				}else{
					CUser::SendUserInfo($USER->GetID(), SITE_ID, GetMessage("NEW_REGISTER"));
				}
			}else{

				$rsUser = CUser::GetByLogin("unregistered");
				$arUser = $rsUser->Fetch();
				if(!empty($arUser)){
					$USER_ID = $arUser["ID"];
				}else{

					$newUser = new CUser;
					$newPass = rand(0, 999999999);
					$arUserFields = Array(
					  "NAME"              => "unregistered",
					  "LAST_NAME"         => "unregistered",
					  "EMAIL"             => "unregistered@unregistered.com",
					  "LOGIN"             => "unregistered",
					  "LID"               => "ru",
					  "ACTIVE"            => "Y",
					  "GROUP_ID"          => array(),
					  "PASSWORD"          => $newPass,
					  "CONFIRM_PASSWORD"  => $newPass,
					);
					
					$USER_ID = $newUser->Add($arUserFields);
				}
			}
		}

		$ORDER_PRICE    = 0;
		$ORDER_QUANTITY = 0;
		$ORDER_DISCOUNT = 0;
		$ORDER_MESSAGE  = "<tr><td>".GetMessage("TOP_NAME")."</td><td>".GetMessage("TOP_QTY")."</td><td>".GetMessage("PRICE")."</td></tr>";

		CSaleBasket::UpdateBasketPrices(CSaleBasket::GetBasketUserID(), $_GET["SITE_ID"]);

		$res = CSaleBasket::GetList(
			array(
				"ID" => "ASC"
			),
			array(
					"FUSER_ID" => CSaleBasket::GetBasketUserID(),
					"LID" => $_GET["SITE_ID"],
					"ORDER_ID" => "NULL"
				),
			false,
			false,
			array(
				"ID",
				"PRODUCT_ID",
				"QUANTITY",
				"PRICE",
				"DISCOUNT_PRICE",
				"NAME",
				"CURRENCY"
			)
		);

		if($res->SelectedRowsCount() <= 0){
			exit(
				jsonEn(
					array(
						"ERROR" => GetMessage("ORDER_EMPTY")
					)
				)
			);
		}

		while ($arRes = $res->GetNext()){
			$ORDER_DISCOUNT  += ($arRes["QUANTITY"] * $arRes["DISCOUNT_PRICE"]);
			$ORDER_PRICE     += ($arRes["PRICE"] * $arRes["QUANTITY"]);
			$ORDER_QUANTITY  += $arRes["QUANTITY"];
			$ORDER_MESSAGE   .= "<tr><td>".$arRes["NAME"]."</td><td>".$arRes["QUANTITY"]."</td><td>".SaleFormatCurrency($arRes["PRICE"], $arRes["CURRENCY"])." ".$arRes["CURRENCY"]."</td></tr>";
		}

		$DELIVERY_INFO = CSaleDelivery::GetByID($_GET["DEVIVERY_TYPE"]);

		$ORDER_ID = CSaleOrder::Add(
			array(
			   "LID" => $_GET["SITE_ID"],
			   "PERSON_TYPE_ID" => $_GET["PERSON_TYPE"],
			   "PAYED" => "N",
			   "CANCELED" => "N",
			   "STATUS_ID" => "N",
			   "PRICE" => ($DELIVERY_INFO["PRICE"] + $ORDER_PRICE),
			   "CURRENCY" => $OPTION_CURRENCY,
			   "USER_ID" => !empty($USER_ID) ? $USER_ID : IntVal($USER->GetID()),
			   "PAY_SYSTEM_ID" => $_GET["PAY_TYPE"],
			   "PRICE_DELIVERY" => $DELIVERY_INFO["PRICE"],
			   "DELIVERY_ID" => $_GET["DEVIVERY_TYPE"],
			   "DISCOUNT_VALUE" => $ORDER_DISCOUNT,
			   "TAX_VALUE" => 0.0,
			   "USER_DESCRIPTION" => (BX_UTF === true) ? $_GET["COMMENT"] : iconv("UTF-8", "windows-1251//IGNORE", $_GET["COMMENT"])
			)
		);

		if(empty($ORDER_ID)){

			exit(
				jsonEn(
					array(
						"ERROR" => !empty($gEX = $APPLICATION->GetException()) ? $gEX->GetString() : GetMessage("ORDER_ERROR")
					)
				)
			);
		}

		$orderInfo = CSaleOrder::GetByID($ORDER_ID);

		CSaleBasket::OrderBasket(
			$ORDER_ID, $_SESSION["SALE_USER_ID"], $_GET["SITE_ID"]
		);

		foreach ($_GET as $i => $prop_value) {
			if(strstr($i, "ORDER_PROP")){

				$nextProp = CSaleOrderProps::GetByID(
					preg_replace('/[^0-9]/', '', $i)
				);

				if($nextProp["IS_LOCATION"] === "Y"){
					$prop_value = $_GET["location"];
				}

				CSaleOrderPropsValue::Add(
					array(
					   "ORDER_ID" => $ORDER_ID,
					   "ORDER_PROPS_ID" => $nextProp["ID"],
					   "NAME" => $nextProp["NAME"],
					   "CODE" => $nextProp["CODE"],
					   "VALUE" => (BX_UTF === true) ? $prop_value : iconv("UTF-8", "windows-1251//IGNORE", $prop_value)
					)
				);
			}
		}

		$PAYSYSTEM = CSalePaySystem::GetByID(
			$_GET["PAY_TYPE"],
			$_GET["PERSON_TYPE"]
		);
		
		$res = CSalePaySystemAction::GetList(
			array(),
			array(
					"PAY_SYSTEM_ID" => $PAYSYSTEM["ID"],
					"PERSON_TYPE_ID" => $_GET["PERSON_TYPE"]
				),
			false,
			false,
			array("ID", "PAY_SYSTEM_ID", "PERSON_TYPE_ID", "NAME", "ACTION_FILE", "RESULT_FILE", "NEW_WINDOW", "PARAMS", "ENCODING", "LOGOTIP")
		);

		if ($PAYSYSTEM_ACTION = $res->Fetch()){
			$dbOrder = CSaleOrder::GetList(
				array("DATE_UPDATE" => "DESC"),
				array(
					"LID" => $_GET["SITE_ID"],
					"ID" => $ORDER_ID
				)
			);
			if($arOrder = $dbOrder->GetNext()){
				CSalePaySystemAction::InitParamArrays($arOrder, $arOrder["ID"], $PAYSYSTEM_ACTION["PARAMS"]);
				$PAY_DATA = returnBuff($_SERVER["DOCUMENT_ROOT"].$PAYSYSTEM_ACTION["ACTION_FILE"]."/payment.php");
				echo jsonEn(
					array(
						"ORDER_ID" => $orderInfo["ACCOUNT_NUMBER"],
						"NEW_WINDOW" => $PAYSYSTEM_ACTION["NEW_WINDOW"],
						"PAYSYSTEM" => trim(
							str_replace(
								array("\n", "\r", "\t"), "", $PAY_DATA)
						)
					)
				);
			}
		}
	
		$arFields = Array(
			"ORDER_ID" => $orderInfo["ACCOUNT_NUMBER"],
			"ORDER_DATE" => Date($DB->DateFormatToPHP(CLang::GetDateFormat("SHORT", $_GET["SITE_ID"]))),
			"ORDER_USER" => $USER->GetFormattedName(false),
			"PRICE" => SaleFormatCurrency($ORDER_PRICE, $OPTION_CURRENCY),
			"BCC" => COption::GetOptionString("sale", "order_email", "order@".$SERVER_NAME),
			"EMAIL" => !empty($_GET["email"]) ? $_GET["email"] : $USER->GetEmail(),
			"ORDER_LIST" => "<table width=100%>".$ORDER_MESSAGE."</table>",
			"SALE_EMAIL" => COption::GetOptionString("sale", "order_email", "order@".$SERVER_NAME),
			"DELIVERY_PRICE" => $DELIVERY_INFO["PRICE"],
		);

		$eventName = "SALE_NEW_ORDER";

		$bSend = true;
		foreach(GetModuleEvents("sale", "OnOrderNewSendEmail", true) as $arEvent)
			if (ExecuteModuleEventEx($arEvent, Array($ORDER_ID, &$eventName, &$arFields))===false)
				$bSend = false;

		if($bSend){
			$event = new CEvent;
			$event->Send($eventName, $_GET["SITE_ID"], $arFields, "N");
		}

		CSaleMobileOrderPush::send("ORDER_CREATED", array("ORDER_ID" => $arFields["ORDER_ID"]));
	}
}else{
	die(false);
}

function jsonEn($data){
	foreach ($data as $index => $arValue) {
		$arJsn[] = '"'.$index.'" : "'.addslashes($arValue).'"';
	}
	return  "{".implode($arJsn, ",")."}";
}

function returnBuff($file){
	ob_start();
	include($file);
	$fData = ob_get_contents();
	ob_end_clean();
	return $fData;
}

?>