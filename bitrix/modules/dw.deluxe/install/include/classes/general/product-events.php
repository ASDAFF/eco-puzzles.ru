<?
//product events
class DwProductEvents{

	//vars
	private static $siteLang = "ru";
	private static $lastId = 0;
	private static $brandPropertyCode = "ATT_BRAND";
	private static $collectionPropertyCode = "COLLECTION";

	//functions
	public static function productUpdate(\Bitrix\Main\Event $event){

    	//get settings
		$arTemplateSettings = DwSettings::getInstance()->getSettingsFromOption();
		$arEventParams = $event->getParameters();

		//deactivate products by null quantity
		if(!empty($arTemplateSettings)){
			self::deactivateProduct($arEventParams, $arTemplateSettings);
		}

	}

	public static function productAfterSave($arg1, $arg2 = false){

    	//get settings
		$arTemplateSettings = DwSettings::getInstance()->getSettingsFromOption();

		//check settings
		if(!empty($arTemplateSettings)){

			//min max price
			self::sortPriceAutoUpdate($arg1, $arg2, $arTemplateSettings);

			//auto collection
			self::collectionAutoUpdate($arg1, $arg2, $arTemplateSettings);

			//auto brands
			self::brandsAutoUpdate($arg1, $arg2, $arTemplateSettings);

		}

	}

	public static function deactivateProduct($arEventParams, $arTemplateSettings = array()){

		//check settings
		if(empty($arTemplateSettings)){
			return false;
		}

		//load modules
		\Bitrix\Main\Loader::includeModule("iblock");

		//vars
		$productId = !empty($arEventParams["id"]) ? $arEventParams["id"] : false;
		$productIblockId = !empty($arEventParams["external_fields"]["IBLOCK_ID"]) ? $arEventParams["external_fields"]["IBLOCK_ID"] : false;
		$arProcessProductsId = array();
		$arCurrentSettings = array();
		$currentSiteId = false;

		//block looping
		if(self::$lastId == $productId){
			return false;
		}

		//check iblock
		if(empty($productIblockId)){
			return false;
		}

		//get binding sites
		$rsIblock = CIBlock::GetSite($productIblockId);
		while($arIblockSites = $rsIblock->Fetch()){
			if(!empty($arIblockSites["LID"]) && !empty($arTemplateSettings[$arIblockSites["LID"]])){
				//set current settings from binding site id
				$arCurrentSettings = $arTemplateSettings[$arIblockSites["LID"]];
			}
		}

		if(!empty($arCurrentSettings["TEMPLATE_USE_AUTO_DEACTIVATE_PRODUCTS"]) && $arCurrentSettings["TEMPLATE_USE_AUTO_DEACTIVATE_PRODUCTS"] == "Y"){

			//is id sku offer?
			$skuResult = CCatalogSku::GetProductInfo($productId);
			if(is_array($skuResult)){
				$productId = $skuResult["ID"];
				$productIblockId = $skuResult["IBLOCK_ID"];
			}

			//check exist offers
			$offersExist = CCatalogSKU::getExistOffers(array($productId), $productIblockId);
			if(!empty($offersExist[$productId])){

				//get sku iblock info
				$skuIblockInfo = CCatalogSKU::GetInfoByProductIBlock($productIblockId);
				if(is_array($skuIblockInfo)){

					//vars
					$productNotDiactivate = false;

					//get iblock offers
					$rsOffers = CIBlockElement::GetList(
						array(),
						array(
							"IBLOCK_ID" => $skuIblockInfo["IBLOCK_ID"],
							"PROPERTY_".$skuIblockInfo["SKU_PROPERTY_ID"] => $productId,
						),
						false,
						false,
						array("ID", "IBLOCK_ID", "ACTIVE", "CATALOG_QUANTITY", "CATALOG_AVAILABLE")
					);

					while($arOffer = $rsOffers->Fetch()){

						//check quantity
						if($arOffer["CATALOG_QUANTITY"] <= 0){
							//save product id for disable
							if($arOffer["ACTIVE"] == "Y"){
								$arProcessProductsId[$arOffer["ID"]] = false;
							}
						}

						else{
							//save product id for activate
							if($arOffer["ACTIVE"] == "N"){
								$arProcessProductsId[$arOffer["ID"]] = true;
							}
							//set flag
							$productNotDiactivate = true;
						}

					}

					$arProcessProductsId[$productId] = $productNotDiactivate;

				}
			}

			//has not sku offers
			else{

				//get product quantity
				$rsElement = CIBlockElement::GetList(
					array(),
					array(
						"ID" => $productId,
					),
					false,
					false,
					array("ID", "IBLOCK_ID", "ACTIVE", "CATALOG_QUANTITY", "CATALOG_AVAILABLE")
				);
				//

				if($arNextElement = $rsElement->Fetch()){

					//check quantity
					if($arNextElement["CATALOG_QUANTITY"] <= 0){
						//save product id for disable
						if($arNextElement["ACTIVE"] == "Y"){
							$arProcessProductsId[$productId] = false;
						}
					}

					else{
						//save product id for activate
						if($arNextElement["ACTIVE"] == "N"){
							$arProcessProductsId[$productId] = true;
						}
					}

				}

			}

			//process changes
			if(!empty($arProcessProductsId)){

				//each accumulated items
				foreach($arProcessProductsId as $nextProductId => $productActiveFlag){

					//save last id for block looping
					self::$lastId = $nextProductId;

					//update iblock element
					$updateElement = new CIBlockElement;
					if(!$updateElement->Update($nextProductId, array("ACTIVE" => !empty($productActiveFlag) ? "Y" : "N"))){
						file_put_contents($_SERVER["DOCUMENT_ROOT"]."/events_error.txt", $updateElement->LAST_ERROR);
					}

					unset($updateElement);

				}

			}

		}

	}

	//auto collection system
	public static function collectionAutoUpdate($arg1, $arg2 = false, $arTemplateSettings = array()){

		//load modules
		\Bitrix\Main\Loader::includeModule("catalog");
		\Bitrix\Main\Loader::includeModule("iblock");

		//vars
		$productId = (!empty($arg1) && is_numeric($arg1) ? $arg1 : (!empty($arg1["ID"]) ? $arg1["ID"] : (!empty($arg2["PRODUCT_ID"]) ? $arg2["PRODUCT_ID"] : false)));
		$productIblockId = (!empty($arg1["IBLOCK_ID"]) ? $arg1["IBLOCK_ID"] : (!empty($arg2["IBLOCK_ID"]) ? $arg2["IBLOCK_ID"] : false));
		$arSettings = array();

		//check vars
		if(empty($arTemplateSettings) || empty($productIblockId) || empty($productId)){
			return false;
		}

		//get binding sites
		$rsIblock = CIBlock::GetSite($productIblockId);
		while($arIblockSites = $rsIblock->Fetch()){
			if(!empty($arIblockSites["LID"]) && !empty($arTemplateSettings[$arIblockSites["LID"]])){
				//set current settings from binding site id
				$arSettings = $arTemplateSettings[$arIblockSites["LID"]];
			}
		}

		//check settings for current iblock (site)
		if(empty($arSettings)){
			return false;
		}

		//check active option
		if(!empty($arSettings["TEMPLATE_USE_AUTO_COLLECTION"]) && $arSettings["TEMPLATE_USE_AUTO_COLLECTION"] == "Y"){

			//check fields options
			if(!empty($arSettings["TEMPLATE_COLLECTION_IBLOCK_ID"]) && !empty($arSettings["TEMPLATE_COLLECTION_PROPERTY_CODE"])){

				//set params
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

		}

	}

	//auto brand system
	public static function brandsAutoUpdate($arg1, $arg2 = false, $arTemplateSettings = array()){

		//load modules
		\Bitrix\Main\Loader::includeModule("catalog");
		\Bitrix\Main\Loader::includeModule("iblock");

		//vars
		$productId = (!empty($arg1) && is_numeric($arg1) ? $arg1 : (!empty($arg1["ID"]) ? $arg1["ID"] : (!empty($arg2["PRODUCT_ID"]) ? $arg2["PRODUCT_ID"] : false)));
		$productIblockId = (!empty($arg1["IBLOCK_ID"]) ? $arg1["IBLOCK_ID"] : (!empty($arg2["IBLOCK_ID"]) ? $arg2["IBLOCK_ID"] : false));
		$arSettings = array();

		//check vars
		if(empty($arTemplateSettings) || empty($productIblockId) || empty($productId)){
			return false;
		}

		//get binding sites
		$rsIblock = CIBlock::GetSite($productIblockId);
		while($arIblockSites = $rsIblock->Fetch()){
			if(!empty($arIblockSites["LID"]) && !empty($arTemplateSettings[$arIblockSites["LID"]])){
				//set current settings from binding site id
				$arSettings = $arTemplateSettings[$arIblockSites["LID"]];
			}
		}

		//check settings for current iblock (site)
		if(empty($arSettings)){
			return false;
		}

		//check active option
		if(!empty($arSettings["TEMPLATE_USE_AUTO_BRAND"]) && $arSettings["TEMPLATE_USE_AUTO_BRAND"] == "Y"){

			//check fields options
			if(!empty($arSettings["TEMPLATE_BRAND_IBLOCK_ID"]) && !empty($arSettings["TEMPLATE_BRAND_PROPERTY_CODE"])){

				//set params
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
	public static function sortPriceAutoUpdate($arg1, $arg2 = false, $arTemplateSettings = array()){

		//load modules
		\Bitrix\Main\Loader::includeModule("catalog");
		\Bitrix\Main\Loader::includeModule("iblock");
		\Bitrix\Main\Loader::includeModule("sale");

		//vars
		$productId = (!empty($arg1) && is_numeric($arg1) ? $arg1 : (!empty($arg1["ID"]) ? $arg1["ID"] : (!empty($arg2["PRODUCT_ID"]) ? $arg2["PRODUCT_ID"] : false)));
		$productIblockId = (!empty($arg1["IBLOCK_ID"]) ? $arg1["IBLOCK_ID"] : (!empty($arg2["IBLOCK_ID"]) ? $arg2["IBLOCK_ID"] : false));
		$arSettings = array();

		//check vars
		if(empty($arTemplateSettings) || empty($productIblockId) || empty($productId)){
			return false;
		}

		//get binding sites
		$rsIblock = CIBlock::GetSite($productIblockId);
		while($arIblockSites = $rsIblock->Fetch()){
			if(!empty($arIblockSites["LID"]) && !empty($arTemplateSettings[$arIblockSites["LID"]])){
				//set current settings from binding site id
				$arSettings = $arTemplateSettings[$arIblockSites["LID"]];
			}
		}

		//check settings for current iblock (site)
		if(empty($arSettings)){
			return false;
		}

		if(!empty($arSettings["TEMPLATE_USE_AUTO_SAVE_PRICE"]) && $arSettings["TEMPLATE_USE_AUTO_SAVE_PRICE"] == "Y"){

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

}
?>