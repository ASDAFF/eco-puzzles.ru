<?

//product events
class DwProductEvents{

	//vars
	private static $siteLang = "ru";
	private static $brandPropertyCode = "ATT_BRAND";
	private static $collectionPropertyCode = "COLLECTION";

	//functions
	public static function productAfterSave($arg1, $arg2 = false){
    	
    	//get settings
		$arTemplateSettings = DwSettings::getInstance()->getCurrentSettings();

		//min max price
		if(!empty($arTemplateSettings["TEMPLATE_USE_AUTO_SAVE_PRICE"]) && $arTemplateSettings["TEMPLATE_USE_AUTO_SAVE_PRICE"] == "Y"){
			self::sortPriceAutoUpdate($arg1, $arg2);
		}

		//auto brands
		//check active option
		if(!empty($arTemplateSettings["TEMPLATE_USE_AUTO_BRAND"]) && $arTemplateSettings["TEMPLATE_USE_AUTO_BRAND"] == "Y"){
			
			//check fields options
			if(!empty($arTemplateSettings["TEMPLATE_BRAND_IBLOCK_ID"]) && !empty($arTemplateSettings["TEMPLATE_BRAND_PROPERTY_CODE"])){
				self::brandsAutoUpdate($arg1, $arg2, $arTemplateSettings);
			}

		}

		//auto collection
		//check active option
		if(!empty($arTemplateSettings["TEMPLATE_USE_AUTO_COLLECTION"]) && $arTemplateSettings["TEMPLATE_USE_AUTO_COLLECTION"] == "Y"){
			
			//check fields options
			if(!empty($arTemplateSettings["TEMPLATE_COLLECTION_IBLOCK_ID"]) && !empty($arTemplateSettings["TEMPLATE_COLLECTION_PROPERTY_CODE"])){
				self::collectionAutoUpdate($arg1, $arg2, $arTemplateSettings);
			}

		}

	}		


	//auto collection system
	public static function collectionAutoUpdate($arg1, $arg2, $arSettings){

		//load modules
		\Bitrix\Main\Loader::includeModule("catalog");
		\Bitrix\Main\Loader::includeModule("iblock");
		\Bitrix\Main\Loader::includeModule("sale");

		//vars
		$productId = !empty($arg1["PRODUCT_ID"]) ? $arg1["PRODUCT_ID"] : (!empty($arg2["PRODUCT_ID"]) ? $arg2["PRODUCT_ID"] : false);
		$collectionIblockId = $arSettings["TEMPLATE_COLLECTION_IBLOCK_ID"];
		$collectionCode = $arSettings["TEMPLATE_COLLECTION_PROPERTY_CODE"];

		//is id sku offer?
		$skuResult = CCatalogSku::GetProductInfo($productId);
		if(is_array($skuResult)){
			$productId = $skuResult["ID"];
		}

		//get element
		$rsElement = CIBlockElement::GetList(
			array(),
			array(
				"ID" => $productId,
			),
			false,
			false,
			array("ID", "CODE", "IBLOCK_ID")
		);

		if($nextElement = $rsElement->GetNextElement()){

			//get element selected fileds
			$arElement = $nextElement->GetFields();

			//get brand property value
			$rsProperty = CIBlockElement::GetProperty($arElement["IBLOCK_ID"], $arElement["ID"], array(), array("CODE" => $collectionCode));
			if($arProperty = $rsProperty->Fetch()){
				if(!empty($arProperty["VALUE_ENUM"])){
					if($collectionElementId = self::iblockBindUpdate($productId, $collectionIblockId, $arProperty["VALUE_ENUM"])){
						self::productBindUpdate($productId, self::$collectionPropertyCode, $collectionElementId, $collectionIblockId);
					}
				}
			}

		}

	}

	//auto brand system
	public static function brandsAutoUpdate($arg1, $arg2, $arSettings){

		//load modules
		\Bitrix\Main\Loader::includeModule("catalog");
		\Bitrix\Main\Loader::includeModule("iblock");
		\Bitrix\Main\Loader::includeModule("sale");

		//vars
		$productId = !empty($arg1["PRODUCT_ID"]) ? $arg1["PRODUCT_ID"] : (!empty($arg2["PRODUCT_ID"]) ? $arg2["PRODUCT_ID"] : false);
		$brandIblockId = $arSettings["TEMPLATE_BRAND_IBLOCK_ID"];
		$brandCode = $arSettings["TEMPLATE_BRAND_PROPERTY_CODE"];

		//is id sku offer?
		$skuResult = CCatalogSku::GetProductInfo($productId);
		if(is_array($skuResult)){
			$productId = $skuResult["ID"];
		}

		//get element
		$rsElement = CIBlockElement::GetList(
			array(),
			array(
				"ID" => $productId,
			),
			false,
			false,
			array("ID", "CODE", "IBLOCK_ID")
		);

		if($nextElement = $rsElement->GetNextElement()){

			//get element selected fileds
			$arElement = $nextElement->GetFields();

			//get brand property value
			$rsProperty = CIBlockElement::GetProperty($arElement["IBLOCK_ID"], $arElement["ID"], array(), array("CODE" => $brandCode));
			if($arProperty = $rsProperty->Fetch()){
				if(!empty($arProperty["VALUE_ENUM"])){
					if($brandElementId = self::iblockBindUpdate($productId, $brandIblockId, $arProperty["VALUE_ENUM"])){
						self::productBindUpdate($productId, self::$brandPropertyCode, $brandElementId, $brandIblockId);
					}
				}
			}

		}

	}

	//product service update
	public static function productBindUpdate($productId, $propertyCode, $bindElementId, $bindIblockId){

		if(empty($productId) || empty($bindElementId) || empty($bindIblockId) || empty($propertyCode)){
			return false;
		}

		//update property value
		return CIBlockElement::SetPropertyValueCode($productId, $propertyCode, array("VALUE" => $bindElementId));

	}

	//iblock service update
	public static function iblockBindUpdate($productId, $iblockId, $elementName){

		//check params
		if(empty($productId) || empty($elementName) || empty($iblockId)){
			return false;
		}

		//vars
		$elementCode = Cutil::translit($elementName, self::$siteLang);
		$returnId = false;

		//check iblock element
		$rsElement = CIBlockElement::GetList(
			array(),
			array(
				"IBLOCK_ID" => $iblockId,
				"CODE" => $elementCode
 			),
			false,
			false,
			array("ID", "CODE", "NAME", "IBLOCK_ID")
		);

		//check exist element
		if(!$checkElement = $rsElement->GetNextElement()){

			//new element fields
			$arFields = array(
				"PROPERTY_VALUES" => array(),
				"IBLOCK_ID" => $iblockId,
				"IBLOCK_SECTION_ID" => 0,
				"NAME" => $elementName,
				"CODE" => $elementCode,
				"DETAIL_TEXT" => "",
				"ACTIVE" => "Y",
			);

			//create new element
			$obElement = new CIBlockElement();
			$returnId = $obElement->Add($arFields, false, false, true);

		}

		//element exist
		else{

			//get element id
			$checkElementFields = $checkElement->GetFields();
			if(!empty($checkElementFields["ID"])){
				$returnId = $checkElementFields["ID"];
			}

		}

		//return element id
		return $returnId;

	}
 
	//min & max price update property
	public static function sortPriceAutoUpdate($arg1, $arg2){

		//load modules
		\Bitrix\Main\Loader::includeModule("catalog");
		\Bitrix\Main\Loader::includeModule("iblock");
		\Bitrix\Main\Loader::includeModule("sale");

		//globals
		global $USER;

		//vars
		$OFFERS_PROPERTY_ID = false;
		$OFFERS_IBLOCK_ID = false;
		$ELEMENT_ID = false;
		$IBLOCK_ID = false;

		if(\Bitrix\Main\Loader::includeModule("currency")){
			$strDefaultCurrency = CCurrency::GetBaseCurrency();
		}

		//Check for catalog event
		if(is_array($arg2) && !empty($arg2["PRODUCT_ID"])){

			//Get iblock element
			$rsPriceElement = CIBlockElement::GetList(
				array(),
				array(
					"ID" => $arg2["PRODUCT_ID"],
				),
				false,
				false,
				array("ID", "IBLOCK_ID")
			);

			if($arPriceElement = $rsPriceElement->Fetch()){
				$arCatalog = CCatalog::GetByID($arPriceElement["IBLOCK_ID"]);
				if(is_array($arCatalog)){
					//Check if it is offers iblock
					if($arCatalog["OFFERS"] == "Y"){
						//Find product element
						$rsElement = CIBlockElement::GetProperty(
							$arPriceElement["IBLOCK_ID"],
							$arPriceElement["ID"],
							"sort",
							"asc",
							array("ID" => $arCatalog["SKU_PROPERTY_ID"])
						);
						$arElement = $rsElement->Fetch();
						if($arElement && !empty($arElement["VALUE"])){
							$ELEMENT_ID = $arElement["VALUE"];
							$IBLOCK_ID = $arCatalog["PRODUCT_IBLOCK_ID"];
							$OFFERS_IBLOCK_ID = $arCatalog["IBLOCK_ID"];
							$OFFERS_PROPERTY_ID = $arCatalog["SKU_PROPERTY_ID"];
						}
					}
					//or iblock which has offers
					elseif(!empty($arCatalog["OFFERS_IBLOCK_ID"])){
						$ELEMENT_ID = $arPriceElement["ID"];
						$IBLOCK_ID = $arPriceElement["IBLOCK_ID"];
						$OFFERS_IBLOCK_ID = $arCatalog["OFFERS_IBLOCK_ID"];
						$OFFERS_PROPERTY_ID = $arCatalog["OFFERS_PROPERTY_ID"];
					}
					//or it's regular catalog
					else{
						$ELEMENT_ID = $arPriceElement["ID"];
						$IBLOCK_ID = $arPriceElement["IBLOCK_ID"];
						$OFFERS_IBLOCK_ID = false;
						$OFFERS_PROPERTY_ID = false;
					}
				}
			}
		}

		//Check for iblock event
		elseif(is_array($arg1) && !empty($arg1["ID"]) && !empty($arg1["IBLOCK_ID"])){
			//Check if iblock has offers
			$arOffers = CIBlockPriceTools::GetOffersIBlock($arg1["IBLOCK_ID"]);
			if(is_array($arOffers)){
				$ELEMENT_ID = $arg1["ID"];
				$IBLOCK_ID = $arg1["IBLOCK_ID"];
				$OFFERS_IBLOCK_ID = $arOffers["OFFERS_IBLOCK_ID"];
				$OFFERS_PROPERTY_ID = $arOffers["OFFERS_PROPERTY_ID"];
			}
		}

		if($ELEMENT_ID){
			$arPropCache = array();
			if(!array_key_exists($IBLOCK_ID, $arPropCache)){

				//Check for MINIMAL_PRICE property
				$rsProperty = CIBlockProperty::GetByID("MINIMUM_PRICE", $IBLOCK_ID);
				$arProperty = $rsProperty->Fetch();
				
				if($arProperty){
					$arPropCache[$IBLOCK_ID] = $arProperty["ID"];
				}

				else{
					$arPropCache[$IBLOCK_ID] = false;
				}

			}

			if($arPropCache[$IBLOCK_ID]){

				//Compose elements filter
				if($OFFERS_IBLOCK_ID){
					$rsOffers = CIBlockElement::GetList(
						array(),
						array(
							"IBLOCK_ID" => $OFFERS_IBLOCK_ID,
							"PROPERTY_".$OFFERS_PROPERTY_ID => $ELEMENT_ID,
							"ACTIVE" => "Y"
						),
						false,
						false,
						array("ID")
					);
					while($arOffer = $rsOffers->Fetch()){
						$arProductID[] = $arOffer["ID"];
					}

					if (!is_array($arProductID)){
						$arProductID = array($ELEMENT_ID);
					}
				}
				else{
					$arProductID = array($ELEMENT_ID);
				}

				$minPrice = false;
				$maxPrice = false;

				foreach ($arProductID as $productID){

					//get price
					$arDiscountPrice = CCatalogProduct::GetOptimalPrice($productID, 1, $USER->GetUserGroupArray(), false, false, $arCatalog["LID"]);

					//convert price
					if(!empty($strDefaultCurrency) && $strDefaultCurrency != $arDiscountPrice["RESULT_PRICE"]["CURRENCY"]){
						$arDiscountPrice["DISCOUNT_PRICE"] = CCurrencyRates::ConvertCurrency($arDiscountPrice["DISCOUNT_PRICE"], $arDiscountPrice["RESULT_PRICE"]["CURRENCY"], $strDefaultCurrency);
					}

					if($minPrice === false || $minPrice > $arDiscountPrice["DISCOUNT_PRICE"]){
						$minPrice = $arDiscountPrice["DISCOUNT_PRICE"];
					}

					if($maxPrice === false || $maxPrice < $arDiscountPrice["DISCOUNT_PRICE"]){
						$maxPrice = $arDiscountPrice["DISCOUNT_PRICE"];
					}
				}

				//Save found minimal price into property
				if($minPrice !== false){
					CIBlockElement::SetPropertyValuesEx(
						$ELEMENT_ID,
						$IBLOCK_ID,
						array(
							"MINIMUM_PRICE" => $minPrice,
							"MAXIMUM_PRICE" => $maxPrice,
						)
					);
				}
			}
		}		
	}

}

?>