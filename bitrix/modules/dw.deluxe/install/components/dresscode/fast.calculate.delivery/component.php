<?
	if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true){
		die();
	}

	if(empty($arParams["PRODUCT_ID"])){
		return false;
	}

	if(empty($arParams["LOAD_SCRIPT"])){
		$arParams["LOAD_SCRIPT"] = "Y";
	}

	if(empty($arParams["SITE_ID"])){
		$arParams["SITE_ID"] = SITE_ID;
	}

	//get product measure ratio
	$arResult["MEASURE_RATIO"] = $this->getMeasureRatio($arParams["PRODUCT_ID"]);
	if(empty($arParams["PRODUCT_QUANTITY"])){
		$arParams["PRODUCT_QUANTITY"] = $arResult["MEASURE_RATIO"];
	}
	
	//get delivery items
	$arResult["DELIVERY_ITEMS"] = $this->getCalculatedItems($arParams);

	//show template
	$this->IncludeComponentTemplate();	

?>