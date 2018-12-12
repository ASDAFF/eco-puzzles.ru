<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();
$this->setFrameMode(true);?>
<?
	if(CModule::IncludeModule("iblock") && CModule::IncludeModule("catalog")){

	//global vars
	global $APPLICATION;

	$arSortFields = array(
		"SHOWS" => array(
			"ORDER"=> "DESC",
			"CODE" => "SHOWS",
			"NAME" => GetMessage("CATALOG_SORT_FIELD_SHOWS")
		),
		"NAME" => array( // параметр в url
			"ORDER"=> "ASC", //в возрастающем порядке
			"CODE" => "NAME", // Код поля для сортировки
			"NAME" => GetMessage("CATALOG_SORT_FIELD_NAME") // имя для вывода в публичной части, редактировать в (/lang/ru/section.php)
		),
		"PRICE_ASC"=> array(
			"ORDER"=> "ASC",
			"CODE" => "PROPERTY_MINIMUM_PRICE",  // изменен для сортировки по ТП
			"NAME" => GetMessage("CATALOG_SORT_FIELD_PRICE_ASC")
		),
		"PRICE_DESC" => array(
			"ORDER"=> "DESC",
			"CODE" => "PROPERTY_MAXIMUM_PRICE", // изменен для сортировки по ТП
			"NAME" => GetMessage("CATALOG_SORT_FIELD_PRICE_DESC")
		)
	);

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
		$arCurSection = $arCachedVars["SECTION"];
		$arResult["PRICE_SORT_FROM_PROPERTY"] = $arCachedVars["PRICE_SORT_FROM_PROPERTY"];
		$arResult["IPROPERTY_VALUES"] = $arCachedVars["IPROPERTY_VALUES"];
		$arResult["SECTION_BANNERS"] = $arCachedVars["SECTION_BANNERS"];
		$arResult["BASE_PRICE"] = $arCachedVars["BASE_PRICE"];
	}
	
	//no cache
	elseif($obCache->StartDataCache()){
		
		$arCurSection = array();
		$dbRes = CIBlockSection::GetList(array(), $arFilter, false, array("ID", "IBLOCK_ID"));
		
		if($arCurSection = $dbRes->GetNext()){

			if(defined("BX_COMP_MANAGED_CACHE")){
			
				global $CACHE_MANAGER;
				$CACHE_MANAGER->StartTagCache("/");
				$CACHE_MANAGER->RegisterTag("iblock_id_".$arParams["IBLOCK_ID"]);
				$CACHE_MANAGER->EndTagCache();

			}

		}

		else{
			if(!$arCurSection = $dbRes->GetNext()){
				$arCurSection = array();
			}
		}

		$ipropValues = new \Bitrix\Iblock\InheritedProperty\SectionValues($arCurSection["IBLOCK_ID"], $arCurSection["ID"]);
		$arResult["IPROPERTY_VALUES"] = $ipropValues->getValues();
		$arResult["BASE_PRICE"] = CCatalogGroup::GetBaseGroup();

		//check for available min_price and max_price property
		$rsMinPriceProperty = CIBlock::GetProperties($arParams["IBLOCK_ID"], Array(), Array("CODE" => "MINIMUM_PRICE"));
		$arResult["PRICE_SORT_FROM_PROPERTY"] = $rsMinPriceProperty->SelectedRowsCount() == 1 ? "Y" : "N";

		// get section banner
		$arResult["SECTION_BANNERS"] = array();
		if(empty($arParams["SHOW_SECTION_BANNER"]) || !empty($arParams["SHOW_SECTION_BANNER"]) && $arParams["SHOW_SECTION_BANNER"] == "Y"){
			if(!empty($arResult["VARIABLES"]["SECTION_ID"])){
				$arSectionID = array();
				$navChain = CIBlockSection::GetNavChain($arParams["IBLOCK_ID"], $arResult["VARIABLES"]["SECTION_ID"]);
				while($arNextSection = $navChain->GetNext()){
					$arSectionID[$arNextSection["ID"]] = $arNextSection["ID"];
				}
				if(!empty($arSectionID)){
					$rsSection = CIBlockSection::GetList(array("DEPTH_LEVEL" => "DESC"), array("IBLOCK_ID" => $arParams["IBLOCK_ID"], "ID" => $arSectionID, "ACTIVE" => "Y", "GLOBAL_ACTIVE" => "Y"), false, array("ID", "IBLOCK_ID", "UF_BANNER", "UF_BANNER_LINK"));
					while($arSection = $rsSection->GetNext()){
						if(!empty($arSection["UF_BANNER"])){
							foreach ($arSection["UF_BANNER"] as $ib => $bannerID){
								$arResult["SECTION_BANNERS"][$ib]["IMAGE"] = CFile::ResizeImageGet($bannerID, array("width" => 2560, "height" => 1440), BX_RESIZE_IMAGE_PROPORTIONAL, true);
								if(!empty($arSection["UF_BANNER_LINK"][$ib])){
									$arResult["SECTION_BANNERS"][$ib]["LINK"] = $arSection["UF_BANNER_LINK"][$ib];
								}
							}
							break(1);
						}
					}
				}
			}
		}

		$obCache->EndDataCache(
			array(
				"SECTION" => $arCurSection,
				"BASE_PRICE" => $arResult["BASE_PRICE"],
				"SECTION_BANNERS" => $arResult["SECTION_BANNERS"],
				"IPROPERTY_VALUES" => $arResult["IPROPERTY_VALUES"],
				"PRICE_SORT_FROM_PROPERTY" => $arResult["PRICE_SORT_FROM_PROPERTY"]
			)
		);

	}

	if($arResult["PRICE_SORT_FROM_PROPERTY"] == "N"){
		$arSortFields["PRICE_ASC"] = array(
			"ORDER"=> "ASC",
			"CODE" => "CATALOG_PRICE_".$arResult["BASE_PRICE"]["ID"],
			"NAME" => GetMessage("CATALOG_SORT_FIELD_PRICE_ASC")
		);
		$arSortFields["PRICE_DESC"] = array(
			"ORDER"=> "DESC",
			"CODE" => "CATALOG_PRICE_".$arResult["BASE_PRICE"]["ID"],
			"NAME" => GetMessage("CATALOG_SORT_FIELD_PRICE_DESC")
		);
	}

?>
<h1><?if(!empty($arResult["IPROPERTY_VALUES"]["SECTION_PAGE_TITLE"])):?><?=$arResult["IPROPERTY_VALUES"]["SECTION_PAGE_TITLE"]?><?else:?><?=$APPLICATION->ShowTitle(false)?><?endif;?></h1>
<?if(!empty($arResult["SECTION_BANNERS"])):?>
	<div id="catalog-section-banners">
		<ul class="slideBox">
			<?foreach ($arResult["SECTION_BANNERS"] as $isc => $arNextBanner):?>
				<?if(!empty($arNextBanner["IMAGE"])):?>
					<li><a<?if(!empty($arNextBanner["LINK"]) && filter_var($arNextBanner["LINK"], FILTER_VALIDATE_URL)):?> href="<?=$arNextBanner["LINK"]?>"<?endif;?>><img src="<?=$arNextBanner["IMAGE"]["src"]?>"></a></li>
				<?endif;?>
			<?endforeach;?>
		</ul>
		<a href="#" class="catalog-section-banners-btn-left"></a>
		<a href="#" class="catalog-section-banners-btn-right"></a>
		<script>
			$(function() {
				$("#catalog-section-banners").dwSlider({
					rightButton: ".catalog-section-banners-btn-right",
					leftButton: ".catalog-section-banners-btn-left",
					delay: 6000,
					speed: 1000
				});
			});
		</script>
	</div>
<?endif;?>
<div id="catalogColumn">
	<div class="leftColumn">
			<?$APPLICATION->IncludeComponent(
				"bitrix:catalog.section.list",
				"level2",
				array(
					"IBLOCK_TYPE" => $arParams["IBLOCK_TYPE"],
					"IBLOCK_ID" => $arParams["IBLOCK_ID"],
					"SECTION_ID" => $arResult["VARIABLES"]["SECTION_ID"],
					"SECTION_CODE" => $arResult["VARIABLES"]["SECTION_CODE"],
					"CACHE_TYPE" => $arParams["CACHE_TYPE"],
					"CACHE_TIME" => $arParams["CACHE_TIME"],
					"CACHE_GROUPS" => $arParams["CACHE_GROUPS"],
					"COUNT_ELEMENTS" => $arParams["SECTION_COUNT_ELEMENTS"],
					"TOP_DEPTH" => 1,
					"SECTION_URL" => $arResult["FOLDER"].$arResult["URL_TEMPLATES"]["section"],
					"VIEW_MODE" => $arParams["SECTIONS_VIEW_MODE"],
					"SHOW_PARENT_NAME" => $arParams["SECTIONS_SHOW_PARENT_NAME"],
					"HIDE_SECTION_NAME" => (isset($arParams["SECTIONS_HIDE_SECTION_NAME"]) ? $arParams["SECTIONS_HIDE_SECTION_NAME"] : "N"),
					"ADD_SECTIONS_CHAIN" => (isset($arParams["ADD_SECTIONS_CHAIN"]) ? $arParams["ADD_SECTIONS_CHAIN"] : '')
				),
				$component
			);?>
			<?$APPLICATION->IncludeComponent(
				"bitrix:catalog.smart.filter",
				".default",
				array(
					"IBLOCK_TYPE" => $arParams["IBLOCK_TYPE"],
					"IBLOCK_ID" => $arParams["IBLOCK_ID"],
					"SECTION_ID" => $arCurSection["ID"],
					"FILTER_NAME" => $arParams["FILTER_NAME"],
					"PRICE_CODE" => $arParams["FILTER_PRICE_CODE"],
					"CACHE_TYPE" => "A",
					"CACHE_TIME" => $arParams["CACHE_TIME"],
					"CACHE_GROUPS" => $arParams["CACHE_GROUPS"],
					"SAVE_IN_SESSION" => "N",
					"FILTER_VIEW_MODE" => $arParams["FILTER_VIEW_MODE"],
					"XML_EXPORT" => "Y",
					"SECTION_TITLE" => "NAME",
					"SECTION_DESCRIPTION" => "DESCRIPTION",
					"HIDE_NOT_AVAILABLE" => $arParams["HIDE_NOT_AVAILABLE"],
					"TEMPLATE_THEME" => $arParams["TEMPLATE_THEME"],
					"CONVERT_CURRENCY" => $arParams["CONVERT_CURRENCY"],
					"CURRENCY_ID" =>  $arParams["CURRENCY_ID"],
					"SEF_MODE" => "Y",
					"SEF_RULE" => $arResult["FOLDER"].$arResult["URL_TEMPLATES"]["smart_filter"],
					"SMART_FILTER_PATH" => $arResult["VARIABLES"]["SMART_FILTER_PATH"],
					"PAGER_PARAMS_NAME" => $arParams["PAGER_PARAMS_NAME"],
					"COMPONENT_TEMPLATE" => ".default",
					"SECTION_CODE" => "",
					"SECTION_CODE_PATH" => ""
				),
				false
			);?>
		<?
		}
		?>
	</div>
	<div class="rightColumn">
		<?if(!empty($_REQUEST["SORT_FIELD"]) && !empty($arSortFields[$_REQUEST["SORT_FIELD"]])){

			setcookie("CATALOG_SORT_FIELD", $_REQUEST["SORT_FIELD"], time() + 60 * 60 * 24 * 30 * 12 * 2, "/");

			$arParams["ELEMENT_SORT_FIELD"] = $arSortFields[$_REQUEST["SORT_FIELD"]]["CODE"];
			$arParams["ELEMENT_SORT_ORDER"] = $arSortFields[$_REQUEST["SORT_FIELD"]]["ORDER"];	

			$arSortFields[$_REQUEST["SORT_FIELD"]]["SELECTED"] = "Y";

		}elseif(!empty($_COOKIE["CATALOG_SORT_FIELD"]) && !empty($arSortFields[$_COOKIE["CATALOG_SORT_FIELD"]])){ // COOKIE
			
			$arParams["ELEMENT_SORT_FIELD"] = $arSortFields[$_COOKIE["CATALOG_SORT_FIELD"]]["CODE"];
			$arParams["ELEMENT_SORT_ORDER"] = $arSortFields[$_COOKIE["CATALOG_SORT_FIELD"]]["ORDER"];
			
			$arSortFields[$_COOKIE["CATALOG_SORT_FIELD"]]["SELECTED"] = "Y";
		}
		?>

		<?$arSortProductNumber = array(
			30 => array("NAME" => 30), 
			60 => array("NAME" => 60), 
			90 => array("NAME" => 90)
		);?>

		<?if(!empty($_REQUEST["SORT_TO"]) && $arSortProductNumber[$_REQUEST["SORT_TO"]]){
			setcookie("CATALOG_SORT_TO", $_REQUEST["SORT_TO"], time() + 60 * 60 * 24 * 30 * 12 * 2, "/");
			$arSortProductNumber[$_REQUEST["SORT_TO"]]["SELECTED"] = "Y";
			$arParams["PAGE_ELEMENT_COUNT"] = $_REQUEST["SORT_TO"];
		}elseif (!empty($_COOKIE["CATALOG_SORT_TO"]) && $arSortProductNumber[$_COOKIE["CATALOG_SORT_TO"]]){
			$arSortProductNumber[$_COOKIE["CATALOG_SORT_TO"]]["SELECTED"] = "Y";
			$arParams["PAGE_ELEMENT_COUNT"] = $_COOKIE["CATALOG_SORT_TO"];
		}?>

		<?$arTemplates = array(
			"SQUARES" => array(
				"CLASS" => "squares"
			),
			"LINE" => array(
				"CLASS" => "line"
			),
			"TABLE" => array(
				"CLASS" => "table"
			)	
		);?>

		<?if(!empty($_REQUEST["VIEW"]) && $arTemplates[$_REQUEST["VIEW"]]){
			setcookie("CATALOG_VIEW", $_REQUEST["VIEW"], time() + 60 * 60 * 24 * 30 * 12 * 2);
			$arTemplates[$_REQUEST["VIEW"]]["SELECTED"] = "Y";
			$arParams["CATALOG_TEMPLATE"] = $_REQUEST["VIEW"];
		}elseif (!empty($_COOKIE["CATALOG_VIEW"]) && $arTemplates[$_COOKIE["CATALOG_VIEW"]]){
			$arTemplates[$_COOKIE["CATALOG_VIEW"]]["SELECTED"] = "Y";
			$arParams["CATALOG_TEMPLATE"] = $_COOKIE["CATALOG_VIEW"];
		}else{
			$arTemplates[key($arTemplates)]["SELECTED"] = "Y";
		}
		?>
		<?$APPLICATION->IncludeComponent(
			"dresscode:slider",
			"middle",
			array(
				"IBLOCK_TYPE" => "sliders",
				"IBLOCK_ID" => "27",
				"CACHE_TYPE" => "Y",
				"CACHE_TIME" => "3600000",
				"PICTURE_WIDTH" => "1476",
				"PICTURE_HEIGHT" => "202",
				"COMPONENT_TEMPLATE" => "middle"
			),
			false
		);?>
		<div id="catalog">
			<?$APPLICATION->IncludeComponent(
				"bitrix:catalog.section.list",
				"catalog-pictures",
				array(
					"IBLOCK_TYPE" => $arParams["IBLOCK_TYPE"],
					"IBLOCK_ID" => $arParams["IBLOCK_ID"],
					"SECTION_ID" => $arResult["VARIABLES"]["SECTION_ID"],
					"SECTION_CODE" => $arResult["VARIABLES"]["SECTION_CODE"],
					"CACHE_TYPE" => $arParams["CACHE_TYPE"],
					"CACHE_TIME" => $arParams["CACHE_TIME"],
					"CACHE_GROUPS" => $arParams["CACHE_GROUPS"],
					"COUNT_ELEMENTS" => $arParams["SECTION_COUNT_ELEMENTS"],
					"TOP_DEPTH" => 1,
					"SECTION_URL" => $arResult["FOLDER"].$arResult["URL_TEMPLATES"]["section"],
					"VIEW_MODE" => $arParams["SECTIONS_VIEW_MODE"],
					"SHOW_PARENT_NAME" => $arParams["SECTIONS_SHOW_PARENT_NAME"],
					"HIDE_SECTION_NAME" => (isset($arParams["SECTIONS_HIDE_SECTION_NAME"]) ? $arParams["SECTIONS_HIDE_SECTION_NAME"] : "N"),
					"ADD_SECTIONS_CHAIN" => "N"
				),
				$component
			);?>
			<div id="catalogLine">
				<div class="column oFilter">
					<a href="#" class="oSmartFilter btn-simple btn-micro"><span class="ico"></span><?=GetMessage("CATALOG_FILTER")?></a>
				</div>
				<?if(!empty($arSortFields)):?>
					<div class="column">
						<div class="label">
							<?=GetMessage("CATALOG_SORT_LABEL");?>
						</div>
						<select name="sortFields" id="selectSortParams">
							<?foreach ($arSortFields as $arSortFieldCode => $arSortField):?>
								<option value="<?=$APPLICATION->GetCurPageParam("SORT_FIELD=".$arSortFieldCode, array("SORT_FIELD"));?>"<?if($arSortField["SELECTED"] == "Y"):?> selected<?endif;?>><?=$arSortField["NAME"]?></option>
							<?endforeach;?>
						</select>
					</div>
				<?endif;?>
				<?if(!empty($arSortProductNumber)):?>
					<div class="column">
						<div class="label">
							<?=GetMessage("CATALOG_SORT_TO_LABEL");?>
						</div>
						<select name="countElements" id="selectCountElements">
							<?foreach ($arSortProductNumber as $arSortNumberElementId => $arSortNumberElement):?>
								<option value="<?=$APPLICATION->GetCurPageParam("SORT_TO=".$arSortNumberElementId, array("SORT_TO"));?>"<?if($arSortNumberElement["SELECTED"] == "Y"):?> selected<?endif;?>><?=$arSortNumberElement["NAME"]?></option>
							<?endforeach;?>
						</select>
					</div>
				<?endif;?>
				<?if(!empty($arTemplates)):?>
					<div class="column">
						<div class="label">
							<?=GetMessage("CATALOG_VIEW_LABEL");?>
						</div>
						<div class="viewList">
							<?foreach ($arTemplates as $arTemplatesCode => $arNextTemplate):?>
								<div class="element"><a<?if($arNextTemplate["SELECTED"] != "Y"):?> href="<?=$APPLICATION->GetCurPageParam("VIEW=".$arTemplatesCode, array("VIEW"));?>"<?endif;?> class="<?=$arNextTemplate["CLASS"]?><?if($arNextTemplate["SELECTED"] == "Y"):?> selected<?endif;?>"></a></div>
							<?endforeach;?>
						</div>
					</div>
				<?endif;?>
			</div>
			<?reset($arTemplates);?>
			<?$APPLICATION->IncludeComponent(
				"dresscode:catalog.section",
				 !empty($arParams["CATALOG_TEMPLATE"]) ? strtolower($arParams["CATALOG_TEMPLATE"]) : strtolower(key($arTemplates)),
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
					"ENABLED_SKU_FILTER" => "Y",
					"ADD_SECTIONS_CHAIN" => "N"
				),
				$component
			);?>
		</div>
	</div>
</div>