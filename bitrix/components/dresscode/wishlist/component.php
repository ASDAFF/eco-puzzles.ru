<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)	die();?>
<?
	if(CModule::IncludeModule("iblock")){
		
		//globals
		global $arrFilter;
			
		//flag
		$arResult["SHOW_TEMPLATE"] = true;

		//check params
		if(!empty($arParams["IBLOCK_ID"])){

			//get sku iblock info
			$arOffersSkuInfo = CCatalogSKU::GetInfoByProductIBlock($arParams["IBLOCK_ID"]);

			//check items
			if(!empty($_SESSION["WISHLIST_LIST"]["ITEMS"])){

				//products id filter
				// $arrFilter["ID"] = $_SESSION["WISHLIST_LIST"]["ITEMS"];

				//sku filter
				if(!empty($arOffersSkuInfo)){
					$arrFilter[] = array(
						"LOGIC" => "OR",
						array(
							"ID" => CIBlockElement::SubQuery("ID", array("IBLOCK_ID" => $arOffersSkuInfo["IBLOCK_ID"], "PROPERTY_".$arOffersSkuInfo["SKU_PROPERTY_ID"] => $_SESSION["WISHLIST_LIST"]["ITEMS"]))
						),
						array(
							"ID" => $_SESSION["WISHLIST_LIST"]["ITEMS"]
						)
					);
		  		}
		  		//product no sku filter
		  		else{
		  			$arrFilter["ID"] = $_SESSION["WISHLIST_LIST"]["ITEMS"];
		  		}

			}

			else{
				//disable flag
				$arResult["SHOW_TEMPLATE"] = false;
			}
			
			$arParams["FILTER_NAME"] = "arrFilter";
			// $arParams["CURRENCY_ID"] = CCurrency::GetBaseCurrency();
			// $arParams['CONVERT_CURRENCY'] = "Y";
		}

		$this->IncludeComponentTemplate();

	}
?>

