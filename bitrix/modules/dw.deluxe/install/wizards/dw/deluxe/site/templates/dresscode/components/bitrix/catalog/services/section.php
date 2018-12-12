<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();?>
<?
	if (CModule::IncludeModule("iblock")){

		//global vars
		global $APPLICATION;

	  	//get section ID for smart filter
		$arFilter = array(
			"ACTIVE" => "Y",
			"GLOBAL_ACTIVE" => "Y",
			"IBLOCK_ID" => $arParams["IBLOCK_ID"],
		);

		if(!empty($arResult["VARIABLES"]["SECTION_ID"])){
			$arFilter["ID"] = $arResult["VARIABLES"]["SECTION_ID"];
		}

		elseif(!empty($arResult["VARIABLES"]["SECTION_CODE"])){
			$arFilter["=CODE"] = $arResult["VARIABLES"]["SECTION_CODE"];
		}

		//start cache
		$obCache = new CPHPCache();

		//get from cache
		if($obCache->InitCache(36000000, serialize($arFilter), "/")){
			$arCachedVars = $obCache->GetVars();
			$arResult["SECTION"] = $arCachedVars["SECTION"];
		}
		
		//no cache
		elseif($obCache->StartDataCache()){
			
			$arResult["SECTION"] = array();
			$dbRes = CIBlockSection::GetList(array(), $arFilter, false, array("ID", "IBLOCK_ID", "NAME", "PICTURE", "DETAIL_PICTURE", "UF_TEXT", "UF_SMALL_TEXT", "DESCRIPTION"));
			
			if($arResult["SECTION"] = $dbRes->GetNext()){

				if(defined("BX_COMP_MANAGED_CACHE")){
				
					global $CACHE_MANAGER;
					$CACHE_MANAGER->StartTagCache("/");
					$CACHE_MANAGER->RegisterTag("iblock_id_".$arParams["IBLOCK_ID"]);
					$CACHE_MANAGER->EndTagCache();

				}

				//get resized pictures
				$arResult["SECTION"]["RESIZE_DETAIL_PICTURE"] = CFile::ResizeImageGet($arResult["SECTION"]["DETAIL_PICTURE"], array("width" => 600, "height" => 600), BX_RESIZE_IMAGE_PROPORTIONAL, false, false, false, 100);
				$arResult["SECTION"]["RESIZE_PICTURE"] = CFile::ResizeImageGet($arResult["SECTION"]["PICTURE"], array("width" => 1920, "height" => 600), BX_RESIZE_IMAGE_PROPORTIONAL, false, false, false, 100);

			}

			else{
				if(!$arResult["SECTION"] = $dbRes->GetNext()){
					$arResult["SECTION"] = array();
				}
			}

			$obCache->EndDataCache(
				array(
					"SECTION" => $arResult["SECTION"],
				)
			);

		}
	}


?>

<div class="banner-animated fullscreen-banner banner-elem <?if(empty($arResult["SECTION"]["RESIZE_PICTURE"])):?> banner-no-bg<?endif;?>"<?if(!empty($arResult["SECTION"]["RESIZE_PICTURE"])):?> style="background: url('<?=$arResult["SECTION"]["RESIZE_PICTURE"]["src"]?>') center center / cover no-repeat;"<?endif;?>>
	<div class="tb">
		<div class="text-wrap tc">
			<h1 class="ff-medium"><?=$arResult["SECTION"]["NAME"]?></h1>
			<?if(!empty($arResult["SECTION"]["UF_SMALL_TEXT"])):?>
				<div class="price theme-color ff-medium"><?=$arResult["SECTION"]["UF_SMALL_TEXT"]?></div>
			<?endif;?>
			<?if(!empty($arResult["SECTION"]["UF_TEXT"])):?>
				<div class="descr"><?=$arResult["SECTION"]["UF_TEXT"]?></div>
			<?endif;?>
			<a class="btn-simple" href="<?=SITE_DIR?>callback/"><?=GetMessage("SERVICES_CALLBACK")?></a>
		</div>
		<?if(!empty($arResult["SECTION"]["RESIZE_DETAIL_PICTURE"])):?>
			<div class="image tc">
				<img src="<?=$arResult["SECTION"]["RESIZE_DETAIL_PICTURE"]["src"]?>" alt="<?=$arResult["SECTION"]["NAME"]?>">
			</div>
		<?endif;?>
	</div>
</div>

<div class="global-block-container">
	<div class="global-content-block">
		<?if(!empty($arResult["SECTION"]["DESCRIPTION"])):?>
			<div class="detail-text-wrap">
				<div class="h2 ff-medium"><?=GetMessage("SERVICES_MORE_HEADING")?></div>
				<?=$arResult["SECTION"]["DESCRIPTION"]?>
			</div>
		<?endif;?>
		<?$APPLICATION->IncludeComponent(
			"dresscode:catalog.section",
			"services",
			array(
				"IBLOCK_TYPE" => $arParams["IBLOCK_TYPE"],
				"IBLOCK_ID" => $arParams["IBLOCK_ID"],
				"ELEMENT_SORT_FIELD" => $arParams["ELEMENT_SORT_FIELD"],
				"ELEMENT_SORT_ORDER" => $arParams["ELEMENT_SORT_ORDER"] ,
				"ELEMENT_SORT_FIELD2" => $arParams["ELEMENT_SORT_FIELD2"],
				"ELEMENT_SORT_ORDER2" => $arParams["ELEMENT_SORT_ORDER2"],
				"PROPERTY_CODE" => $arParams["LIST_PROPERTY_CODE"],
				"META_KEYWORDS" => $arParams["LIST_META_KEYWORDS"],
				"META_DESCRIPTION" => $arParams["LIST_META_DESCRIPTION"],
				"BROWSER_TITLE" => $arParams["LIST_BROWSER_TITLE"],
				"INCLUDE_SUBSECTIONS" => $arParams["INCLUDE_SUBSECTIONS"],
				"BASKET_URL" => $arParams["BASKET_URL"],
				"ACTION_VARIABLE" => $arParams["ACTION_VARIABLE"],
				"PRODUCT_ID_VARIABLE" => $arParams["PRODUCT_ID_VARIABLE"],
				"SECTION_ID_VARIABLE" => $arParams["SECTION_ID_VARIABLE"],
				"PRODUCT_QUANTITY_VARIABLE" => $arParams["PRODUCT_QUANTITY_VARIABLE"],
				"PRODUCT_PROPS_VARIABLE" => $arParams["PRODUCT_PROPS_VARIABLE"],
				"FILTER_NAME" => $arParams["FILTER_NAME"],
				"CACHE_TYPE" => $arParams["CACHE_TYPE"],
				"CACHE_TIME" => $arParams["CACHE_TIME"],
				"CACHE_FILTER" => $arParams["CACHE_FILTER"],
				"CACHE_GROUPS" => $arParams["CACHE_GROUPS"],
				"SET_TITLE" => $arParams["SET_TITLE"],
				"SET_STATUS_404" => $arParams["SET_STATUS_404"],
				"DISPLAY_COMPARE" => $arParams["USE_COMPARE"],
				"PAGE_ELEMENT_COUNT" => $arParams["PAGE_ELEMENT_COUNT"],
				"LINE_ELEMENT_COUNT" => $arParams["LINE_ELEMENT_COUNT"],
				"PRICE_CODE" => $arParams["PRICE_CODE"],
				"USE_PRICE_COUNT" => $arParams["USE_PRICE_COUNT"],
				"SHOW_PRICE_COUNT" => $arParams["SHOW_PRICE_COUNT"],
				"HIDE_MEASURES" => $arParams["HIDE_MEASURES"],
				"PRICE_VAT_INCLUDE" => $arParams["PRICE_VAT_INCLUDE"],
				"USE_PRODUCT_QUANTITY" => $arParams['USE_PRODUCT_QUANTITY'],
				"ADD_PROPERTIES_TO_BASKET" => (isset($arParams["ADD_PROPERTIES_TO_BASKET"]) ? $arParams["ADD_PROPERTIES_TO_BASKET"] : ''),
				"PARTIAL_PRODUCT_PROPERTIES" => (isset($arParams["PARTIAL_PRODUCT_PROPERTIES"]) ? $arParams["PARTIAL_PRODUCT_PROPERTIES"] : ''),
				"PRODUCT_PROPERTIES" => $arParams["PRODUCT_PROPERTIES"],
				"SHOW_SECTION_BANNER" => !empty($arParams["SHOW_SECTION_BANNER"]) ? $arParams["SHOW_SECTION_BANNER"] : "Y",
				"DISPLAY_TOP_PAGER" => $arParams["DISPLAY_TOP_PAGER"],
				"DISPLAY_BOTTOM_PAGER" => $arParams["DISPLAY_BOTTOM_PAGER"],
				"PAGER_TITLE" => $arParams["PAGER_TITLE"],
				"PAGER_SHOW_ALWAYS" => $arParams["PAGER_SHOW_ALWAYS"],
				"PAGER_TEMPLATE" => $arParams["PAGER_TEMPLATE"],
				"PAGER_DESC_NUMBERING" => $arParams["PAGER_DESC_NUMBERING"],
				"PAGER_DESC_NUMBERING_CACHE_TIME" => $arParams["PAGER_DESC_NUMBERING_CACHE_TIME"],
				"PAGER_SHOW_ALL" => $arParams["PAGER_SHOW_ALL"],

				"OFFERS_CART_PROPERTIES" => $arParams["OFFERS_CART_PROPERTIES"],
				"OFFERS_FIELD_CODE" => $arParams["LIST_OFFERS_FIELD_CODE"],
				"OFFERS_PROPERTY_CODE" => $arParams["LIST_OFFERS_PROPERTY_CODE"],
				"OFFERS_SORT_FIELD" => $arParams["OFFERS_SORT_FIELD"],
				"OFFERS_SORT_ORDER" => $arParams["OFFERS_SORT_ORDER"],
				"OFFERS_SORT_FIELD2" => $arParams["OFFERS_SORT_FIELD2"],
				"OFFERS_SORT_ORDER2" => $arParams["OFFERS_SORT_ORDER2"],
				"OFFERS_LIMIT" => $arParams["LIST_OFFERS_LIMIT"],

				"SECTION_ID" => $arResult["VARIABLES"]["SECTION_ID"],
				"SECTION_CODE" => $arResult["VARIABLES"]["SECTION_CODE"],
				"SECTION_URL" => $arResult["FOLDER"].$arResult["URL_TEMPLATES"]["section"],
				"DETAIL_URL" => $arResult["FOLDER"].$arResult["URL_TEMPLATES"]["element"],
				'CONVERT_CURRENCY' => $arParams['CONVERT_CURRENCY'],
				'CURRENCY_ID' => $arParams['CURRENCY_ID'],
				'HIDE_NOT_AVAILABLE' => $arParams["HIDE_NOT_AVAILABLE"],

				'LABEL_PROP' => $arParams['LABEL_PROP'],
				'ADD_PICT_PROP' => $arParams['ADD_PICT_PROP'],
				'PRODUCT_DISPLAY_MODE' => $arParams['PRODUCT_DISPLAY_MODE'],

				'OFFER_ADD_PICT_PROP' => $arParams['OFFER_ADD_PICT_PROP'],
				'OFFER_TREE_PROPS' => $arParams['OFFER_TREE_PROPS'],
				'PRODUCT_SUBSCRIPTION' => $arParams['PRODUCT_SUBSCRIPTION'],
				'SHOW_DISCOUNT_PERCENT' => $arParams['SHOW_DISCOUNT_PERCENT'],
				'SHOW_OLD_PRICE' => $arParams['SHOW_OLD_PRICE'],
				'MESS_BTN_BUY' => $arParams['MESS_BTN_BUY'],
				'MESS_BTN_ADD_TO_BASKET' => $arParams['MESS_BTN_ADD_TO_BASKET'],
				'MESS_BTN_SUBSCRIBE' => $arParams['MESS_BTN_SUBSCRIBE'],
				'MESS_BTN_DETAIL' => $arParams['MESS_BTN_DETAIL'],
				'MESS_NOT_AVAILABLE' => $arParams['MESS_NOT_AVAILABLE'],

				"USE_MAIN_ELEMENT_SECTION" => $arParams["USE_MAIN_ELEMENT_SECTION"],
				'HIDE_NOT_AVAILABLE_OFFERS' => $arParams["HIDE_NOT_AVAILABLE_OFFERS"],

				'TEMPLATE_THEME' => (isset($arParams['TEMPLATE_THEME']) ? $arParams['TEMPLATE_THEME'] : ''),
				"ADD_SECTIONS_CHAIN" => "Y"
			),
			$component
		);?>
	</div>
	<div class="global-information-block">
		<?$APPLICATION->IncludeComponent(
			"bitrix:main.include", 
			".default", 
			array(
				"COMPONENT_TEMPLATE" => ".default",
				"AREA_FILE_SHOW" => "sect",
				"AREA_FILE_SUFFIX" => "information_block",
				"AREA_FILE_RECURSIVE" => "Y",
				"EDIT_TEMPLATE" => ""
			),
			false
		);?>
	</div>
</div>