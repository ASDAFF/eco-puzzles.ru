<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();
	$this->setFrameMode(true);
	$countPropertyElements = 7;
	$morePhotoCounter = 0;
	$propertyCounter = 0;
	global $relatedFilter;
	global $similarFilter;
	global $USER;
?>
<?
	$this->AddEditAction($arResult["ID"], $arResult["EDIT_LINK"], CIBlock::GetArrayByID($arResult["IBLOCK_ID"], "ELEMENT_EDIT"));
	$this->AddDeleteAction($arResult["ID"], $arResult["DELETE_LINK"], CIBlock::GetArrayByID($arResult["IBLOCK_ID"], "ELEMENT_DELETE"), array("CONFIRM" => GetMessage('CT_BNL_ELEMENT_DELETE_CONFIRM')));
?>

<?$this->SetViewTarget("after_breadcrumb_container");?>
	<h1 class="changeName"><?=$APPLICATION->GetPageProperty("title")?></h1>
<?$this->EndViewTarget();?>

<div id="<?=$this->GetEditAreaId($arResult["ID"]);?>">
	<div id="catalogElement" class="item<?if(!empty($arResult["SKU_OFFERS"])):?> elementSku<?endif;?>" data-from-cache="<?=$arResult["FROM_CACHE"]?>" data-convert-currency="<?=$arParams["CONVERT_CURRENCY"]?>" data-currency-id="<?=$arParams["CURRENCY_ID"]?>" data-hide-not-available="<?=$arParams["HIDE_NOT_AVAILABLE"]?>" data-currency="<?=$arResult["EXTRA_SETTINGS"]["CURRENCY"]?>" data-product-id="<?=!empty($arResult["~ID"]) ? $arResult["~ID"] : $arResult["ID"]?>" data-iblock-id="<?=$arResult["SKU_INFO"]["IBLOCK_ID"]?>" data-prop-id="<?=$arResult["SKU_INFO"]["SKU_PROPERTY_ID"]?>" data-hide-measure="<?=$arParams["HIDE_MEASURES"]?>" data-price-code="<?=implode("||", $arParams["PRODUCT_PRICE_CODE"])?>">
		<div id="elementSmallNavigation">
			<?if(!empty($arResult["TABS"])):?>
				<div class="tabs">
					<?foreach ($arResult["TABS"] as $it => $arTab):?>
						<div class="tab<?if($arTab["ACTIVE"] == "Y"):?> active<?endif;?>" data-id="<?=$arTab["ID"]?>"><a href="<?if(!empty($arTab["LINK"])):?><?=$arTab["LINK"]?><?else:?>#<?endif;?>"><span><?=$arTab["NAME"]?></span></a></div>
					<?endforeach;?>
				</div>
			<?endif;?>
		</div>
		<div id="tableContainer">
			<div id="elementNavigation" class="column">
				<?if(!empty($arResult["TABS"])):?>
					<div class="tabs">
						<?foreach ($arResult["TABS"] as $it => $arTab):?>
							<div class="tab<?if($arTab["ACTIVE"] == "Y"):?> active<?endif;?>" data-id="<?=$arTab["ID"]?>"><a href="<?if(!empty($arTab["LINK"])):?><?=$arTab["LINK"]?><?else:?>#<?endif;?>"><?=$arTab["NAME"]?><img src="<?=$arTab["PICTURE"]?>" alt="<?=$arTab["NAME"]?>"></a></div>
						<?endforeach;?>
					</div>
				<?endif;?>
			</div>
			<div id="elementContainer" class="column">
				<div class="mainContainer" id="browse">
					<div class="col">
						<?if(!empty($arResult["PROPERTIES"]["OFFERS"]["VALUE"])):?>
							<div class="markerContainer">
								<?foreach ($arResult["PROPERTIES"]["OFFERS"]["VALUE"] as $ifv => $marker):?>
								    <div class="marker" style="background-color: <?=strstr($arResult["PROPERTIES"]["OFFERS"]["VALUE_XML_ID"][$ifv], "#") ? $arResult["PROPERTIES"]["OFFERS"]["VALUE_XML_ID"][$ifv] : "#424242"?>"><?=$marker?></div>
								<?endforeach;?>
							</div>
						<?endif;?>
						<?if(!empty($arResult["IMAGES"])):?>
							<div id="pictureContainer">
								<div class="pictureSlider">
									<?foreach ($arResult["IMAGES"] as $ipr => $arNextPicture):?>
										<div class="item">
											<a href="<?=$arNextPicture["LARGE_IMAGE"]["SRC"]?>" title="<?=GetMessage("CATALOG_ELEMENT_ZOOM")?>"  class="zoom" data-small-picture="<?=$arNextPicture["SMALL_IMAGE"]["SRC"]?>" data-large-picture="<?=$arNextPicture["LARGE_IMAGE"]["SRC"]?>"><img src="<?=$arNextPicture["MEDIUM_IMAGE"]["SRC"]?>" alt="<?if($ipr==0):?><?if(!empty($arResult["IPROPERTY_VALUES"]["ELEMENT_DETAIL_PICTURE_FILE_ALT"])):?><?=$arResult["IPROPERTY_VALUES"]["ELEMENT_DETAIL_PICTURE_FILE_ALT"]?><?else:?><?=$arResult["NAME"]?><?endif;?><?endif;?>" title="<?if($ipr==0):?><?if(!empty($arResult["IPROPERTY_VALUES"]["ELEMENT_DETAIL_PICTURE_FILE_TITLE"])):?><?=$arResult["IPROPERTY_VALUES"]["ELEMENT_DETAIL_PICTURE_FILE_TITLE"]?><?else:?><?=$arResult["NAME"]?><?endif;?><?endif;?>"></a>										
										</div>
									<?endforeach;?>
								</div>
							</div>
							<div id="moreImagesCarousel"<?if(count($arResult["IMAGES"]) <= 1):?> class="hide"<?endif;?>>
								<div class="carouselWrapper">
									<div class="slideBox">
										<?if(count($arResult["IMAGES"]) > 1):?>
											<?foreach ($arResult["IMAGES"] as $ipr => $arNextPicture):?>
												<div class="item">
													<a href="<?=$arNextPicture["LARGE_IMAGE"]["SRC"]?>" data-large-picture="<?=$arNextPicture["LARGE_IMAGE"]["SRC"]?>" data-small-picture="<?=$arNextPicture["SMALL_IMAGE"]["SRC"]?>">
														<img src="<?=$arNextPicture["SMALL_IMAGE"]["SRC"]?>" alt="">
													</a>
												</div>
											<?endforeach;?>
										<?endif;?>
									</div>
								</div>
								<div class="controls">
									<a href="#" id="moreImagesLeftButton"></a>
									<a href="#" id="moreImagesRightButton"></a>
								</div>
							</div>
						<?endif;?>
					</div>
					<div class="col<?if(empty($arResult["PREVIEW_TEXT"]) && empty($arResult["SKU_OFFERS"]) && empty($arResult["PROPERTIES"])):?> hide<?endif;?>">
						<div id="smallElementTools">
							<?include($_SERVER["DOCUMENT_ROOT"]."/".$templateFolder."/include/right_section.php");?>
						</div>
						<?if(!empty($arResult["BRAND"]["PICTURE"])):?>
							<a href="<?=$arResult["BRAND"]["DETAIL_PAGE_URL"]?>" class="brandImage"><img src="<?=$arResult["BRAND"]["PICTURE"]["src"]?>" alt="<?=$arResult["BRAND"]["NAME"]?>"></a>
						<?endif;?>
						<?if(!empty($arResult["PREVIEW_TEXT"])):?>
							<div class="description">
								<div class="heading"><?=GetMessage("CATALOG_ELEMENT_PREVIEW_TEXT_LABEL")?></div>
								<div class="changeShortDescription" data-first-value='<?=$arResult["PREVIEW_TEXT"]?>'><?=$arResult["PREVIEW_TEXT"]?></div>
							</div>
						<?endif;?>
						<?if(!empty($arResult["SKU_OFFERS"])):?>
							<?if(!empty($arResult["SKU_PROPERTIES"]) && $level = 1):?>
								<div class="elementSkuVariantLabel"><?=GetMessage("SKU_VARIANT_LABEL")?></div>
								<?foreach ($arResult["SKU_PROPERTIES"] as $propName => $arNextProp):?>
									<?if(!empty($arNextProp["VALUES"])):?>
										<div class="elementSkuProperty" data-name="<?=$propName?>" data-level="<?=$level++?>" data-highload="<?=$arNextProp["HIGHLOAD"]?>">
											<div class="elementSkuPropertyName"><?=$arNextProp["NAME"]?>:</div>
											<ul class="elementSkuPropertyList">
												<?foreach ($arNextProp["VALUES"] as $xml_id => $arNextPropValue):?>
													<li class="elementSkuPropertyValue<?if($arNextPropValue["DISABLED"] == "Y"):?> disabled<?elseif($arNextPropValue["SELECTED"] == "Y"):?> selected<?endif;?>" data-name="<?=$propName?>" data-value="<?=$arNextPropValue["VALUE"]?>">
														<a href="#" class="elementSkuPropertyLink">
															<?if(!empty($arNextPropValue["IMAGE"])):?>
																<img src="<?=$arNextPropValue["IMAGE"]["src"]?>">
															<?else:?>
																<?=$arNextPropValue["DISPLAY_VALUE"]?>
															<?endif;?>
														</a>
													</li>
												<?endforeach;?>
											</ul>
										</div>
									<?endif;?>
								<?endforeach;?>
							<?endif;?>
						<?endif;?>
						<div class="changePropertiesNoGroup">
							<?$APPLICATION->IncludeComponent(
								"dresscode:catalog.properties.list", 
								"no-group",
								array(
									"PRODUCT_ID" => $arResult["ID"],
									"COUNT_PROPERTIES" => $countPropertyElements,
									"ELEMENT_LAST_SECTION_ID" => $arResult["LAST_SECTION"]["ID"]
								),
								false
							);?>
						</div>
					</div>
				</div>
				<?if($arParams["DISPLAY_OFFERS_TABLE"] == "Y" && !empty($arResult["SKU_OFFERS"])):?>
					<div id="skuOffersTable">
						<span class="heading"><?=GetMessage("ELEMENT_SKU_OFFERS_TABLE_HEADING")?></span>
						<div class="offersTableContainer">
							<table class="offersTable">
								<tr>
									<th colspan="2"><?=GetMessage("ELEMENT_SKU_OFFERS_TABLE_TITLE")?></th>
									<?if(!empty($arResult["OFFERS_PROPERTY_MAP"])):?>
										<?foreach ($arResult["OFFERS_PROPERTY_MAP"] as $mkey => $arNextMapItem):?>
											<?if(!empty($arNextMapItem["VALUES"])):?>
												<th><?=$arNextMapItem["NAME"]?></th>
											<?endif;?>
										<?endforeach;?>
									<?endif;?>
									<th><?=GetMessage("ELEMENT_SKU_OFFERS_TABLE_PRICE")?></th>
									<th><?=GetMessage("ELEMENT_SKU_OFFERS_TABLE_QUANTITY")?></th>
									<th><?=GetMessage("ELEMENT_SKU_OFFERS_TABLE_BASKET")?></th>
								</tr>
								<?foreach ($arResult["SKU_OFFERS"] as $ikey => $arNextOffer):?>
									<tr>
										<td class="offersPicture"><img src="<?=$arNextOffer["PICTURE"]["src"]?>" alt="<?=$arNextOffer["NAME"]?>"></td>
										<td class="offersName"><?=$arNextOffer["NAME"]?></td>
										<?if(!empty($arNextOffer["PROPERTIES"])):?>
											<?foreach ($arNextOffer["PROPERTIES"] as $iProp => $arNextProperty):?>
												<?if(!empty($arNextProperty["VALUE"])):?>
													<td class="property"><?=$arNextProperty["VALUE"]?></td>
												<?endif;?>
											<?endforeach;?>
										<?endif;?>
										<td>
											<?if(!empty($arNextOffer["PRICE"])):?>
												<?if($arNextOffer["EXTRA_SETTINGS"]["COUNT_PRICES"] > 1):?>
													<a class="price getPricesWindow" data-id="<?=$arNextOffer["ID"]?>">
														<span class="lnk"><?=CCurrencyLang::CurrencyFormat($arNextOffer["PRICE"]["DISCOUNT_PRICE"], $arNextOffer["EXTRA_SETTINGS"]["CURRENCY"], true)?></span>
														<?if($arParams["HIDE_MEASURES"] != "Y" && !empty($arNextOffer["EXTRA_SETTINGS"]["MEASURES"][$arNextOffer["CATALOG_MEASURE"]]["SYMBOL_RUS"])):?>
															<span class="measure"> / <?=$arNextOffer["EXTRA_SETTINGS"]["MEASURES"][$arNextOffer["CATALOG_MEASURE"]]["SYMBOL_RUS"]?></span>
														<?endif;?>
														<?if(!empty($arNextOffer["PRICE"]["DISCOUNT"])):?>
															<s class="discount"><?=CCurrencyLang::CurrencyFormat($arNextOffer["PRICE"]["RESULT_PRICE"]["BASE_PRICE"], $arNextOffer["EXTRA_SETTINGS"]["CURRENCY"], true)?></s>
														<?endif;?>
													</a>
												<?else:?>
													<a class="price"><?=CCurrencyLang::CurrencyFormat($arNextOffer["PRICE"]["DISCOUNT_PRICE"], $arNextOffer["EXTRA_SETTINGS"]["CURRENCY"], true)?>
														<?if($arParams["HIDE_MEASURES"] != "Y" && !empty($arNextOffer["EXTRA_SETTINGS"]["MEASURES"][$arNextOffer["CATALOG_MEASURE"]]["SYMBOL_RUS"])):?>
															<span class="measure"> / <?=$arNextOffer["EXTRA_SETTINGS"]["MEASURES"][$arNextOffer["CATALOG_MEASURE"]]["SYMBOL_RUS"]?></span>
														<?endif;?>
														<?if(!empty($arNextOffer["PRICE"]["DISCOUNT"])):?>
															<s class="discount"><?=CCurrencyLang::CurrencyFormat($arNextOffer["PRICE"]["RESULT_PRICE"]["BASE_PRICE"], $arNextOffer["EXTRA_SETTINGS"]["CURRENCY"], true)?></s>
														<?endif;?>
													</a>
												<?endif;?>
											<?else:?>
												<a class="price"><?=GetMessage("REQUEST_PRICE_LABEL")?></a>
											<?endif;?>
										</td>
										<td class="quantity">
											<?if($arNextOffer["CATALOG_QUANTITY"] > 0):?>
												<?if(!empty($arNextOffer["EXTRA_SETTINGS"]["STORES"]) && $arNextOffer["EXTRA_SETTINGS"]["STORES_MAX_QUANTITY"] > 0):?>
													<a href="#" data-id="<?=$arNextOffer["ID"]?>" class="inStock getStoresWindow"><img src="<?=SITE_TEMPLATE_PATH?>/images/inStock.png" alt="<?=GetMessage("AVAILABLE")?>" class="icon"><span><?=GetMessage("AVAILABLE")?></span></a>
												<?else:?>
													<span class="inStock"><img src="<?=SITE_TEMPLATE_PATH?>/images/inStock.png" alt="<?=GetMessage("AVAILABLE")?>" class="icon"><span><?=GetMessage("AVAILABLE")?></span></span>
												<?endif;?>
											<?else:?>
												<?if($arNextOffer["CATALOG_AVAILABLE"] == "Y"):?>
													<a class="onOrder"><img src="<?=SITE_TEMPLATE_PATH?>/images/onOrder.png" alt="<?=GetMessage("ON_ORDER")?>" class="icon"><?=GetMessage("ON_ORDER")?></a>
												<?else:?>
													<a class="outOfStock"><img src="<?=SITE_TEMPLATE_PATH?>/images/outOfStock.png" alt="<?=GetMessage("CATALOG_NO_AVAILABLE")?>" class="icon"><?=GetMessage("CATALOG_NO_AVAILABLE")?></a>
												<?endif;?>
											<?endif;?>
										</td>
										<td class="basket">
											<?if(!empty($arNextOffer["PRICE"])):?>
												<a href="#" class="addCart<?if($arNextOffer["CATALOG_AVAILABLE"] != "Y"):?> disabled<?endif;?>" data-id="<?=$arNextOffer["ID"]?>" data-quantity="<?=$arNextOffer["EXTRA_SETTINGS"]["BASKET_STEP"]?>"><?=GetMessage("ADDCART_LABEL")?></a>
											<?else:?>
												<a href="#" class="addCart disabled requestPrice" data-id="<?=$arNextOffer["ID"]?>" data-quantity="<?=$arNextOffer["EXTRA_SETTINGS"]["BASKET_STEP"]?>"><?=GetMessage("REQUEST_PRICE_BUTTON_LABEL")?></a>
											<?endif;?>										
										</td>
									</tr>
								<?endforeach;?>
							</table>
						</div>
					</div>
				<?endif;?>
				<?if(!empty($arResult["COMPLECT"]["ITEMS"])):?>
					<div id="complect">
						<span class="heading"><?=GetMessage("ELEMENT_COMPLECT_HEADING")?></span>
						<div class="complectList">
							<?foreach($arResult["COMPLECT"]["ITEMS"] as $inc => $arNextComplect):?>
								<div class="complectListItem">
									<div class="complectListItemWrap">
										<div class="complectListItemPicture">
											<a href="<?=$arNextComplect["DETAIL_PAGE_URL"]?>" class="complectListItemPicLink"><img src="<?=$arNextComplect["PICTURE"]["src"]?>" alt="<?=$arNextComplect["NAME"]?>"></a>
										</div>
										<div class="complectListItemName">
											<a href="<?=$arNextComplect["DETAIL_PAGE_URL"]?>" class="complectListItemLink"><span class="middle"><?=$arNextComplect["NAME"]?></span></a>
										</div>
										<a class="complectListItemPrice">
											<?=$arNextComplect["PRICE"]["PRICE_FORMATED"]?> 
											<?if($arParams["HIDE_MEASURES"] != "Y" && !empty($arResult["MEASURES"][$arNextComplect["CATALOG_MEASURE"]]["SYMBOL_RUS"])):?>
												<span class="measure"> /<?if(!empty($arNextComplect["QUANTITY"]) && $arNextComplect["QUANTITY"] != 1):?> <?=$arNextComplect["QUANTITY"]?><?endif;?> <?=$arResult["MEASURES"][$arNextComplect["CATALOG_MEASURE"]]["SYMBOL_RUS"]?></span>
											<?endif;?>
											<?if($arNextComplect["PRICE"]["PRICE_DIFF"] > 0):?>
												<s class="discount"><?=$arNextComplect["PRICE"]["BASE_PRICE_FORMATED"]?></s>
											<?endif;?>
										</a>
									</div>
								</div>
							<?endforeach;?>
						</div>
						<div class="complectResult">
							<?=GetMessage("CATALOG_ELEMENT_COMPLECT_PRICE_RESULT")?>
							<div class="complectPriceResult"><?=CCurrencyLang::CurrencyFormat($arResult["PRICE"]["DISCOUNT_PRICE"], $arResult["EXTRA_SETTINGS"]["CURRENCY"], true)?></div> 
							<?if(!empty($arResult["PRICE"]["DISCOUNT"])):?>
								<s class="discount"><?=CCurrencyLang::CurrencyFormat($arResult["PRICE"]["RESULT_PRICE"]["BASE_PRICE"], $arResult["EXTRA_SETTINGS"]["CURRENCY"], true)?></s>
								<div class="complectResultEconomy">
									<?=GetMessage("CATALOG_ELEMENT_COMPLECT_ECONOMY")?> <span class="complectResultEconomyValue"><?=CCurrencyLang::CurrencyFormat($arResult["PRICE"]["RESULT_PRICE"]["DISCOUNT"], $arResult["EXTRA_SETTINGS"]["CURRENCY"], true)?></span>
								</div>
							<?endif;?>
						</div>
					</div>
				<?endif;?>
				<?CBitrixComponent::includeComponentClass("bitrix:sale.products.gift");
					$APPLICATION->IncludeComponent(
						"bitrix:sale.products.gift",
						".default",
						array(
							"IBLOCK_ID" => $arParams["IBLOCK_ID"],
							"IBLOCK_TYPE" => $arParams["IBLOCK_TYPE"],
							"PRODUCT_ID_VARIABLE" => $arParams["PRODUCT_ID_VARIABLE"],
							"ACTION_VARIABLE" => $arParams["ACTION_VARIABLE"],
							"PRODUCT_ROW_VARIANTS" => "",
							"PAGE_ELEMENT_COUNT" => 8,
							"DEFERRED_PRODUCT_ROW_VARIANTS" => \Bitrix\Main\Web\Json::encode(
								SaleProductsGiftComponent::predictRowVariants(
									1,
									1
								)
							),
							"DEFERRED_PAGE_ELEMENT_COUNT" => 8,//$arParams["GIFTS_DETAIL_PAGE_ELEMENT_COUNT"],
							"SHOW_DISCOUNT_PERCENT" => $arParams["GIFTS_SHOW_DISCOUNT_PERCENT"],
							"DISCOUNT_PERCENT_POSITION" => $arParams["DISCOUNT_PERCENT_POSITION"],
							"SHOW_OLD_PRICE" => $arParams["GIFTS_SHOW_OLD_PRICE"],
							"PRODUCT_DISPLAY_MODE" => "Y",
							"PRODUCT_BLOCKS_ORDER" => $arParams["GIFTS_PRODUCT_BLOCKS_ORDER"],
							"TEXT_LABEL_GIFT" => $arParams["GIFTS_DETAIL_TEXT_LABEL_GIFT"],
							"LABEL_PROP_".$arParams["IBLOCK_ID"] => array(),
							"LABEL_PROP_MOBILE_".$arParams["IBLOCK_ID"] => array(),
							"LABEL_PROP_POSITION" => $arParams["LABEL_PROP_POSITION"],

							"ADD_TO_BASKET_ACTION" => (isset($arParams["ADD_TO_BASKET_ACTION"]) ? $arParams["ADD_TO_BASKET_ACTION"] : ""),
							"MESS_BTN_BUY" => $arParams["~GIFTS_MESS_BTN_BUY"],
							"MESS_BTN_ADD_TO_BASKET" => $arParams["~GIFTS_MESS_BTN_BUY"],
							"MESS_BTN_DETAIL" => $arParams["~MESS_BTN_DETAIL"],
							"MESS_BTN_SUBSCRIBE" => $arParams["~MESS_BTN_SUBSCRIBE"],

							"SHOW_PRODUCTS_".$arParams["IBLOCK_ID"] => "Y",
							"PROPERTY_CODE_".$arParams["IBLOCK_ID"] => $arParams["LIST_PROPERTY_CODE"],
							"PROPERTY_CODE_MOBILE".$arParams["IBLOCK_ID"] => $arParams["LIST_PROPERTY_CODE_MOBILE"],
							"PROPERTY_CODE_".$arResult["OFFERS_IBLOCK"] => $arParams["OFFER_TREE_PROPS"],
							"OFFER_TREE_PROPS_".$arResult["OFFERS_IBLOCK"] => $arParams["OFFER_TREE_PROPS"],
							"CART_PROPERTIES_".$arResult["OFFERS_IBLOCK"] => $arParams["OFFERS_CART_PROPERTIES"],
							"ADDITIONAL_PICT_PROP_".$arParams["IBLOCK_ID"] => (isset($arParams["ADD_PICT_PROP"]) ? $arParams["ADD_PICT_PROP"] : ""),
							"ADDITIONAL_PICT_PROP_".$arResult["OFFERS_IBLOCK"] => (isset($arParams["OFFER_ADD_PICT_PROP"]) ? $arParams["OFFER_ADD_PICT_PROP"] : ""),
							"CACHE_TYPE" => $arParams["CACHE_TYPE"],
							"CACHE_TIME" => $arParams["CACHE_TIME"],
							"HIDE_NOT_AVAILABLE" => $arParams["HIDE_NOT_AVAILABLE"],
							"HIDE_NOT_AVAILABLE_OFFERS" => $arParams["HIDE_NOT_AVAILABLE"],
							"PRODUCT_SUBSCRIPTION" => $arParams["PRODUCT_SUBSCRIPTION"],
							"TEMPLATE_THEME" => $arParams["TEMPLATE_THEME"],
							"PRICE_CODE" => $arParams["PRODUCT_PRICE_CODE"],
							"SHOW_PRICE_COUNT" => $arParams["SHOW_PRICE_COUNT"],
							"PRICE_VAT_INCLUDE" => $arParams["PRICE_VAT_INCLUDE"],
							"CONVERT_CURRENCY" => $arParams["CONVERT_CURRENCY"],
							"BASKET_URL" => $arParams["BASKET_URL"],
							"ADD_PROPERTIES_TO_BASKET" => $arParams["ADD_PROPERTIES_TO_BASKET"],
							"PRODUCT_PROPS_VARIABLE" => $arParams["PRODUCT_PROPS_VARIABLE"],
							"PARTIAL_PRODUCT_PROPERTIES" => $arParams["PARTIAL_PRODUCT_PROPERTIES"],
							"USE_PRODUCT_QUANTITY" => "N",
							"PRODUCT_QUANTITY_VARIABLE" => $arParams["PRODUCT_QUANTITY_VARIABLE"],
							"CACHE_GROUPS" => $arParams["CACHE_GROUPS"],
							"POTENTIAL_PRODUCT_TO_BUY" => array(
								"ID" => isset($arResult["ID"]) ? $arResult["ID"] : null,
								"MODULE" => isset($arResult["MODULE"]) ? $arResult["MODULE"] : "catalog",
								"PRODUCT_PROVIDER_CLASS" => isset($arResult["PRODUCT_PROVIDER_CLASS"]) ? $arResult["PRODUCT_PROVIDER_CLASS"] : "CCatalogProductProvider",
								"QUANTITY" => isset($arResult["QUANTITY"]) ? $arResult["QUANTITY"] : null,
								"IBLOCK_ID" => isset($arResult["IBLOCK_ID"]) ? $arResult["IBLOCK_ID"] : null,

								"PRIMARY_OFFER_ID" => isset($arResult["OFFERS"][$arResult["OFFERS_SELECTED"]]["ID"])
									? $arResult["OFFERS"][$arResult["OFFERS_SELECTED"]]["ID"]
									: null,
								"SECTION" => array(
									"ID" => isset($arResult["SECTION"]["ID"]) ? $arResult["SECTION"]["ID"] : null,
									"IBLOCK_ID" => isset($arResult["SECTION"]["IBLOCK_ID"]) ? $arResult["SECTION"]["IBLOCK_ID"] : null,
									"LEFT_MARGIN" => isset($arResult["SECTION"]["LEFT_MARGIN"]) ? $arResult["SECTION"]["LEFT_MARGIN"] : null,
									"RIGHT_MARGIN" => isset($arResult["SECTION"]["RIGHT_MARGIN"]) ? $arResult["SECTION"]["RIGHT_MARGIN"] : null,
								),
							),

							"USE_ENHANCED_ECOMMERCE" => $arParams["USE_ENHANCED_ECOMMERCE"],
							"DATA_LAYER_NAME" => $arParams["DATA_LAYER_NAME"],
							"BRAND_PROPERTY" => $arParams["BRAND_PROPERTY"]
						),
						$component,
						array("HIDE_ICONS" => "Y")
					);
				?>
				<?$APPLICATION->IncludeComponent(
		        	"bitrix:catalog.set.constructor", 
		        	".default", 
		        	array(
		        		"ELEMENT_ID" => $arResult["ID"],
		        		"CURRENCY_ID" => $arResult["EXTRA_SETTINGS"]["CURRENCY"],
		        		"PRICE_CODE" => $arParams["PRODUCT_PRICE_CODE"],
		        		"IBLOCK_ID" => $arResult["IBLOCK_ID"],
		        		"OFFERS_CART_PROPERTIES" => array(),
		        		"BASKET_URL" => "/personal/cart/",
		        		"CACHE_TIME" => "36000000",
		        		"PRICE_VAT_INCLUDE" => "N",
		        		"CONVERT_CURRENCY" => "Y",
		        		"CACHE_GROUPS" => "Y",
		        		"CACHE_TYPE" => "Y"
		        	),
		        	false
		        );?>
				<?if(!empty($arResult["DETAIL_TEXT"])):?>
					<div id="detailText">
						<div class="heading"><?=GetMessage("CATALOG_ELEMENT_DETAIL_TEXT_HEADING")?></div>
						<div class="changeDescription" data-first-value='<?=str_replace("'", "", $arResult["~DETAIL_TEXT"])?>'><?=$arResult["~DETAIL_TEXT"]?></div>
					</div>
				<?endif;?>
				<div class="changePropertiesGroup">
					<?$APPLICATION->IncludeComponent(
						"dresscode:catalog.properties.list", 
						"group", 
						array(
							"PRODUCT_ID" => $arResult["ID"],
							"ELEMENT_LAST_SECTION_ID" => $arResult["LAST_SECTION"]["ID"]
						),
						false
					);?>
				</div>
		        <?if($arResult["SHOW_RELATED"] == "Y"):?>
		        	<div id="related">
						<div class="heading"><?=GetMessage("CATALOG_ELEMENT_ACCEESSORIES")?> (<?=$arResult["RELATED_COUNT"] <= 8 ? $arResult["RELATED_COUNT"] : 8?>)</div>
						<?$APPLICATION->IncludeComponent(
							"dresscode:catalog.section", 
							"squares", 
							array(
								"IBLOCK_TYPE" => $arParams["IBLOCK_TYPE"],
								"IBLOCK_ID" => $arParams["IBLOCK_ID"],
								"CONVERT_CURRENCY" => "Y",
								"CURRENCY_ID" => $arResult["EXTRA_SETTINGS"]["CURRENCY"],
								"ADD_SECTIONS_CHAIN" => "N",
								"COMPONENT_TEMPLATE" => "squares",
								"SECTION_ID" => $_REQUEST["SECTION_ID"],
								"SECTION_CODE" => "",
								"SECTION_USER_FIELDS" => array(
									0 => "",
									1 => "",
								),
								"ELEMENT_SORT_FIELD" => "sort",
								"ELEMENT_SORT_ORDER" => "asc",
								"ELEMENT_SORT_FIELD2" => "id",
								"ELEMENT_SORT_ORDER2" => "desc",
								"FILTER_NAME" => "relatedFilter",
								"INCLUDE_SUBSECTIONS" => "Y",
								"SHOW_ALL_WO_SECTION" => "Y",
								"HIDE_NOT_AVAILABLE" => $arParams["HIDE_NOT_AVAILABLE"],
								"PAGE_ELEMENT_COUNT" => "8",
								"LINE_ELEMENT_COUNT" => "3",
								"PROPERTY_CODE" => array(
									0 => "",
									1 => "",
								),
								"OFFERS_LIMIT" => "1",
								"BACKGROUND_IMAGE" => "-",
								"SECTION_URL" => "",
								"DETAIL_URL" => "",
								"SECTION_ID_VARIABLE" => "SECTION_ID",
								"SEF_MODE" => "N",
								"AJAX_MODE" => "N",
								"AJAX_OPTION_JUMP" => "N",
								"AJAX_OPTION_STYLE" => "Y",
								"AJAX_OPTION_HISTORY" => "N",
								"AJAX_OPTION_ADDITIONAL" => "undefined",
								"CACHE_TYPE" => "Y",
								"CACHE_TIME" => "36000000",
								"CACHE_GROUPS" => "Y",
								"SET_TITLE" => "Y",
								"SET_BROWSER_TITLE" => "Y",
								"BROWSER_TITLE" => "-",
								"SET_META_KEYWORDS" => "Y",
								"META_KEYWORDS" => "-",
								"SET_META_DESCRIPTION" => "Y",
								"META_DESCRIPTION" => "-",
								"SET_LAST_MODIFIED" => "N",
								"USE_MAIN_ELEMENT_SECTION" => "N",
								"CACHE_FILTER" => "Y",
								"ACTION_VARIABLE" => "action",
								"PRODUCT_ID_VARIABLE" => "id",
								"PRICE_CODE" => $arParams["PRODUCT_PRICE_CODE"],
								"USE_PRICE_COUNT" => "N",
								"SHOW_PRICE_COUNT" => "1",
								"PRICE_VAT_INCLUDE" => "Y",
								"BASKET_URL" => "/personal/basket.php",
								"USE_PRODUCT_QUANTITY" => "N",
								"PRODUCT_QUANTITY_VARIABLE" => "undefined",
								"ADD_PROPERTIES_TO_BASKET" => "Y",
								"PRODUCT_PROPS_VARIABLE" => "prop",
								"PARTIAL_PRODUCT_PROPERTIES" => "N",
								"PRODUCT_PROPERTIES" => array(
								),
								"PAGER_TEMPLATE" => "round",
								"DISPLAY_TOP_PAGER" => "N",
								"DISPLAY_BOTTOM_PAGER" => "N",
								"PAGER_TITLE" => GetMessage("CATALOG_ELEMENT_ACCEESSORIES"),
								"PAGER_SHOW_ALWAYS" => "N",
								"PAGER_DESC_NUMBERING" => "N",
								"PAGER_DESC_NUMBERING_CACHE_TIME" => "36000",
								"PAGER_SHOW_ALL" => "N",
								"PAGER_BASE_LINK_ENABLE" => "N",
								"SET_STATUS_404" => "N",
								"SHOW_404" => "N",
								"MESSAGE_404" => ""
							),
							false
						);?>
					</div>
				<?endif;?>
		        <?if(isset($arResult["REVIEWS"])):?>
		        	<div id="catalogReviews">
				        <div class="heading"><?=GetMessage("REVIEW")?> (<?=count($arResult["REVIEWS"])?>) <?if($arParams["SHOW_REVIEW_FORM"]):?><a href="#" class="reviewAddButton"><?=GetMessage("REVIEWS_ADD")?></a><?endif;?><div class="ratingContainer"><div class="label"><?=GetMessage("RATING_PRODUCT")?> </div><div class="rating"><i class="m" style="width:<?=($arResult["PROPERTIES"]["RATING"]["VALUE"] * 100 / 5)?>%"></i><i class="h"></i></div></div></div>
				        <ul id="reviews">
				            <?foreach($arResult["REVIEWS"] as $i => $arReview):?>
				                <li class="reviewItem<?if($i > 2):?> hide<?endif?>">
				                    <div class="reviewTable">
				                    	<div class="reviewColumn">
				                    		<div class="reviewDate">
						                        <div class="label"><?=GetMessage("REVIEWS_DATE")?></div> <?=FormatDate(array(
						                        "tommorow" => "tommorow",
						                        "today" => "today",  
						                        "yesterday" => "yesterday", 
						                        "d" => 'j F',  
						                         "" => 'j F Y',  
						                        ), MakeTimeStamp($arReview["DATE_CREATE"], "DD.MM.YYYY HH:MI:SS"));
						                        ?>			                    			
				                    		</div>
					                    	<div class="reviewName">
					                    		<div class="label"><?=GetMessage("REVIEWS_AUTHOR")?></div> <?=$arReview["PROPERTY_NAME_VALUE"]?>
					                    	</div>
							                <div class="reviewRating">
							                    <span class="rating"><i class="m" style="width:<?=($arReview["PROPERTY_RATING_VALUE"] * 100 / 5)?>%"></i><i class="h"></i></span>
							                </div>				                
							            </div>
				                    	<div class="reviewColumn">
	                		                <?if(!empty($arReview["~PROPERTY_DIGNITY_VALUE"])):?>
		                		                <div class="advantages">
								                    <span class="label"><?=GetMessage("DIGNIFIED")?> </span>
								                    <p><?=$arReview["~PROPERTY_DIGNITY_VALUE"]?></p>
								                </div>
							                <?endif;?>
							                <?if(!empty($arReview["~PROPERTY_SHORTCOMINGS_VALUE"])):?>
								                <div class="limitations">
								                    <span class="label"><?=GetMessage("FAULTY")?> </span>
								                    <p><?=$arReview["~PROPERTY_SHORTCOMINGS_VALUE"]?></p>
								                </div>
							                <?endif;?>
							                <?if(!empty($arReview["~DETAIL_TEXT"])):?>
								                <div class="impressions"> 
								                    <span class="label"><?=GetMessage("IMPRESSION")?></span>
								                    <p><?=$arReview["~DETAIL_TEXT"]?></p>
								                </div>
							                <?endif;?>
				                    		<div class="controls">
						                        <span><?=GetMessage("REVIEWSUSEFUL")?></span>
						                        <a href="#" class="good" data-id="<?=$arReview["ID"]?>"><?=GetMessage("YES")?> (<span><?=!empty($arReview["PROPERTY_GOOD_REVIEW_VALUE"]) ? $arReview["PROPERTY_GOOD_REVIEW_VALUE"] : 0 ?></span>)</a>
						                        <a href="#" class="bad" data-id="<?=$arReview["ID"]?>"><?=GetMessage("NO")?> (<span><?=!empty($arReview["PROPERTY_BAD_REVIEW_VALUE"]) ? $arReview["PROPERTY_BAD_REVIEW_VALUE"] : 0 ?></span>)</a>
						                    </div>	
				                    	</div>
				                    </div>
				                </li>
				            <?endforeach;?>
				        </ul>
				        <?if(count($arResult["REVIEWS"]) > 3):?><a href="#" id="showallReviews" data-open="N"><?=GetMessage("SHOWALLREVIEWS")?></a><?endif;?>
			      	</div>
			    <?endif;?>
		        <?if($USER->IsAuthorized()):?>
		            <?if($arParams["SHOW_REVIEW_FORM"]):?>
			            <div id="newReview">
			                <span class="heading"><?=GetMessage("ADDAREVIEW")?></span>
			                <form action="" method="GET">
			                    <div id="newRating"><ins><?=GetMessage("YOURRATING")?></ins><span class="rating"><i class="m" style="width:0%"></i><i class="h"></i></span></div>
			                    <table>
			                        <tbody>
			                            <tr>
			                                <td class="left">
				                                    <label><?=GetMessage("EXPERIENCE")?></label>
			                                    <?if(!empty($arResult["NEW_REVIEW"]["EXPERIENCE"])):?>
			                                        <ul class="usedSelect">
			                                            <?foreach ($arResult["NEW_REVIEW"]["EXPERIENCE"] as $arExp):?>
			                                                <li><a href="#" data-id="<?=$arExp["ID"]?>"><?=$arExp["VALUE"]?></a></li>
			                                            <?endforeach;?>
			                                        </ul>
			                                    <?endif;?>
			                                    <label><?=GetMessage("DIGNIFIED")?></label>
			                                    <textarea rows="10" cols="45" name="DIGNITY"></textarea>
			                                </td>
			                                <td class="right">
			                                    <label><?=GetMessage("FAULTY")?></label>
			                                    <textarea rows="10" cols="45" name="SHORTCOMINGS"></textarea> 
			                                    <label><?=GetMessage("IMPRESSION")?></label>
			                                    <textarea rows="10" cols="45" name="COMMENT"></textarea>   
			                                    <label><?=GetMessage("INTRODUCEYOURSELF")?></label>
			                                    <input type="text" name="NAME"><a href="#" class="submit" data-id="<?=$arParams["REVIEW_IBLOCK_ID"]?>"><?=GetMessage("SENDFEEDBACK")?></a>
			                                </td>
			                            </tr>
			                        </tbody>
			                    </table>
			                    <input type="hidden" name="USED" id="usedInput" value="" />
			                    <input type="hidden" name="RATING" id="ratingInput" value="0"/>
			                    <input type="hidden" name="PRODUCT_NAME" value="<?=$arResult["NAME"]?>"/>
			                    <input type="hidden" name="PRODUCT_ID" value="<?=$arResult["ID"]?>"/>
			                </form>
			            </div>
			        <?endif;?>
		        <?endif;?>
				<?if($arResult["SHOW_SIMILAR"] == "Y"):?>
		        	<div id="similar">
						<div class="heading"><?=GetMessage("CATALOG_ELEMENT_SIMILAR")?> (<?=$arResult["SIMILAR_COUNT"] <= 8 ? $arResult["SIMILAR_COUNT"] : 8?>)</div>
						<?$APPLICATION->IncludeComponent(
							"dresscode:catalog.section", 
							"squares", 
							array(
								"IBLOCK_TYPE" => $arParams["IBLOCK_TYPE"],
								"IBLOCK_ID" => $arParams["IBLOCK_ID"],
								"CONVERT_CURRENCY" => "Y",
								"CURRENCY_ID" => $arResult["EXTRA_SETTINGS"]["CURRENCY"],
								"ADD_SECTIONS_CHAIN" => "N",
								"COMPONENT_TEMPLATE" => "squares",
								"SECTION_ID" => $_REQUEST["SECTION_ID"],
								"SECTION_CODE" => "",
								"SECTION_USER_FIELDS" => array(
									0 => "",
									1 => "",
								),
								"ELEMENT_SORT_FIELD" => "rand",
								"ELEMENT_SORT_ORDER" => "asc",
								"ELEMENT_SORT_FIELD2" => "rand",
								"ELEMENT_SORT_ORDER2" => "desc",
								"FILTER_NAME" => "similarFilter",
								"INCLUDE_SUBSECTIONS" => "Y",
								"SHOW_ALL_WO_SECTION" => "Y",
								"HIDE_NOT_AVAILABLE" => $arParams["HIDE_NOT_AVAILABLE"],
								"PAGE_ELEMENT_COUNT" => "8",
								"LINE_ELEMENT_COUNT" => "3",
								"PROPERTY_CODE" => array(
									0 => "",
									1 => "",
								),
								"OFFERS_LIMIT" => "1",
								"BACKGROUND_IMAGE" => "-",
								"SECTION_URL" => "",
								"DETAIL_URL" => "",
								"SECTION_ID_VARIABLE" => "SECTION_ID",
								"SEF_MODE" => "N",
								"AJAX_MODE" => "N",
								"AJAX_OPTION_JUMP" => "N",
								"AJAX_OPTION_STYLE" => "Y",
								"AJAX_OPTION_HISTORY" => "N",
								"AJAX_OPTION_ADDITIONAL" => "undefined",
								"CACHE_TYPE" => "Y",
								"CACHE_TIME" => "36000000",
								"CACHE_GROUPS" => "Y",
								"SET_TITLE" => "Y",
								"SET_BROWSER_TITLE" => "Y",
								"BROWSER_TITLE" => "-",
								"SET_META_KEYWORDS" => "Y",
								"META_KEYWORDS" => "-",
								"SET_META_DESCRIPTION" => "Y",
								"META_DESCRIPTION" => "-",
								"SET_LAST_MODIFIED" => "N",
								"USE_MAIN_ELEMENT_SECTION" => "N",
								"CACHE_FILTER" => "Y",
								"ACTION_VARIABLE" => "action",
								"PRODUCT_ID_VARIABLE" => "id",
								"PRICE_CODE" => $arParams["PRODUCT_PRICE_CODE"],
								"USE_PRICE_COUNT" => "N",
								"SHOW_PRICE_COUNT" => "1",
								"PRICE_VAT_INCLUDE" => "Y",
								"BASKET_URL" => "/personal/basket.php",
								"USE_PRODUCT_QUANTITY" => "N",
								"PRODUCT_QUANTITY_VARIABLE" => "undefined",
								"ADD_PROPERTIES_TO_BASKET" => "Y",
								"PRODUCT_PROPS_VARIABLE" => "prop",
								"PARTIAL_PRODUCT_PROPERTIES" => "N",
								"PRODUCT_PROPERTIES" => array(
								),
								"PAGER_TEMPLATE" => "round",
								"DISPLAY_TOP_PAGER" => "N",
								"DISPLAY_BOTTOM_PAGER" => "N",
								"PAGER_TITLE" => GetMessage("CATALOG_ELEMENT_SIMILAR"),
								"PAGER_SHOW_ALWAYS" => "N",
								"PAGER_DESC_NUMBERING" => "N",
								"PAGER_DESC_NUMBERING_CACHE_TIME" => "36000",
								"PAGER_SHOW_ALL" => "N",
								"PAGER_BASE_LINK_ENABLE" => "N",
								"SET_STATUS_404" => "N",
								"SHOW_404" => "N",
								"MESSAGE_404" => ""
							),
							false
						);?>
					</div>
				<?endif;?>
				<?if($arParams["HIDE_AVAILABLE_TAB"] != "Y"):?>
					<?$APPLICATION->IncludeComponent(
						"bitrix:catalog.store.amount", 
						".default", 
						array(
							"COMPONENT_TEMPLATE" => ".default",
							"STORES" => array(
							),
							"ELEMENT_ID" => $arResult["ID"],
							"ELEMENT_CODE" => $arResult["CODE"],
							"STORE_PATH" => "/stores/#store_id#/",
							"CACHE_TYPE" => "Y",
							"CACHE_TIME" => "36000000",
							"MAIN_TITLE" => "",
							"USER_FIELDS" => array(
								0 => "",
								1 => "",
							),
							"FIELDS" => array(
								0 => "TITLE",
								1 => "ADDRESS",
								2 => "DESCRIPTION",
								3 => "PHONE",
								4 => "EMAIL",
								5 => "IMAGE_ID",
								6 => "COORDINATES",
								7 => "SCHEDULE",
								8 => "",
							),
							"SHOW_EMPTY_STORE" => "N",
							"USE_MIN_AMOUNT" => "Y",
							"SHOW_GENERAL_STORE_INFORMATION" => "N",
							"MIN_AMOUNT" => "0",
							"IBLOCK_TYPE" => "catalog",
							"IBLOCK_ID" => "",
							"OFFER_ID" => ""
						),
						false
					);?>
				<?endif;?>
				<?if(!empty($arResult["FILES"])):?>
				<div id="files">
					<div class="heading"><?=GetMessage("FILES_HEADING")?></div>
					<div class="wrap">
	 					<div class="items">
							<?foreach ($arResult["FILES"] as $ifl => $arFile):?>
								<?
									if($arFile["CONTENT_TYPE"] == "application/pdf"){
										$fileType = "Pdf";
									}elseif($arFile["CONTENT_TYPE"] == "application/msword" || $arFile["CONTENT_TYPE"] == "application/vnd.openxmlformats-officedocument.wordprocessingml.document"){
										$fileType = "Word";
									}elseif($arFile["CONTENT_TYPE"] == "image/jpeg" || $arFile["CONTENT_TYPE"] == "image/png"){
										$fileType = "Image";
									}elseif($arFile["CONTENT_TYPE"] == "text/plain"){
										$fileType = "Text";
									}elseif($arFile["CONTENT_TYPE"] == "application/vnd.ms-excel" || $arFile["CONTENT_TYPE"] == "application/vnd.openxmlformats-officedocument.spreadsheetml.sheet"){
										$fileType = "Excel";
									}else{
										$fileType = "";
									}
								?>
								<div class="item">
									<div class="tb">
										<div class="tbr">
											<div class="icon">
												<a href="<?=$arFile["SRC"]?>">
													<img src="<?=SITE_TEMPLATE_PATH?>/images/file<?=$fileType?>.png" alt="<?=$arFile["PARENT_NAME"]?>">
												</a>
											</div>
											<div class="info">
												<a href="<?=$arFile["SRC"]?>" class="name" target="_blank"><span><?=$arFile["ORIGINAL_NAME"]?></span></a>
												<small class="parentName"><?=preg_replace("/\[.*\]/", "", trim($arFile["PARENT_NAME"]))?>, <?=CFile::FormatSize($arFile["FILE_SIZE"])?></small>
											</div>
										</div>
									</div>
								</div>
							<?endforeach;?>
						</div>
					</div>
				</div>
				<?endif;?>	
				<?if(!empty($arResult["VIDEO"])):?>
					<div id="video">
						<div class="heading"><?=GetMessage("VIDEO_HEADING")?></div>
						<div class="wrap">
							<div class="items sz<?=count($arResult["VIDEO"])?>">
								<?foreach ($arResult["VIDEO"] as $ivp => $videoValue):?>
									<div class="item">
										<iframe src="<?=$videoValue?>" frameborder="0" allowfullscreen></iframe>
									</div>
								<?endforeach;?>
							</div>
						</div>
					</div>
				<?endif;?>
			</div>
			<div id="elementTools" class="column">
				<div class="fixContainer">
					<?require($_SERVER["DOCUMENT_ROOT"]."/".$templateFolder."/include/right_section.php");?>
				</div>
			</div>
		</div>
	</div>
</div>
<div id="elementError">
  <div id="elementErrorContainer">
    <span class="heading"><?=GetMessage("ERROR")?></span>
    <a href="#" id="elementErrorClose"></a>
    <p class="message"></p>
    <a href="#" class="close"><?=GetMessage("CLOSE")?></a>
  </div>
</div>

<div class="cheaper-product-name"><?=$arResult["NAME"]?></div>
<?if(!empty($arParams["DISPLAY_CHEAPER"]) && $arParams["DISPLAY_CHEAPER"] == "Y"):?>
	<?$APPLICATION->IncludeComponent(
		"bitrix:form.result.new", 
		"modal", 
		array(
			"CACHE_TIME" => "3600000",
			"CACHE_TYPE" => "Y",
			"CHAIN_ITEM_LINK" => "",
			"CHAIN_ITEM_TEXT" => "",
			"EDIT_URL" => "result_edit.php",
			"IGNORE_CUSTOM_TEMPLATE" => "N",
			"LIST_URL" => "result_list.php",
			"SEF_MODE" => "N",
			"SUCCESS_URL" => "",
			"USE_EXTENDED_ERRORS" => "N",
			"WEB_FORM_ID" => $arParams["CHEAPER_FORM_ID"],
			"COMPONENT_TEMPLATE" => "modal",
			"MODAL_BUTTON_NAME" => "",
			"MODAL_BUTTON_CLASS_NAME" => "cheaper label hidden changeID".(empty($arResult["PRICE"]) || $arResult["CATALOG_AVAILABLE"] != "Y" ? " disabled" : ""),
			"VARIABLE_ALIASES" => array(
				"WEB_FORM_ID" => "WEB_FORM_ID",
				"RESULT_ID" => "RESULT_ID",
			)
		),
		false
	);?>
<?endif;?>
<script type="text/javascript">

	var CATALOG_LANG = {
		REVIEWS_HIDE: "<?=GetMessage("REVIEWS_HIDE")?>",
		REVIEWS_SHOW: "<?=GetMessage("REVIEWS_SHOW")?>",
		OLD_PRICE_LABEL: "<?=GetMessage("OLD_PRICE_LABEL")?>",
	};

	var elementAjaxPath = "<?=$templateFolder."/ajax.php"?>";

</script>
<div itemscope itemtype="http://schema.org/Product" class="microdata">
	<meta itemprop="name" content="<?=$arResult["NAME"]?>" />
	<link itemprop="url" href="<?=$arResult["DETAIL_PAGE_URL"]?>" />
	<link itemprop="image" href="<?=$arResult["IMAGES"][0]["LARGE_IMAGE"]["SRC"]?>" />
	<meta itemprop="brand" content="<?=$arResult["BRAND"]["NAME"]?>" />
	<meta itemprop="model" content="<?=$arResult["PROPERTIES"]["CML2_ARTICLE"]["VALUE"]?>" />
	<meta itemprop="productID" content="<?=$arResult["ID"]?>" />
	<meta itemprop="category" content="<?=$arResult["SECTION"]["NAME"]?>" />
	<?if(!empty(!empty($arResult["PROPERTIES"]["RATING"]["VALUE"]))):?>
		<div itemprop="aggregateRating" itemscope itemtype="http://schema.org/AggregateRating">
			<meta itemprop="ratingValue" content="<?=$arResult["PROPERTIES"]["RATING"]["VALUE"]?>">
			<meta itemprop="reviewCount" content="<?=count($arResult["REVIEWS"])?>">
		</div>
	<?endif;?>
	<div itemprop="offers" itemscope itemtype="http://schema.org/Offer">
		<meta itemprop="priceCurrency" content="<?=$arResult["EXTRA_SETTINGS"]["CURRENCY"]?>" />
		<meta itemprop="price" content="<?=$arResult["PRICE"]["DISCOUNT_PRICE"]?>" />
		<?if($arResult["CATALOG_QUANTITY"] > 0):?>
            <link itemprop="availability" href="http://schema.org/InStock">
        <?else:?>
       		<link itemprop="availability" href="http://schema.org/OutOfStock">
        <?endif;?>
	</div>
	<?if(!empty($arResult["PREVIEW_TEXT"])):?>
		<meta itemprop="description" content='<?=$arResult["PREVIEW_TEXT"]?>' />
	<?endif;?>
	<?if(empty($arResult["PREVIEW_TEXT"]) && !empty($arResult["DETAIL_TEXT"])):?>
		<meta itemprop="description" content='<?=$arResult["DETAIL_TEXT"]?>' />
	<?endif;?>
</div>

<meta property="og:title" content="<?=$arResult["NAME"]?>" />
<meta property="og:description" content="<?=htmlspecialcharsbx($arResult["PREVIEW_TEXT"])?>" />
<meta property="og:url" content="<?=$arResult["DETAIL_PAGE_URL"]?>" />
<meta property="og:type" content="website" />
<?if(!empty($arResult["IMAGES"][0]["LARGE_IMAGE"]["SRC"])):?>
	<meta property="og:image" content="<?=$arResult["IMAGES"][0]["LARGE_IMAGE"]["SRC"]?>" />
<?endif;?>

<script src="//yastatic.net/es5-shims/0.0.2/es5-shims.min.js" charset="utf-8"></script>
<script src="//yastatic.net/share2/share.js" charset="utf-8"></script>