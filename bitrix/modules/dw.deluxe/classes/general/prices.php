<?
use Bitrix\Highloadblock as HL;
use Bitrix\Main\Entity;
use Bitrix\Main;

class DwPrices {

	//get sku properties
    public static function getPriceInfo($arOpPriceCodes = array(), $iblock_id = 0){

    	//global vars
    	global $USER;

		//get user groups for create cache
		$arCache = array(
			"USER_GROUP" => $USER->GetUserGroupString(),
			"PRICE_CODES" => $arOpPriceCodes,
			"SITE_ID" => SITE_ID
		);

		//time to life cache
		$cacheTime = 21285912;

		//create cache id
		$cacheID = serialize($arCache);

		//cache dir ( / - all)
		$cacheDir = "/";

		//extra settings from cache
		$obPriceCache = new CPHPCache();

		if($obPriceCache->InitCache($cacheTime, $cacheID, $cacheDir)){
			$arPrices = $obPriceCache->GetVars();
		}

		elseif($obPriceCache->StartDataCache()){

			//check include modules
			if(
				   !\Bitrix\Main\Loader::includeModule("iblock")
				|| !\Bitrix\Main\Loader::includeModule("catalog")
				|| !\Bitrix\Main\Loader::includeModule("sale")
			){

				$obPriceCache->AbortDataCache();
				ShowError("modules not installed!");
				return 0;

			}

			//get price infor
			$arPrices = array();
			$arPrices["ALLOW"] = array();
			$arPrices["ALLOW_FILTER"] = array();

			if(!empty($arOpPriceCodes)){

				$dbPriceType = CCatalogGroup::GetList(
			        array("SORT" => "ASC"),
			        array("NAME" => $arOpPriceCodes)
			    );

				while ($arPriceType = $dbPriceType->Fetch()){

					if($arPriceType["CAN_BUY"] == "Y"){
				    	$arPrices["ALLOW"][] = $arPriceType;
					}

				    $arPrices["ALLOW_FILTER"][] = $arPriceType["ID"];

				}

			}

			//target cache
			if(!empty($iblock_id)){

				global $CACHE_MANAGER;
				$CACHE_MANAGER->StartTagCache($cacheDir);
				$CACHE_MANAGER->RegisterTag("iblock_id_".$iblock_id);
				$CACHE_MANAGER->EndTagCache();

			}

			//save cache
			$obPriceCache->EndDataCache($arPrices);

			//drop
			unset($obPriceCache);

		}

		return $arPrices;

    }

	//get prices by product id
    public static function getPricesByProductId($productId = 0, $arPriceAllow = array(), $arPriceAllowFilter = array(), $arPriceCodes = array(), $iblock_id = 0, $opCurrency = null){

    	//globals
    	global $USER;

		//check include modules
		if(
			   !\Bitrix\Main\Loader::includeModule("iblock")
			|| !\Bitrix\Main\Loader::includeModule("catalog")
			|| !\Bitrix\Main\Loader::includeModule("sale")
		){

			ShowError("modules not installed!");
			return 0;

		}

		//result array
    	$arItemPrice = array();

		//get allow prices info
		if(!empty($arPriceAllow)){
			$arOpPriceCodes = array();
			foreach($arPriceAllow as $ipc => $arNextAllowPrice){
				$dbPrice = CPrice::GetList(
			        array(),
			        array(
			            "PRODUCT_ID" => $productId,
			            "CATALOG_GROUP_ID" => $arNextAllowPrice["ID"]
			        )
			    );
				if($arPriceValues = $dbPrice->Fetch()){
					$arOpPriceCodes[] = array(
						"ID" => $arNextAllowPrice["ID"],
						"PRICE" => $arPriceValues["PRICE"],
						"CURRENCY" => $arPriceValues["CURRENCY"],
						"CATALOG_GROUP_ID" => $arNextAllowPrice["ID"]
					);
				}
			}
		}

		//set base currency for getOptimalPrice function
		if(!empty($opCurrency)){
			//set currency
			CCatalogProduct::setUsedCurrency($opCurrency);
		}else{
			//clear used currency
			CCatalogProduct::clearUsedCurrency();
		}

		//set optimal price and discounts
		if(!empty($arPriceAllow) && !empty($arOpPriceCodes) || empty($arPriceCodes)){
			$arItemPrice = CCatalogProduct::GetOptimalPrice($productId, 1, $USER->GetUserGroupArray(), "N", $arOpPriceCodes);
		}

		//price count
		$arPriceFilter = array("PRODUCT_ID" => $productId, "CAN_ACCESS" => "Y");
		if(!empty($arPriceAllowFilter)){
			$arPriceFilter["CATALOG_GROUP_ID"] = $arPriceAllowFilter;
		}

		$dbPrice = CPrice::GetList(
	        array(),
	        $arPriceFilter,
	        false,
	        false,
	        array("ID", "CATALOG_GROUP_ID", "PRICE", "CURRENCY", "QUANTITY_FROM", "QUANTITY_TO", "CAN_BUY")
	    );

		//if > 0 display [?] for more price table
		if(!empty($arItemPrice)){
			$arItemPrice["COUNT_PRICES"] = $dbPrice->SelectedRowsCount();
		}

		//extended prices
		if(!empty($arItemPrice["COUNT_PRICES"])){

			//vars
			$arItemPrice["EXTENDED_PRICES"] = array();

			//get prices
			while ($arPrice = $dbPrice->Fetch()){

				//check min available price
				if($arPrice["CATALOG_GROUP_ID"] == $arItemPrice["PRICE"]["CATALOG_GROUP_ID"] && $arPrice["CAN_BUY"] == "Y"){

				    //check quantity
				    if(!empty($arPrice["QUANTITY_TO"]) || !empty($arPrice["QUANTITY_FROM"])){

					    //get discounts
					    $arDiscounts = CCatalogDiscount::GetDiscountByPrice(
				            $arPrice["ID"],
				            $USER->GetUserGroupArray(),
				            "N",
				            SITE_ID
				        );

					    //get discount price
					    $arPrice["DISCOUNT_PRICE"] = CCatalogProduct::CountPriceWithDiscount(
				            $arPrice["PRICE"],
				            $arPrice["CURRENCY"],
				            $arDiscounts
				        );

					    //convert currency
						$arItemPrice["EXTENDED_PRICES"][$arPrice["ID"]] = array(
							"DISCOUNT_PRICE" => !empty($opCurrency) ? CCurrencyRates::ConvertCurrency($arPrice["DISCOUNT_PRICE"], $arPrice["CURRENCY"], $opCurrency) : $arPrice["DISCOUNT_PRICE"],
							"PRICE" => !empty($opCurrency) ? CCurrencyRates::ConvertCurrency($arPrice["PRICE"], $arPrice["CURRENCY"], $opCurrency) : $arPrice["PRICE"],
							"QUANTITY_FROM" => $arPrice["QUANTITY_FROM"],
							"QUANTITY_TO" => $arPrice["QUANTITY_TO"]
						);

						//calc old price
						if($arItemPrice["EXTENDED_PRICES"][$arPrice["ID"]]["PRICE"] > $arItemPrice["EXTENDED_PRICES"][$arPrice["ID"]]["DISCOUNT_PRICE"]){
							$arItemPrice["EXTENDED_PRICES"][$arPrice["ID"]]["OLD_PRICE"] = $arItemPrice["EXTENDED_PRICES"][$arPrice["ID"]]["PRICE"];
						}

						//calc economy
						if(!empty($arItemPrice["EXTENDED_PRICES"][$arPrice["ID"]]["OLD_PRICE"])){
							$arItemPrice["EXTENDED_PRICES"][$arPrice["ID"]]["ECONOMY"] = $arItemPrice["EXTENDED_PRICES"][$arPrice["ID"]]["PRICE"] - $arItemPrice["EXTENDED_PRICES"][$arPrice["ID"]]["DISCOUNT_PRICE"];
						}

					}
				}

			}

		}

		//return data;
		return $arItemPrice;

	}

}
?>