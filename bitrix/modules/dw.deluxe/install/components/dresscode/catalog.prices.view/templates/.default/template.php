<?if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) die(); ?>
<?$this->setFrameMode(true);?>
<?if(!empty($arResult["PRICES"])):?>
	<div id="appProductPriceVariant">
		<div class="priceVariantHeading"><?=GetMessage("APP_PRODUCT_PRICE_VARIANT_HEADING")?><a href="#" class="appPriceVariantExit"></a></div>
		<div class="priceVariantList<?if(!empty($arResult["EXTENTED_PRICES"]) && $arResult["EXTENTED_PRICES"] == "Y"):?> extentedPrice<?endif;?>">
			<?foreach ($arResult["PRICES"] as $inp => $arNextPrice):?>
				<?foreach ($arNextPrice as $arNextPriceVariant):?>
					<div class="priceVariantListItem">
						<div class="priceVariantListItemTable">
							<div class="priceVariantListItemRow<?if(!empty($arNextPriceVariant["INCLUDED"])):?> priceVariantListItemIncluded<?endif;?>">
								<div class="priceVariantListItemColumn"><?if(!empty($arNextPriceVariant["MIN_AVAILABLE_PRICE"])):?><span class="minAvailablePrice"><?=GetMessage("APP_PRODUCT_PRICE_VARIANT_AVAILABLE_PRICE_LABEL")?></span><?else:?><?=$arNextPriceVariant["NAME"]?><?endif;?></div>
								<div class="priceVariantListItemColumn"><?if(!empty($arNextPriceVariant["MIN_AVAILABLE_PRICE"])):?><span class="minAvailablePrice"><?endif;?><?=$arNextPriceVariant["DISCOUNT_PRICE_FORMATED"]?><?if($arNextPriceVariant["DISCOUNT_PRICE"] < $arNextPriceVariant["PRICE"]):?><s class="discount"><?=$arNextPriceVariant["PRICE_FORMATED"]?></s><?endif;?><?if(!empty($arNextPriceVariant["MIN_AVAILABLE_PRICE"])):?></span><?endif;?></div>
							</div>
						</div>
					</div>
				<?endforeach;?>
			<?endforeach;?>
		</div>
		<a href="<?=SITE_DIR?>prices-info/" class="linkMore"><?=GetMessage("APP_PRODUCT_PRICE_VARIANT_LINK_MORE")?></a>
		<link rel="stylesheet" href="<?=$templateFolder?>/ajax_styles.css" class="priceVariantStyles">
	</div>
<?endif;?>