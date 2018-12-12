<?
	if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true)
		die();

	//globals
	global $CACHE_MANAGER;
	global $APPLICATION;
	global $USER;

	//params
	if(!isset($arParams["CACHE_TIME"])){
		$arParams["CACHE_TIME"] = 1285912;
	}

	if(empty($arParams["CACHE_TYPE"])){
		$arParams["CACHE_TYPE"] = "Y";
	}

	//big picture
	if(empty($arParams["BIG_PICTURE_WIDTH"])){
		$arParams["BIG_PICTURE_WIDTH"] = 1920;
	}

	if(empty($arParams["BIG_PICTURE_HEIGHT"])){
		$arParams["BIG_PICTURE_HEIGHT"] = 500;
	}

	//small picture
	if(empty($arParams["BIG_PICTURE_WIDTH"])){
		$arParams["SMALL_PICTURE_WIDTH"] = 600;
	}

	if(empty($arParams["BIG_PICTURE_HEIGHT"])){
		$arParams["SMALL_PICTURE_HEIGHT"] = 600;
	}

	//vars
	$arResult = array();
	$arCacheID = array();
	$arResult["PAGE_STORAGE"] = array();

	//cache id
	$arCacheID = array(
		"IBLOCK_ID" => $arParams["IBLOCK_ID"],
		"GROUPS" => $USER->GetGroups(),
		"SITE_ID" => SITE_ID
	);

	$cacheDir = "/";

	//extra settings from cache
	$obExtraCache = new CPHPCache();
	if($arParams["CACHE_TYPE"] != "N" && $obExtraCache->InitCache($arParams["CACHE_TIME"], serialize($arCacheID), $cacheDir)){
		//get info by cache
		$arResult = $obExtraCache->GetVars();
	}

	elseif($obExtraCache->StartDataCache()){

		//check modules
		if (CModule::IncludeModule("sale") &&
			CModule::IncludeModule("catalog") &&
			CModule::IncludeModule("iblock")
		){

			if(!empty($arParams["IBLOCK_ID"])){

				//prepare
				$arSelect = Array("ID", "NAME", "IBLOCK_ID", "IBLOCK_TYPE", "PREVIEW_TEXT", "DETAIL_TEXT", "DETAIL_PICTURE", "PREVIEW_PICTURE");
				$arFilter = Array("IBLOCK_ID" => $arParams["IBLOCK_ID"], "ACTIVE_DATE" => "Y", "ACTIVE" => "Y");
				$res = CIBlockElement::GetList(Array(), $arFilter, false, false, $arSelect);

				while($obElement = $res->GetNextElement()){

					//get data
					$arNextElement = $obElement->GetFields();
					$arNextElement["PROPERTIES"] = $obElement->GetProperties();

					if(!empty($arNextElement["PROPERTIES"]["LINK"]["VALUE"])){

						//storage links
						$arResult["PAGE_STORAGE"][$arNextElement["ID"]] = $arNextElement["PROPERTIES"]["LINK"]["VALUE"];

						//storage full info
						$arResult["PAGES"][$arNextElement["ID"]] = $arNextElement;

					}

				}

				//target cache
				$CACHE_MANAGER->StartTagCache($cacheDir);
				$CACHE_MANAGER->RegisterTag("iblock_id_".$arParams["IBLOCK_ID"]);
				$CACHE_MANAGER->EndTagCache();

				//save cache
				$obExtraCache->EndDataCache($arResult);

			}

			else{
				$obExtraCache->AbortDataCache();
			}

		}

		else{
			$obExtraCache->AbortDataCache();
		}

	}

	//current url
	$curPageUrl = $APPLICATION->GetCurPageParam("", array("clear_cache", "bitrix_include_areas", "set_filter", "show_page_exec_time", "show_include_exec_time", "show_sql_stat"), false);

	//search page
	$searchPage = array_search($curPageUrl, $arResult["PAGE_STORAGE"]);

	//set meta data
	if(!empty($searchPage) && !empty($arResult["PAGES"][$searchPage])){

		//get data
		$arPageData = $arResult["PAGES"][$searchPage];

		//get page params
		$pageBrowserTitle = !empty($arPageData["PROPERTIES"]["SEO_TITLE"]["VALUE"]) ? $arPageData["PROPERTIES"]["SEO_TITLE"]["VALUE"] : "";
		$pageTitle = !empty($arPageData["PROPERTIES"]["SEO_PAGE_TITLE"]["VALUE"]) ? $arPageData["PROPERTIES"]["SEO_PAGE_TITLE"]["VALUE"] : "";
		$pageMetaKeywords = !empty($arPageData["PROPERTIES"]["META_KEYWORDS"]["VALUE"]) ? $arPageData["PROPERTIES"]["META_KEYWORDS"]["VALUE"] : "";
		$pageMetaDescription = !empty($arPageData["PROPERTIES"]["META_DESCRIPTION"]["VALUE"]) ? $arPageData["PROPERTIES"]["META_DESCRIPTION"]["VALUE"] : "";
		$pageTopText = !empty($arPageData["~PREVIEW_TEXT"]) ? $arPageData["~PREVIEW_TEXT"] : "";
		$pageBottomText = !empty($arPageData["~DETAIL_TEXT"]) ? $arPageData["~DETAIL_TEXT"] : "";

		//title & properties params
		$arTitleOptions = array(
			"COMPONENT_NAME" => $this->getName()
		);

		//page title
		if(!empty($pageTitle)){
			$APPLICATION->SetTitle($pageTitle, $arTitleOptions);
		}

		//browser title
		if(!empty($pageBrowserTitle)){
			$APPLICATION->SetPageProperty("title", $pageBrowserTitle, $arTitleOptions);
		}

		//meta keywords
		if(!empty($pageMetaKeywords)){
			$APPLICATION->SetPageProperty("keywords", $pageMetaKeywords, $arTitleOptions);
		}
		
		//meta description
		if(!empty($pageMetaDescription)){
			$APPLICATION->SetPageProperty("description", $pageMetaDescription, $arTitleOptions);
		}

		//page text & banner

		//top text
		if(!empty($pageTopText)){
			$arResult["PAGE_TOP_TEXT"] = $pageTopText;
		}

		//bottom text
		if(!empty($pageBottomText)){
			$arResult["PAGE_BOTTOM_TEXT"] = $pageBottomText;
		}

		//banner
		if(!empty($arPageData["PREVIEW_PICTURE"])){

			//vars
			$arBanner = array();

			//resize pictures

			//full width
			$arBanner["BIG_PICTURE"] = $arPageData["PREVIEW_PICTURE"];

			//small
			if(!empty($arPageData["DETAIL_PICTURE"])){
				$arBanner["SMALL_PICTURE"] = $arPageData["DETAIL_PICTURE"];
			}

			//text
			if(!empty($arPageData["PROPERTIES"]["BANNER_TEXT"])){
				$arBanner["TEXT"] = $arPageData["PROPERTIES"]["BANNER_TEXT"];
			}

			//storage
			$arResult["BANNER"] = $arBanner;

		}

	}

	//show template
	$this->IncludeComponentTemplate();

?>