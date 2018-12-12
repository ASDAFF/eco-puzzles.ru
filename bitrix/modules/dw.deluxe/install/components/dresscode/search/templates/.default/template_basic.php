 <?if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true) die();?>

	<?
		$this->setFrameMode(false);
	?>

	<?if(count($arResult["ITEMS"]) > 1):?>
		<?$this->SetViewTarget("menuRollClass");?> menuRolled<?$this->EndViewTarget();?>
		<?$this->SetViewTarget("hiddenZoneClass");?> hiddenZone<?$this->EndViewTarget();?>
	<?endif;?>

	<?
		$this->SetViewTarget("smartFilter");
    ?>

	<?if(!empty($arResult["MENU_SECTIONS"]) && count($arResult["MENU_SECTIONS"]) > 1):?>
		<div id="nextSection">
			<span class="title"><?=GetMessage("SELECT_CATEGORY");?></span>
			<ul>
				<?foreach ($arResult["MENU_SECTIONS"] as $ic => $arSection):?>
					<li><a<?if($arSection["SELECTED"] != "Y"):?> href="<?=$arSection["FILTER_LINK"]?>"<?endif;?><?if($arSection["SELECTED"] == "Y"):?> class="selected"<?endif;?>><?=$arSection["NAME"]?> (<?=$arSection["ELEMENTS_COUNT"]?>)</a></li>
				<?endforeach;?>
			</ul>
		</div>
	<?endif;?>

	<?if(count($arResult["ITEMS"]) > 1):?>
		<?$APPLICATION->IncludeComponent(
			"dresscode:search.smart.filter", 
			"", 
			array(
				"IBLOCK_TYPE" => $arParams["IBLOCK_TYPE"],
				"IBLOCK_ID" => $arParams["IBLOCK_ID"],
				"SECTION_ID" => $_REQUEST["SECTION_ID"],
				"FILTER_NAME" => "arrFilter",
				"HIDE_NOT_AVAILABLE" => $arParams["HIDE_NOT_AVAILABLE"],
				"SHOW_ALL_WO_SECTION" => "Y",
				"CACHE_TYPE" => "Y",
				"CACHE_TIME" => "36000000",
				"CACHE_GROUPS" => "Y",
				"SAVE_IN_SESSION" => "N",
				"INSTANT_RELOAD" => "N",
				"PRICE_CODE" => $arParams["FILTER_PRICE_CODE"],
				"XML_EXPORT" => "N",
				"SECTION_TITLE" => "-",
				"SECTION_DESCRIPTION" => "-",
				'CONVERT_CURRENCY' => $arParams["CONVERT_CURRENCY"],
				'CURRENCY_ID' => $arParams['CURRENCY_ID']
			),
			false
		);?>
	<?endif;?>
	<?
		$this->EndViewTarget();
	?>
	
	<h1><?=GetMessage("SEARCH_PAGE")?></h1>

	<?if(!empty($arResult["ITEMS"])):?>

		<?$BASE_PRICE = CCatalogGroup::GetBaseGroup();?>
		<?$arSortFields = array(
			"SHOWS" => array(
				"ORDER"=> "DESC",
				"CODE" => "SHOWS",
				"NAME" => GetMessage("CATALOG_SORT_FIELD_SHOWS")
			),	
			"NAME" => array(
				"ORDER"=> "ASC",
				"CODE" => "NAME",
				"NAME" => GetMessage("CATALOG_SORT_FIELD_NAME")
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
		);?>

		<?
			$rsMinPriceProperty = CIBlock::GetProperties($arParams["IBLOCK_ID"], Array(), Array("CODE" => "MINIMUM_PRICE"));
			if($rsMinPriceProperty->SelectedRowsCount() != 1){
				$arSortFields["PRICE_ASC"] = array(
					"ORDER"=> "ASC",
					"CODE" => "CATALOG_PRICE_".$BASE_PRICE["ID"],
					"NAME" => GetMessage("CATALOG_SORT_FIELD_PRICE_ASC")
				);
				$arSortFields["PRICE_DESC"] = array(
					"ORDER"=> "DESC",
					"CODE" => "CATALOG_PRICE_".$BASE_PRICE["ID"],
					"NAME" => GetMessage("CATALOG_SORT_FIELD_PRICE_DESC")
				);
			}
		?>

		<?if(!empty($_REQUEST["SORT_FIELD"]) && !empty($arSortFields[$_REQUEST["SORT_FIELD"]])){

			setcookie("CATALOG_SORT_FIELD", $_REQUEST["SORT_FIELD"], time() + 60 * 60 * 24 * 30 * 12 * 2, "/");

			$arParams["ELEMENT_SORT_FIELD"] = $arSortFields[$_REQUEST["SORT_FIELD"]]["CODE"];
			$arParams["ELEMENT_SORT_ORDER"] = $arSortFields[$_REQUEST["SORT_FIELD"]]["ORDER"];	

			$arSortFields[$_REQUEST["SORT_FIELD"]]["SELECTED"] = "Y";

		}elseif(!empty($_COOKIE["CATALOG_SORT_FIELD"]) && !empty($arSortFields[$_COOKIE["CATALOG_SORT_FIELD"]])){ // COOKIE
			
			$arParams["ELEMENT_SORT_FIELD"] = $arSortFields[$_COOKIE["CATALOG_SORT_FIELD"]]["CODE"];
			$arParams["ELEMENT_SORT_ORDER"] = $arSortFields[$_COOKIE["ORDER"]];
			
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
		<div id="catalogLine">
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
	<?endif;?>

<?if(!empty($arResult["SECTIONS"])):?>
	<h2><?=GetMessage("SECTION_FOR_SEARCH")?> &laquo;<?=$arResult["QUERY"]?>&raquo;</h2>
	<ul id="searchSection">
		<?foreach ($arResult["SECTIONS"] as $i => $arSection):?>
			<li><a href="<?=$arSection["SECTION_PAGE_URL"]?>"><?=$arSection["NAME"]?></a></li>
		<?endforeach;?>
	</ul>
<?endif;?>

<?if(!empty($arResult["ITEMS"])):?>
	<h3><?=GetMessage("PRODUCT_FOR_SEARCH")?> &laquo;<?=$arResult["QUERY"]?>&raquo;</h3>
	<?if(!empty($arResult["QUERY_REPLACE"])):?>
		<p><?=GetMessage("ERROR_IN_QUERTY")?></p>
	<?endif;?>
	<?
		global $arrFilter;
		unset($arrFilter["FACET_OPTIONS"]);
	?>
	<div id="catalog">
		<?$arrFilter["FACET_OPTIONS"] = array();?>
		<?reset($arTemplates);?>
		<?$APPLICATION->IncludeComponent(
			"dresscode:catalog.section", 
			!empty($arParams["CATALOG_TEMPLATE"]) ? strtolower($arParams["CATALOG_TEMPLATE"]) : strtolower(key($arTemplates)),
			array(
				"IBLOCK_TYPE" => $arParams["IBLOCK_TYPE"],
				"IBLOCK_ID" => $arParams["IBLOCK_ID"],
				"ELEMENT_SORT_FIELD" => $arParams["ELEMENT_SORT_FIELD"],
				"ELEMENT_SORT_ORDER" => $arParams["ELEMENT_SORT_ORDER"],
				"PROPERTY_CODE" => $arParams["PROPERTY_CODE"],
				"PAGE_ELEMENT_COUNT" => $arParams["PAGE_ELEMENT_COUNT"],
				"PRICE_CODE" => $arParams["PRICE_CODE"],
				"PAGER_TEMPLATE" => "round",
				"CONVERT_CURRENCY" => $arParams["CONVERT_CURRENCY"],
				"CURRENCY_ID" => $arParams['CURRENCY_ID'],
				"FILTER_NAME" => $arParams["FILTER_NAME"],
				"ADD_SECTIONS_CHAIN" => "N",
				"SHOW_ALL_WO_SECTION" => "Y",
				"SECTION_ID" => $_REQUEST["SECTION_ID"],
				"HIDE_NOT_AVAILABLE" => $arParams["HIDE_NOT_AVAILABLE"],
				"HIDE_MEASURES" => $arParams["HIDE_MEASURES"],
				"ENABLED_SKU_FILTER" => "Y",
				"CACHE_TYPE" => "Y",
				"CACHE_FILTER" => "N",
				"CACHE_GROUPS" => "Y"
			),
			false
		);?>
	</div>
<?else:?>
	<?if(empty($arResult["SECTIONS"])):?>
		<div id="empty">
			<div class="emptyWrapper">
				<div class="pictureContainer">
					<img src="<?=SITE_TEMPLATE_PATH?>/images/emptyFolder.png" alt="<?=GetMessage("EMPTY_HEADING")?>" class="emptyImg">
				</div>
				<div class="info">
					<h3><?=GetMessage("Q")?></h3>
					<p><?=GetMessage("EMPTY")?></p>
					<a href="<?=SITE_DIR?>" class="back"><?=GetMessage("MAIN")?></a>
				</div>
			</div>
			<?$APPLICATION->IncludeComponent("bitrix:menu", "emptyMenu", Array(
				"ROOT_MENU_TYPE" => "left",
					"MENU_CACHE_TYPE" => "N",
					"MENU_CACHE_TIME" => "3600",
					"MENU_CACHE_USE_GROUPS" => "Y",
					"MENU_CACHE_GET_VARS" => "",
					"MAX_LEVEL" => "1",
					"CHILD_MENU_TYPE" => "left",
					"USE_EXT" => "Y",
					"DELAY" => "N",
					"ALLOW_MULTI_SELECT" => "N",
				),
				false
			);?>
		</div>
	<?endif;?>
<?endif;?>