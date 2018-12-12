<?if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) die(); ?>
<?$this->setFrameMode(true);?>
<?if(!empty($arResult)):?>
	<div id="appFastView" class="item<?if(!empty($arResult["SKU_ACTIVE_OFFER"])):?> sku<?endif;?>" data-product-id="<?=!empty($arResult["~ID"]) ? $arResult["~ID"] : $arResult["ID"]?>"<?if(!empty($arResult["SKU_INFO"])):?> data-iblock-id="<?=$arResult["SKU_INFO"]["IBLOCK_ID"]?>" data-prop-id="<?=$arResult["SKU_INFO"]["SKU_PROPERTY_ID"]?>"<?endif;?> data-product-width="<?=$arParams["PICTURE_WIDTH"]?>" data-product-height="<?=$arParams["PICTURE_HEIGHT"]?>" data-hide-measure="<?=$arParams["HIDE_MEASURES"]?>" data-cast-func="fastViewSku" data-change-prop="fast-view" data-more-pictures="Y" data-price-code="<?=implode("||", $arParams["PRODUCT_PRICE_CODE"])?>">
		<div class="appFastViewContainer">
			<div class="appFastViewHeading"><?=GetMessage("FAST_VIEW_HEADING")?> <a href="#" class="appFastViewExit"></a></div>
			<div class="appFastViewColumnContainer">
				<div class="appFastViewPictureColumn">
					<?if(!empty($arResult["PROPERTIES"]["OFFERS"]["VALUE"])):?>
						<div class="markerContainer">
							<?foreach ($arResult["PROPERTIES"]["OFFERS"]["VALUE"] as $ifv => $marker):?>
							    <div class="marker" style="background-color: <?=strstr($arResult["PROPERTIES"]["OFFERS"]["VALUE_XML_ID"][$ifv], "#") ? $arResult["PROPERTIES"]["OFFERS"]["VALUE_XML_ID"][$ifv] : "#424242"?>"><?=$marker?></div>
							<?endforeach;?>
						</div>
					<?endif;?>
					<?if(!empty($arResult["IMAGES"])):?>
						<div class="appFastViewPictureSlider">
							<div class="appFastViewPictureSliderItems">
								<?foreach ($arResult["IMAGES"] as $inm => $arNextPicture):?>
									<div class="appFastViewPictureSliderItem">
										<div class="appFastViewPictureSliderItemLayout">
											<a href="<?=$arResult["DETAIL_PAGE_URL"]?>" class="appFastViewPictureSliderItemLink" data-loupe-picture="<?=$arNextPicture["SUPER_LARGE_PICTURE"]["SRC"]?>">
												<img src="<?=$arNextPicture["LARGE_PICTURE"]["SRC"]?>" class="appFastViewPictureSliderItemPicture" alt="">
											</a>
										</div>
									</div>
								<?endforeach;?>
							</div>
						</div>
						<div class="appFastViewPictureCarousel">
							<div class="appFastViewPictureCarouselItems">
								<?foreach ($arResult["IMAGES"] as $inm => $arNextPicture):?>
									<div class="appFastViewPictureCarouselItem">
										<a href="#" class="appFastViewPictureCarouselItemLink"><img src="<?=$arNextPicture["SMALL_PICTURE"]["SRC"]?>" class="appFastViewPictureCarouselItemPicture" alt=""></a>
									</div>
								<?endforeach;?>
							</div>
							<a href="#" class="appFastViewPictureCarouselLeftButton"></a>
							<a href="#" class="appFastViewPictureCarouselRightButton"></a>
						</div>
					<?endif;?>
				</div>
				<div class="appFastViewDescriptionColumn">
					<div class="appFastViewDescriptionColumnContainer">
						<div class="appFastViewProductHeading"><a href="<?=$arResult["DETAIL_PAGE_URL"]?>" class="appFastViewProductHeadingLink"><span class="appFastViewProductHeadingLinkLayout"><?=$arResult["NAME"]?></span></a></div>
						<?if(!empty($arResult["SKU_ACTIVE_OFFER"])):?>
							<?if(!empty($arResult["SKU_PROPERTIES"]) && $level = 1):?>
								<div class="appFastSkuProductProperties">
									<div class="appFastSkuProductPropertiesHeading"><?=GetMessage("FAST_VIEW_SKU_PROPERTIES_TITLE")?></div>
									<?foreach ($arResult["SKU_PROPERTIES"] as $propName => $arNextProp):?>
										<?if(!empty($arNextProp["VALUES"])):?>
											<div class="skuProperty" data-name="<?=$propName?>" data-level="<?=$level++?>" data-highload="<?=$arNextProp["HIGHLOAD"]?>">
												<div class="skuPropertyName"><?=$arNextProp["NAME"]?>:</div>
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
								</div>
							<?endif;?>
						<?endif;?>
						<div class="changeProperties">
							<?$APPLICATION->IncludeComponent(
								"dresscode:catalog.properties.list", 
								"fast-view", 
								array(
									"PRODUCT_ID" => $arResult["ID"],
									"COUNT_PROPERTIES" => 20
								),
								false
							);?>
						</div>
						<div class="appFastViewDescription<?if(!empty($arResult["PREVIEW_TEXT"])):?> visible<?endif;?>">
							<div class="appFastViewDescriptionHeading"><?=GetMessage("FAST_VIEW_DESCRIPTION_TITLE")?></div>
							<div class="appFastViewDescriptionText"><?if(!empty($arResult["PREVIEW_TEXT"])):?><?=$arResult["PREVIEW_TEXT"]?><?endif;?></div>
						</div>
						<a href="<?=$arResult["DETAIL_PAGE_URL"]?>" class="appFastViewMoreLink"><?=GetMessage("FAST_VIEW_PRODUCT_MORE_LINK")?></a>
					</div>
				</div>
				<div class="appFastViewInformationColumn">
					<div class="article<?if(empty($arResult["PROPERTIES"]["CML2_ARTICLE"]["VALUE"])):?> hidden<?endif;?>">
						<?=GetMessage("FAST_VIEW_ARTICLE_LABEL")?> <span class="changeArticle" data-first-value="<?=$arResult["PROPERTIES"]["CML2_ARTICLE"]["VALUE"]?>"><?=$arResult["PROPERTIES"]["CML2_ARTICLE"]["VALUE"]?></span>
					</div>
					<?if(!empty($arResult["PRICE"])):?>
						<?if($arResult["COUNT_PRICES"] > 1):?>
							<a href="#" data-id="<?=$arResult["ID"]?>" class="price getPricesWindow" data-fixed="Y">
								<span class="priceIcon"></span><?=CCurrencyLang::CurrencyFormat($arResult["PRICE"]["DISCOUNT_PRICE"], $arResult["CURRENCY"], true)?>
								<?if($arParams["HIDE_MEASURES"] != "Y" && !empty($arResult["MEASURES"][$arResult["CATALOG_MEASURE"]]["SYMBOL_RUS"])):?>
									<span class="measure"> / <?=$arResult["MEASURES"][$arResult["CATALOG_MEASURE"]]["SYMBOL_RUS"]?></span>
								<?endif;?>
								<?if(!empty($arResult["PRICE"]["DISCOUNT"])):?>
									<span class="oldPriceLabel"><?=GetMessage("FAST_VIEW_OLD_PRICE_LABEL")?><s class="discount"><?=CCurrencyLang::CurrencyFormat($arResult["PRICE"]["RESULT_PRICE"]["BASE_PRICE"], $arResult["CURRENCY"], true)?></s></span>
								<?endif;?>
							</a>
						<?else:?>
							<a class="price">
								<?=CCurrencyLang::CurrencyFormat($arResult["PRICE"]["DISCOUNT_PRICE"], $arResult["CURRENCY"], true)?>
								<?if($arParams["HIDE_MEASURES"] != "Y" && !empty($arResult["MEASURES"][$arResult["CATALOG_MEASURE"]]["SYMBOL_RUS"])):?>
									<span class="measure"> / <?=$arResult["MEASURES"][$arResult["CATALOG_MEASURE"]]["SYMBOL_RUS"]?></span>
								<?endif;?>
								<?if(!empty($arResult["PRICE"]["DISCOUNT"])):?>
									<span class="oldPriceLabel"><?=GetMessage("FAST_VIEW_OLD_PRICE_LABEL")?><s class="discount"><?=CCurrencyLang::CurrencyFormat($arResult["PRICE"]["RESULT_PRICE"]["BASE_PRICE"], $arResult["CURRENCY"], true)?></s></span>
								<?endif;?>
							</a>
						<?endif;?>
						<a href="#" class="addCart<?if($arResult["CATALOG_AVAILABLE"] != "Y"):?> disabled<?endif;?>" data-id="<?=$arResult["ID"]?>"><img src="<?=SITE_TEMPLATE_PATH?>/images/incart.png" alt="" class="icon"><?=GetMessage("FAST_VIEW_ADDCART_LABEL")?></a>
					<?else:?>
						<a class="price"><?=GetMessage("FAST_VIEW_REQUEST_PRICE_LABEL")?></a>
						<a href="#" class="addCart disabled requestPrice" data-id="<?=$arResult["ID"]?>"><img src="<?=SITE_TEMPLATE_PATH?>/images/request.png" alt="" class="icon"><?=GetMessage("FAST_VIEW_REQUEST_PRICE_BUTTON_LABEL")?></a>
					<?endif;?>
					<div class="catalogQtyBlock">
			            <label class="label"><?=GetMessage("FAST_VIEW_QUANTITY_LABEL")?> </label> <a href="#" class="catalogMinus"></a><input type="text" class="catalogQty" value="<?=$arResult["BASKET_STEP"]?>" data-step="<?=$arResult["BASKET_STEP"]?>" data-max-quantity="<?=$arResult["CATALOG_QUANTITY"]?>" data-enable-trace="<?=(($arResult["CATALOG_QUANTITY_TRACE"] == "Y" && $arResult["CATALOG_CAN_BUY_ZERO"] == "N") ? "Y" : "N")?>"><a href="#" class="catalogPlus"></a>
			        </div>
					<div class="secondTool">
						<?if(isset($arResult["PROPERTIES"]["RATING"]["VALUE"])):?>
							<div class="row">
								<img src="<?=SITE_TEMPLATE_PATH?>/images/reviews.png" alt="" class="icon">
								<span class="label<?if(count($arResult["REVIEWS"]) > 0):?> countReviewsTools<?endif;?>"><?=GetMessage("FAST_VIEW_REVIEWS_LABEL")?></span>
								<div class="rating">
									<i class="m" style="width:<?=($arResult["PROPERTIES"]["RATING"]["VALUE"] * 100 / 5)?>%"></i>
									<i class="h"></i>
								</div>
							</div>
						<?endif;?>	
						<div class="row">
							<a href="#" class="fastBack label changeID<?if(empty($arResult["PRICE"]) || $arResult["CATALOG_AVAILABLE"] != "Y"):?> disabled<?endif;?>" data-id="<?=$arResult["ID"]?>"><img src="<?=SITE_TEMPLATE_PATH?>/images/fastBack.png" alt="<?=GetMessage("FAST_VIEW_FASTBACK_LABEL")?>" class="icon"><?=GetMessage("FAST_VIEW_FASTBACK_LABEL")?></a>
						</div>
						<div class="row">
							<a href="#" class="addWishlist label" data-id="<?=!empty($arResult["~ID"]) ? $arResult["~ID"] : $arResult["ID"]?>"><img src="<?=SITE_TEMPLATE_PATH?>/images/wishlist.png" alt="<?=GetMessage("FAST_VIEW_WISHLIST_LABEL")?>" class="icon"><?=GetMessage("FAST_VIEW_WISHLIST_LABEL")?></a>
						</div>
						<div class="row">
							<a href="#" class="addCompare label changeID" data-id="<?=$arResult["ID"]?>"><img src="<?=SITE_TEMPLATE_PATH?>/images/compare.png" alt="<?=GetMessage("FAST_VIEW_COMPARE_LABEL")?>" class="icon"><?=GetMessage("FAST_VIEW_COMPARE_LABEL")?></a>
						</div>
						<div class="row">
							<?if($arResult["CATALOG_QUANTITY"] > 0):?>
								<?if(!empty($arResult["STORES"])):?>
									<a href="#" data-id="<?=$arResult["ID"]?>" class="inStock label changeAvailable getStoresWindow"><img src="<?=SITE_TEMPLATE_PATH?>/images/inStock.png" alt="<?=GetMessage("FAST_VIEW_AVAILABLE")?>" class="icon"><span><?=GetMessage("FAST_VIEW_AVAILABLE")?></span></a>
								<?else:?>
									<span class="inStock label changeAvailable"><img src="<?=SITE_TEMPLATE_PATH?>/images/inStock.png" alt="<?=GetMessage("FAST_VIEW_AVAILABLE")?>" class="icon"><span><?=GetMessage("FAST_VIEW_AVAILABLE")?></span></span>
								<?endif;?>
							<?else:?>
								<?if($arResult["CATALOG_AVAILABLE"] == "Y"):?>
									<span class="onOrder label changeAvailable"><img src="<?=SITE_TEMPLATE_PATH?>/images/onOrder.png" alt="<?=GetMessage("FAST_VIEW_ON_ORDER")?>" class="icon"><?=GetMessage("FAST_VIEW_ON_ORDER")?></span>
								<?else:?>
									<span class="outOfStock label changeAvailable"><img src="<?=SITE_TEMPLATE_PATH?>/images/outOfStock.png" alt="<?=GetMessage("FAST_VIEW_NO_AVAILABLE")?>" class="icon"><?=GetMessage("FAST_VIEW_NO_AVAILABLE")?></span>
								<?endif;?>
							<?endif;?>
						</div>	
					</div>
				</div>
			</div>
		</div>
		<script>
			var fastViewAjaxDir = "<?=$componentPath?>";
			var CATALOG_LANG = {
				FAST_VIEW_OLD_PRICE_LABEL: "<?=GetMessage("FAST_VIEW_OLD_PRICE_LABEL")?>",
			};
		</script>

		<script src="<?=$templateFolder?>/fast_script.js"></script>
	</div>
<?endif;?>
