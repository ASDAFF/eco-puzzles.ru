<?
	if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true)
		die();

	//d7
	use Bitrix\Sale\Location;
	use Bitrix\Sale\Location\Admin\LocationHelper;

	if(!CModule::IncludeModule("sale"))
		die();

	if(empty($_SESSION["USER_GEO_POSITION"])){
		$arParams["INCLUDE_YANDEX_API"] = true;
	}

	if(empty($arParams["CACHE_TIME"])){
		$arParams["CACHE_TIME"] = 1285192;
	}

	$cacheId = "site.sale.locations";
	$obCache = new CPHPCache();

	if($obCache->InitCache($arParams["CACHE_TIME"], $cacheId)){
	   $arResult = $obCache->GetVars();
	}

	elseif($obCache->StartDataCache()){

		$res = Location\DefaultSiteTable::getList(array(
			"filter" => array(
				"SITE_ID" => SITE_ID,
				"LOCATION.NAME.LANGUAGE_ID" => LANGUAGE_ID
			),
			"order" => array(
				"SORT" => "asc"
			),
			"select" => array(
				"CODE" => "LOCATION.CODE",
				"ID" => "LOCATION.ID",
				"PARENT_ID" => "LOCATION.PARENT_ID",
				"TYPE_ID" => "LOCATION.TYPE_ID",
				"LATITUDE" => "LOCATION.LATITUDE",
				"LONGITUDE" => "LOCATION.LONGITUDE",
				"NAME" => "LOCATION.NAME.NAME",
				"SHORT_NAME" => "LOCATION.NAME.SHORT_NAME",
				"LEFT_MARGIN" => "LOCATION.LEFT_MARGIN",
				"RIGHT_MARGIN" => "LOCATION.RIGHT_MARGIN"
			)
		));

		while($item = $res->Fetch()){
			$arResult["DEFAULT_LOCATIONS"][$item["ID"]] = $item;
		}

		$obCache->EndDataCache($arResult);

	}

	$this->IncludeComponentTemplate();

?>