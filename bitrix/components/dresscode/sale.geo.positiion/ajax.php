<?require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_before.php");?>
<?if(!empty($_GET["act"])){

	//normalize encoding
	if(!empty($_GET) && !defined("BX_UTF")){
		foreach ($_GET as $key => $nextValue) {
			$_GET[$key] = iconv("UTF-8", "WINDOWS-1251//IGNORE",  $nextValue);
		}
	}

	if($_GET["act"] == "userPosition"){
		if(CModule::IncludeModule("sale")){

			//get location id
			if(!empty($_GET["city"])){
				$dbLoc = CSaleLocation::GetList(
					array(
						"CITY_NAME_LANG" => "ASC"
		            ),
		            array("%CITY_NAME" => $_GET["city"], "LID" => LANGUAGE_ID),
		            false,
		            false,
		            array("*")
		        );

				if($arLocation = $dbLoc->Fetch()){
					$_SESSION["USER_GEO_POSITION"] = array(
						"isHighAccuracy" => $_GET["isHighAccuracy"],
						"locationID" => $arLocation["ID"],
						"latitude" => $_GET["latitude"],
						"longitude" => $_GET["longitude"],
						"country" => $_GET["country"],
						"region" => $_GET["region"],
						"city" => $_GET["city"],
						"zoom" => $_GET["zoom"]
					);

					echo \Bitrix\Main\Web\Json::encode($_SESSION["USER_GEO_POSITION"]);
				}else{
					echo \Bitrix\Main\Web\Json::encode(array("ERROR" => "Y"));
				}
			}
		}
	}

	elseif($_GET["act"] == "locSearch"){
		if(!empty($_GET["query"])){
			if(CModule::IncludeModule("sale")){
				$dbLoc = CSaleLocation::GetList(
					array(
						"SORT" => "ASC",
				        "COUNTRY_NAME_LANG" => "ASC",
				        "CITY_NAME_LANG" => "ASC"
				    ),
				    array(
				    	"LID" => LANGUAGE_ID,
				    	"%CITY_NAME" => $_GET["query"]
				    ),
				    false,
				    array("nPageSize" => 10),
				    array("*")
				);

				while($arLoc = $dbLoc->Fetch()){
					$arLocations[$arLoc["ID"]] = $arLoc;
				}

				if(empty($arLocations)){
					$arLocations = array("ERROR" => "Y");
				}

				echo \Bitrix\Main\Web\Json::encode($arLocations);

			}
		}
	}

	elseif($_GET["act"] == "setLocation"){
		if(!empty($_GET["locationID"])){
			if(CModule::IncludeModule("sale")){
				$dbLoc = CSaleLocation::GetList(
					array(
				    ),
				    array(
				    	"ID" => intval($_GET["locationID"]),
				    	"LID" => LANGUAGE_ID
				    ),
				    false,
				    array("nPageSize" => 1),
				    array("*")
				);

				if($arLoc = $dbLoc->Fetch()){

					$_SESSION["USER_GEO_POSITION"] = array(
						"locationID" => intval($_GET["locationID"]),
						"country" => $arLoc["COUNTRY_NAME"],
						"region" => $arLoc["REGION_NAME"],
						"city" => !empty($arLoc["CITY_NAME"]) ? $arLoc["CITY_NAME"] : $arLoc["REGION_NAME"], // if empty city set region
						"isHighAccuracy" => false,
						"longitude" => false,
						"latitude" => false,
						"zoom" => false
					);

					echo \Bitrix\Main\Web\Json::encode(array("SUCCESS" => "Y"));
				}
			}
		}
	}

}?>