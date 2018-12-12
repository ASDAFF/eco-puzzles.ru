<?
	if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true){
		die();
	}

	use \Bitrix\Catalog;
	use	\Bitrix\Sale;

	$fuserId = CSaleBasket::GetBasketUserID();

	if(empty($arParams["ELEMENTS_COUNT"])){
		$arParams["ELEMENTS_COUNT"] = 10;
	}

	//create cache id
	$cacheID = array(
		"NAME" => "ELEMENT_VIEWED_PRODUCTS",
		"USER_GROUPS" => $USER->GetGroups(),
		"ELEMENTS_COUNT" => $arParams["ELEMENTS_COUNT"],
		"USER_ID" => $fuserId,
		"SITE_ID" => SITE_ID
	);


	if($this->StartResultCache($arParams["CACHE_TIME"], serialize($cacheID))){

		\Bitrix\Main\Loader::includeModule("catalog");

		$arResult["ITEMS"] = array();

		$filter = array(
			"=FUSER_ID" => $fuserId,
			"=SITE_ID" => SITE_ID
		);

		$viewedIterator = Catalog\CatalogViewedProductTable::getList(array(
			"select" => array("ELEMENT_ID"),
			"filter" => $filter,
			"order" => array("DATE_VISIT" => "DESC"),
			"limit" => $arParams["ELEMENTS_COUNT"]
		));

		while ($viewedProduct = $viewedIterator->fetch()){
			$arResult["ITEMS"][] = array(
				"ID" => $viewedProduct["ELEMENT_ID"]
			);
		}

		$this->setResultCacheKeys(array());
		$this->IncludeComponentTemplate();
	
	}

?>