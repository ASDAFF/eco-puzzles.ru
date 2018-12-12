<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();
$this->setFrameMode(true);
?>
<?global $APPLICATION;?>
<?
if(!empty($arResult["VARIABLES"]["ELEMENT_CODE"])){
	$arSelect = Array("ID", "IBLOCK_ID", "NAME", "DETAIL_TEXT", "DETAIL_PICTURE", "SECTION_PAGE_URL");
	$arFilter = Array("IBLOCK_ID" => IntVal($arParams["IBLOCK_ID"]), "CODE" => $arResult["VARIABLES"]["ELEMENT_CODE"], "ACTIVE_DATE" => "Y", "ACTIVE" => "Y");
	$res = CIBlockElement::GetList(Array(), $arFilter, false, false, $arSelect);
	if($ob = $res->GetNextElement()){
		$arResult["ITEM"] = $ob->GetFields();
		$ELEMENT_ID = $arResult["ITEM"]["ID"];
		$ELEMENT_NAME = $arResult["ITEM"]["NAME"];
	}
}
?>
<?
if(CModule::IncludeModule("iblock")){
	if(!empty($ELEMENT_ID)){
		
		$ipropValues = new \Bitrix\Iblock\InheritedProperty\ElementValues(
		    $arParams["IBLOCK_ID"],
		    $ELEMENT_ID
		);

		if($arSeoProp = $ipropValues->getValues()){
			
			$APPLICATION->SetPageProperty("description", $arSeoProp["ELEMENT_META_DESCRIPTION"]);
			$APPLICATION->SetPageProperty("keywords", $arSeoProp["ELEMENT_META_KEYWORDS"]);
			
			if(!empty($arSeoProp["ELEMENT_META_TITLE"])){
				$APPLICATION->SetTitle($arSeoProp["ELEMENT_META_TITLE"]);
			}

			if(!empty($arSeoProp["ELEMENT_PAGE_TITLE"])){
				$APPLICATION->AddChainItem($arSeoProp["ELEMENT_PAGE_TITLE"]);
			}

		}else{
			$APPLICATION->AddChainItem($ELEMENT_NAME);
			$APPLICATION->SetTitle($ELEMENT_NAME);
		}

	}
}
?>
<?if(!empty($ELEMENT_ID)):?>
	<?$BASE_PRICE = CCatalogGroup::GetBaseGroup();?>
	<?$arSortFields = array(
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
	);?>

		<?
			$rsMinPriceProperty = CIBlock::GetProperties($arParams["PRODUCT_IBLOCK_ID"], Array(), Array("CODE" => "MINIMUM_PRICE"));
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


	<?$BIG_PICTURE = CFile::ResizeImageGet($arResult["ITEM"]["DETAIL_PICTURE"], array("width" => 150, "height" => 250), BX_RESIZE_IMAGE_PROPORTIONAL, false);?>

	<?if(!empty($BIG_PICTURE["src"])):?>
		<div class="brandsBigPicture"><img src="<?=$BIG_PICTURE["src"]?>" alt="<?=$arResult["ITEM"]["NAME"]?>"></div>
	<?endif;?>

	<?if(!empty($arResult["ITEM"]["DETAIL_TEXT"])):?>
		<div class="brandsDescription"><?=$arResult["ITEM"]["DETAIL_TEXT"]?></div>
	<?endif;?>

	<a href="<?=$arResult["FOLDER"]?>" class="backToList"><?=GetMessage("BACK_TO_LIST_PAGE")?></a>
	<?
			
		//filter for component
		global $arrFilter;
		$arrFilter["PROPERTY_ATT_BRAND"] = $ELEMENT_ID;
		$arrFilter["ACTIVE"] = "Y";
		
		//filter for calc
		$arFilter = array(
			"IBLOCK_ID" => $arParams["PRODUCT_IBLOCK_ID"],
			"PROPERTY_ATT_BRAND" => $ELEMENT_ID,
			"ACTIVE" => "Y"
		);

		if($arParams["HIDE_NOT_AVAILABLE"] == "Y"){
			$arFilter["CATALOG_AVAILABLE"] = "Y";
		}

		$countElements = CIBlockElement::GetList(array(), $arrFilter, array(), false);
			
	?>
		<?if($countElements > 1){

			$arSections = array();
			$arResult["MENU_SECTIONS"] = array();
			$arFilter["SECTION_ID"] = array();

			$res = CIBlockElement::GetList(array("NAME" => "ASC"), $arFilter, false, false, array("ID"));
			while($nextElement = $res->GetNext()){
				$resGroup = CIBlockElement::GetElementGroups($nextElement["ID"], false);
				while($arGroup = $resGroup->Fetch()){
				    $IBLOCK_SECTION_ID = $arGroup["ID"];
					$arSections[$IBLOCK_SECTION_ID] = $IBLOCK_SECTION_ID;
					$arSectionCount[$IBLOCK_SECTION_ID] = !empty($arSectionCount[$IBLOCK_SECTION_ID]) ? $arSectionCount[$IBLOCK_SECTION_ID] + 1 : 1;
				}

				$arResult["ITEMS"][] = $nextElement;
			}

			if(!empty($arSections)){
				$arFilter = array("ID" => $arSections);
				$rsSections = CIBlockSection::GetList(array("NAME" => "ASC"), $arFilter);
				while ($arSection = $rsSections->Fetch()){
					$searchParam = "SECTION_ID=".$arSection["ID"];
					$searchID = intval($_GET["SECTION_ID"]);
					$arSection["SELECTED"] = $arSection["ID"] == $searchID ? "Y" : "N";
					$arSection["FILTER_LINK"] = $APPLICATION->GetCurPageParam($searchParam , array("SECTION_ID"));
					$arSection["ELEMENTS_COUNT"] = $arSectionCount[$arSection["ID"]];
					array_push($arResult["MENU_SECTIONS"], $arSection);
				}
			}

		}?>

		<?if($countElements > 1):?>
			<?$this->SetViewTarget("menuRollClass");?> menuRolled<?$this->EndViewTarget();?>
			<?$this->SetViewTarget("hiddenZoneClass");?> hiddenZone<?$this->EndViewTarget();?>
		<?endif;?>

		<?
			$this->SetViewTarget("smartFilter");
	    ?>

		<?
			$OPTION_CURRENCY  = CCurrency::GetBaseCurrency();
		?>

		<?if(!empty($arResult["MENU_SECTIONS"]) && count($arResult["MENU_SECTIONS"]) > 1):?>
			<div id="nextSection">
				<span class="title"><?=GetMessage("SELECT_CATEGORY");?></span>
				<ul>
					<?foreach ($arResult["MENU_SECTIONS"] as $ic => $arSection):?>
						<li><a href="<?=$arSection["FILTER_LINK"]?>"<?if($arSection["SELECTED"] == "Y"):?> class="selected"<?endif;?>><?=$arSection["NAME"]?> (<?=$arSection["ELEMENTS_COUNT"]?>)</a></li>
					<?endforeach;?>
				</ul>
			</div>
		<?endif;?>

		<?if($countElements > 1):?>
			<?$APPLICATION->IncludeComponent(
				"dresscode:cast.smart.filter", 
				"", 
				array(
					"IBLOCK_TYPE" => $arParams["PRODUCT_IBLOCK_TYPE"],
					"IBLOCK_ID" =>$arParams["PRODUCT_IBLOCK_ID"],
					"SECTION_ID" => $_REQUEST["SECTION_ID"],
					"FILTER_NAME" => $arParams["PRODUCT_FILTER_NAME"],
					"HIDE_NOT_AVAILABLE" => $arParams["HIDE_NOT_AVAILABLE"],
					"INCLUDE_SUBSECTIONS" => "Y",
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
					"CONVERT_CURRENCY" => $arParams["PRODUCT_CONVERT_CURRENCY"],
					"CURRENCY_ID" => $arParams["PRODUCT_CURRENCY_ID"],
					"FILTER_ADD_PROPERTY_NAME" => "ATT_BRAND",
					"FILTER_ADD_PROPERTY_VALUE" => $ELEMENT_ID,

				),
				false
			);?>
		<?endif;?>
		<?
			$this->EndViewTarget();
		?>

	<?if($countElements):?>
		<div id="catalog">
			<h1 class="brandsHeading"><?if(!empty($arSeoProp["ELEMENT_PAGE_TITLE"])):?><?=$arSeoProp["ELEMENT_PAGE_TITLE"]?><?else:?><?=GetMessage("CATALOG_TITLE")?><?=$ELEMENT_NAME?><?endif;?></h1>
			<div id="catalogLine">
				<?if($countElements > 1):?>
					<div class="column oFilter">
						<a href="#" class="oSmartFilter btn-simple btn-micro"><span class="ico"></span><?=GetMessage("CATALOG_FILTER")?></a>
					</div>
				<?endif;?>
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
			<?
				reset($arTemplates);

				global $arrFilter;
				unset($arrFilter["FACET_OPTIONS"]);

				$arrFilter["FACET_OPTIONS"] = array();
				$_REQUEST["SECTION_ID"] = !empty($_REQUEST["SECTION_ID"]) ? $_REQUEST["SECTION_ID"] : 0;

			?>
			<?$APPLICATION->IncludeComponent(
				"dresscode:catalog.section",
				 !empty($arParams["CATALOG_TEMPLATE"]) ? strtolower($arParams["CATALOG_TEMPLATE"]) : strtolower(key($arTemplates)),
				array(
					"IBLOCK_TYPE" => $arParams["PRODUCT_IBLOCK_TYPE"],
					"IBLOCK_ID" => $arParams["PRODUCT_IBLOCK_ID"],
					"ELEMENT_SORT_FIELD" => $arParams["ELEMENT_SORT_FIELD"],
					"ELEMENT_SORT_ORDER" => $arParams["ELEMENT_SORT_ORDER"],
					"INCLUDE_SUBSECTIONS" => "N",
					"FILTER_NAME" => $arParams["PRODUCT_FILTER_NAME"],
					"PRICE_CODE" => $arParams["PRODUCT_PRICE_CODE"],
					"PROPERTY_CODE" => $arParams["PRODUCT_PROPERTY_CODE"],
					"PAGER_TEMPLATE" => "round",
					"PAGE_ELEMENT_COUNT" => $arParams["PAGE_ELEMENT_COUNT"],
					"CONVERT_CURRENCY" => $arParams["PRODUCT_CONVERT_CURRENCY"],
					"CURRENCY_ID" => $arParams["PRODUCT_CURRENCY_ID"],
					"HIDE_NOT_AVAILABLE" => $arParams["HIDE_NOT_AVAILABLE"],
					"HIDE_MEASURES" => $arParams["HIDE_MEASURES"],
					"SECTION_ID" => $_REQUEST["SECTION_ID"],
					"HIDE_DESCRIPTION_TEXT" => "Y",
					"SHOW_ALL_WO_SECTION" => "Y",
					"ENABLED_SKU_FILTER" => "Y",
					"ADD_SECTIONS_CHAIN" => "N",
					"CACHE_FILTER" => "N",
					"CACHE_TYPE" => "Y",
					"AJAX_MODE" => "N"
				),
				$component
			);?>

		</div>
	<?else:?>
		<style>
			.backToList{
				display: inline-block;
				margin-bottom: 24px;
				float: none;
			}
		</style>
	<?endif;?>
<?else:?>

	<?
		if (!defined("ERROR_404"))
		   define("ERROR_404", "Y");

		\CHTTP::setStatus("404 Not Found");

		if ($APPLICATION->RestartWorkarea()) {
		   require(\Bitrix\Main\Application::getDocumentRoot()."/404.php");
		   die();
		}
	?>

<?endif;?>