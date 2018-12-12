<?if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();?>
<?$this->setFrameMode(true);?>
<?if(!empty($arResult)):?>
	<?
		$this->AddEditAction($arResult["ID"], $arResult["EDIT_LINK"], CIBlock::GetArrayByID($arParams["IBLOCK_ID"], "ELEMENT_EDIT"));
		$this->AddDeleteAction($arResult["ID"], $arResult["DELETE_LINK"], CIBlock::GetArrayByID($arParams["IBLOCK_ID"], "ELEMENT_DELETE"), array());
	?>
	<div class="item product sku" id="<?=$this->GetEditAreaId($arResult["ID"]);?>" data-from-cache="<?=$arResult["FROM_CACHE"]?>" data-convert-currency="<?=$arParams["CONVERT_CURRENCY"]?>" data-currency-id="<?=$arParams["CURRENCY_ID"]?>" data-product-id="<?=!empty($arResult["~ID"]) ? $arResult["~ID"] : $arResult["ID"]?>" data-iblock-id="<?=$arResult["SKU_INFO"]["IBLOCK_ID"]?>" data-prop-id="<?=$arResult["SKU_INFO"]["SKU_PROPERTY_ID"]?>" data-product-width="<?=$arParams["PICTURE_WIDTH"]?>" data-product-height="<?=$arParams["PICTURE_HEIGHT"]?>" data-hide-measure="<?=$arParams["HIDE_MEASURES"]?>" data-currency="<?=$arResult["EXTRA_SETTINGS"]["CURRENCY"]?>" data-hide-not-available="<?=$arParams["HIDE_NOT_AVAILABLE"]?>" data-price-code="<?=implode("||", $arParams["PRODUCT_PRICE_CODE"])?>">
		<div class="tabloid">
			<a href="#" class="removeFromWishlist" data-id="<?=$arResult["~ID"]?>"></a>
			<?if(!empty($arResult["PROPERTIES"]["OFFERS"]["VALUE"])):?>
				<div class="markerContainer">
					<?foreach ($arResult["PROPERTIES"]["OFFERS"]["VALUE"] as $ifv => $marker):?>
					    <div class="marker" style="background-color: <?=strstr($arResult["PROPERTIES"]["OFFERS"]["VALUE_XML_ID"][$ifv], "#") ? $arResult["PROPERTIES"]["OFFERS"]["VALUE_XML_ID"][$ifv] : "#424242"?>"><?=$marker?></div>
					<?endforeach;?>
				</div>
			<?endif;?>
			<?if(isset($arResult["PROPERTIES"]["RATING"]["VALUE"])):?>
			    <div class="rating">
			      <i class="m" style="width:<?=($arResult["PROPERTIES"]["RATING"]["VALUE"] * 100 / 5)?>%"></i>
			      <i class="h"></i>
			    </div>
		    <?endif;?>
			<a href="<?=$arResult["DETAIL_PAGE_URL"]?>" class="picture">
				<img src="<?=$arResult["PICTURE"]["src"]?>" alt="<?if(!empty($arResult["IPROPERTY_VALUES"]["ELEMENT_PREVIEW_PICTURE_FILE_ALT"])):?><?=$arResult["IPROPERTY_VALUES"]["ELEMENT_PREVIEW_PICTURE_FILE_ALT"]?><?else:?><?=$arResult["NAME"]?><?endif;?>" title="<?if(!empty($arResult["IPROPERTY_VALUES"]["ELEMENT_PREVIEW_PICTURE_FILE_TITLE"])):?><?=$arResult["IPROPERTY_VALUES"]["ELEMENT_PREVIEW_PICTURE_FILE_TITLE"]?><?else:?><?=$arResult["NAME"]?><?endif;?>">
				<span class="getFastView" data-id="<?=$arResult["ID"]?>"><?=GetMessage("FAST_VIEW_PRODUCT_LABEL")?></span>
			</a>
			<a href="<?=$arResult["DETAIL_PAGE_URL"]?>" class="name"><span class="middle"><?=$arResult["NAME"]?></span></a>
			<?if(!empty($arResult["PRICE"])):?>
				<?if($arResult["EXTRA_SETTINGS"]["COUNT_PRICES"] > 1):?>
					<a class="price getPricesWindow" data-id="<?=$arResult["ID"]?>">
						<span class="priceIcon"></span><?=CCurrencyLang::CurrencyFormat($arResult["PRICE"]["DISCOUNT_PRICE"], $arResult["EXTRA_SETTINGS"]["CURRENCY"], true)?>
						<?if($arParams["HIDE_MEASURES"] != "Y" && !empty($arResult["EXTRA_SETTINGS"]["MEASURES"][$arResult["CATALOG_MEASURE"]]["SYMBOL_RUS"])):?>
							<span class="measure"> / <?=$arResult["EXTRA_SETTINGS"]["MEASURES"][$arResult["CATALOG_MEASURE"]]["SYMBOL_RUS"]?></span>
						<?endif;?>
						<?if(!empty($arResult["PRICE"]["DISCOUNT"])):?>
							<s class="discount"><?=CCurrencyLang::CurrencyFormat($arResult["PRICE"]["RESULT_PRICE"]["BASE_PRICE"], $arResult["EXTRA_SETTINGS"]["CURRENCY"], true)?></s>
						<?endif;?>
					</a>
				<?else:?>
					<a class="price"><?=CCurrencyLang::CurrencyFormat($arResult["PRICE"]["DISCOUNT_PRICE"], $arResult["EXTRA_SETTINGS"]["CURRENCY"], true)?>
						<?if($arParams["HIDE_MEASURES"] != "Y" && !empty($arResult["EXTRA_SETTINGS"]["MEASURES"][$arResult["CATALOG_MEASURE"]]["SYMBOL_RUS"])):?>
							<span class="measure"> / <?=$arResult["EXTRA_SETTINGS"]["MEASURES"][$arResult["CATALOG_MEASURE"]]["SYMBOL_RUS"]?></span>
						<?endif;?>
						<?if(!empty($arResult["PRICE"]["DISCOUNT"])):?>
							<s class="discount"><?=CCurrencyLang::CurrencyFormat($arResult["PRICE"]["RESULT_PRICE"]["BASE_PRICE"], $arResult["EXTRA_SETTINGS"]["CURRENCY"], true)?></s>
						<?endif;?>
					</a>
				<?endif;?>
				<a href="#" class="addCart<?if($arResult["CATALOG_AVAILABLE"] != "Y"):?> disabled<?endif;?>" data-id="<?=$arResult["ID"]?>"><img src="<?=SITE_TEMPLATE_PATH?>/images/incart.png" alt="" class="icon"><?=GetMessage("ADDCART_LABEL")?></a>
			<?else:?>
				<a class="price"><?=GetMessage("REQUEST_PRICE_LABEL")?></a>
				<a href="#" class="addCart disabled requestPrice" data-id="<?=$arResult["ID"]?>"><img src="<?=SITE_TEMPLATE_PATH?>/images/request.png" alt="" class="icon"><?=GetMessage("REQUEST_PRICE_BUTTON_LABEL")?></a>
			<?endif;?>
			<div class="optional">
				<div class="row">
					<a href="#" class="fastBack label<?if(empty($arResult["PRICE"]) || $arResult["CATALOG_AVAILABLE"] != "Y"):?> disabled<?endif;?>" data-id="<?=$arResult["ID"]?>"><img src="<?=SITE_TEMPLATE_PATH?>/images/fastBack.png" alt="" class="icon"><?=GetMessage("FASTBACK_LABEL")?></a>
					<a href="#" class="addCompare label" data-id="<?=$arResult["ID"]?>"><img src="<?=SITE_TEMPLATE_PATH?>/images/compare.png" alt="" class="icon"><?=GetMessage("COMPARE_LABEL")?></a>
				</div>
				<div class="row">
					<a href="#" class="addWishlist label" data-id="<?=$arResult["~ID"]?>"><img src="<?=SITE_TEMPLATE_PATH?>/images/wishlist.png" alt="" class="icon"><?=GetMessage("WISHLIST_LABEL")?></a>
					<?if($arResult["CATALOG_QUANTITY"] > 0):?>
						<?if(!empty($arResult["EXTRA_SETTINGS"]["STORES"]) && $arResult["EXTRA_SETTINGS"]["STORES_MAX_QUANTITY"] > 0):?>
							<a href="#" data-id="<?=$arResult["ID"]?>" class="inStock label changeAvailable getStoresWindow"><img src="<?=SITE_TEMPLATE_PATH?>/images/inStock.png" alt="<?=GetMessage("AVAILABLE")?>" class="icon"><span><?=GetMessage("AVAILABLE")?></span></a>
						<?else:?>
							<span class="inStock label changeAvailable"><img src="<?=SITE_TEMPLATE_PATH?>/images/inStock.png" alt="<?=GetMessage("AVAILABLE")?>" class="icon"><span><?=GetMessage("AVAILABLE")?></span></span>
						<?endif;?>
					<?else:?>
						<?if($arResult["CATALOG_AVAILABLE"] == "Y"):?>
							<a class="onOrder label changeAvailable"><img src="<?=SITE_TEMPLATE_PATH?>/images/onOrder.png" alt="" class="icon"><?=GetMessage("ON_ORDER")?></a>
						<?else:?>
							<a class="outOfStock label changeAvailable"><img src="<?=SITE_TEMPLATE_PATH?>/images/outOfStock.png" alt="" class="icon"><?=GetMessage("NOAVAILABLE")?></a>
						<?endif;?>
					<?endif;?>
				</div>
			</div>
			<?if(!empty($arResult["SKU_OFFERS"])):?>
				<?if(!empty($arResult["SKU_PROPERTIES"]) && $level = 1):?>
					<?foreach ($arResult["SKU_PROPERTIES"] as $propName => $arNextProp):?>
						<?if(!empty($arNextProp["VALUES"])):?>
							<div class="skuProperty" data-name="<?=$propName?>" data-level="<?=$level++?>" data-highload="<?=$arNextProp["HIGHLOAD"]?>">
								<div class="skuPropertyName"><?=$arNextProp["NAME"]?></div>
								<ul class="skuPropertyList">
									<?foreach ($arNextProp["VALUES"] as $xml_id => $arNextPropValue):?>
										<li class="skuPropertyValue<?if($arNextPropValue["DISABLED"] == "Y"):?> disabled<?elseif($arNextPropValue["SELECTED"] == "Y"):?> selected<?endif;?>" data-name="<?=$propName?>" data-value="<?=$arNextPropValue["VALUE"]?>">
											<a href="#" class="skuPropertyLink">
												<?if(!empty($arNextPropValue["IMAGE"])):?>
													<img src="<?=$arNextPropValue["IMAGE"]["src"]?>" alt="">
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
		</div>
	</div>
<?endif;?>