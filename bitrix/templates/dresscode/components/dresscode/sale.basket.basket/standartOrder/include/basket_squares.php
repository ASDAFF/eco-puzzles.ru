<div class="items productList">
	<?foreach ($arResult["ITEMS"] as $ix => $arElement):?>
		<?$countPos += $arElement["QUANTITY"]?>
		<div class="item product parent" data-product-iblock-id="<?=$arElement["INFO"]["IBLOCK_ID"]?>" data-id="<?=$arElement["INFO"]["ID"]?>" data-cart-id="<?=$arElement["ID"]?>">
			<div class="tabloid">
			 	<div class="topSection">
					<div class="column">
						<?if($arElement["INFO"]["CATALOG_QUANTITY"] > 0):?>
							<?if(!empty($arElement["INFO"]["STORES"])):?>
								<a href="#" data-id="<?=$arElement["INFO"]["ID"]?>" class="inStock label changeAvailable getStoresWindow"><img src="<?=SITE_TEMPLATE_PATH?>/images/inStock.png" alt="<?=GetMessage("AVAILABLE")?>" class="icon"><span><?=GetMessage("AVAILABLE")?></span></a>
							<?else:?>
								<span class="inStock label changeAvailable"><img src="<?=SITE_TEMPLATE_PATH?>/images/inStock.png" alt="<?=GetMessage("AVAILABLE")?>" class="icon"><span><?=GetMessage("AVAILABLE")?></span></span>
							<?endif;?>
						<?else:?>
							<?if(!empty($arElement["CAN_BUY"]) && $arElement["CAN_BUY"] == "Y"):?>
								<a class="onOrder label changeAvailable"><img src="<?=SITE_TEMPLATE_PATH?>/images/onOrder.png" alt="" class="icon"><?=GetMessage("ON_ORDER")?></a>
							<?else:?>
								<a class="outOfStock label changeAvailable"><img src="<?=SITE_TEMPLATE_PATH?>/images/outOfStock.png" alt="" class="icon"><?=GetMessage("NOAVAILABLE")?></a>
							<?endif;?>
						<?endif;?>
                    </div>
                    <div class="column">
						<a href="#" class="delete" data-id="<?=$arElement["ID"]?>"></a>
                    </div>
			 	</div>
				<div class="productTable">
					<div class="productColImage">
					    <a href="<?=$arElement["DETAIL_PAGE_URL"]?>" class="picture" target="_blank">
					    	<img src="<?=!empty($arElement["INFO"]["PICTURE"]["src"]) ? $arElement["INFO"]["PICTURE"]["src"] : SITE_TEMPLATE_PATH."/images/empty.png"?>" alt="<?=$arElement["NAME"]?>">
					    	<span class="getFastView" data-id="<?=$arElement["PRODUCT_ID"]?>"><?=GetMessage("FAST_VIEW_PRODUCT_LABEL")?></span>
					    </a>
					</div>
					<div class="productColText">
						<a href="<?=$arElement["DETAIL_PAGE_URL"]?>" class="name" target="_blank"><span class="middle"><?=$arElement["NAME"]?></span></a>

						<?if($arElement["INFO"]["COUNT_PRICES"] > 1):?>
							<a href="#" class="price getPricesWindow" data-id="<?=$arElement["INFO"]["ID"]?>">
								<span class="priceIcon"></span><?=FormatCurrency($arElement["PRICE"], $OPTION_CURRENCY);?>
								<?if($arParams["HIDE_MEASURES"] != "Y" && !empty($arResult["MEASURES"][$arElement["INFO"]["CATALOG_MEASURE"]]["SYMBOL_RUS"])):?>
									<span class="measure"> / <?=$arResult["MEASURES"][$arElement["INFO"]["CATALOG_MEASURE"]]["SYMBOL_RUS"]?></span>
								<?endif;?>
			  					<?=(((abs($arElement["INFO"]["OLD_PRICE"] - $arElement["PRICE"])) > 0.01) ? '<s class="discount">'.FormatCurrency($arElement["INFO"]["OLD_PRICE"], $OPTION_CURRENCY).'</s>' : '')?>
			  				</a>
						<?else:?>
							<a class="price">
								<?=FormatCurrency($arElement["PRICE"], $OPTION_CURRENCY);?>
								<?if($arParams["HIDE_MEASURES"] != "Y" && !empty($arResult["MEASURES"][$arElement["INFO"]["CATALOG_MEASURE"]]["SYMBOL_RUS"])):?>
									<span class="measure"> / <?=$arResult["MEASURES"][$arElement["INFO"]["CATALOG_MEASURE"]]["SYMBOL_RUS"]?></span>
								<?endif;?>
			  					<?=(((abs($arElement["INFO"]["OLD_PRICE"] - $arElement["PRICE"])) > 0.01) ? '<s class="discount">'.FormatCurrency($arElement["INFO"]["OLD_PRICE"], $OPTION_CURRENCY).'</s>' : '')?>
			  				</a>
						<?endif;?>
						<div class="basketQty">
							<?=GetMessage("BASKET_QUANTITY_LABEL")?> <a href="#" class="minus" data-id="<?=$arElement["ID"]?>"></a>
							<input name="qty" type="text" value="<?=doubleval($arElement["QUANTITY"])?>" class="qty" <?if($arResult["OPTION_QUANTITY_TRACE"] == "Y"):?>data-max-quantity="<?=$arElement["INFO"]["CATALOG_QUANTITY"]?>"<?endif;?> data-id="<?=$arElement["ID"]?>" data-ratio="<?=$arElement["INFO"]["ADDBASKET_QUANTITY_RATIO"]?>" />
							<a href="#" class="plus" data-id="<?=$arElement["ID"]?>"></a>
						</div>
						<span class="sum hidden" data-price="<?=doubleval($arElement["PRICE"])?>"><?=FormatCurrency($arElement["PRICE"] * doubleval($arElement["QUANTITY"]), $OPTION_CURRENCY);?> </span>
						
					</div>
				</div>
			</div>
		</div>
	<?endforeach;?>
	<div class="item product fastBayContainer">
		<div class="tb">
			<div class="tc">
				<img class="fastBayImg" src="<?=SITE_TEMPLATE_PATH?>/images/basketProductListCart.png" alt="<?=GetMessage("FAST_BUY_PRODUCT_LABEL")?>">
				<div class="fastBayLabel"><?=GetMessage("FAST_BUY_PRODUCT_LABEL")?></div>
				<div class="fastBayText"><?=GetMessage("FAST_BUY_PRODUCT_TEXT")?></div>
				<a href="#" class="show-always btn-simple btn-micro" id="fastBasketOrder"><?=GetMessage("FAST_BUY_PRODUCT_BTN_TEXT")?></a>
			</div>
		</div>
	</div>
	<div class="clear"></div>
</div>
