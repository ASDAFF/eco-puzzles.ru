	<div class="mainTool">
		<?if(!empty($arResult["PRICE"])):?>
			<?if($arResult["EXTRA_SETTINGS"]["COUNT_PRICES"] > 1):?>
				<a class="price changePrice getPricesWindow" data-id="<?=$arResult["ID"]?>">
					<?if(!empty($arResult["PRICE"]["DISCOUNT"])):?>
						<span class="priceBlock">
						<span class="oldPriceLabel"><s class="discount"><?=CCurrencyLang::CurrencyFormat($arResult["PRICE"]["RESULT_PRICE"]["BASE_PRICE"], $arResult["EXTRA_SETTINGS"]["CURRENCY"], true)?></s></span>
							<?if(!empty($arResult["PRICE"]["RESULT_PRICE"]["DISCOUNT"])):?>
								<span class="oldPriceLabel"><?=GetMessage("OLD_PRICE_DIFFERENCE_LABEL")?> <span class="economy"><?=CCurrencyLang::CurrencyFormat($arResult["PRICE"]["RESULT_PRICE"]["DISCOUNT"], $arResult["EXTRA_SETTINGS"]["CURRENCY"], true)?></span></span>
							<?endif;?>
						</span>
					<?endif;?>
					<span class="priceContainer"><span class="priceIcon"></span><span class="priceVal"><?=CCurrencyLang::CurrencyFormat($arResult["PRICE"]["DISCOUNT_PRICE"], $arResult["EXTRA_SETTINGS"]["CURRENCY"], true)?></span></span>
					<?if($arParams["HIDE_MEASURES"] != "Y" && !empty($arResult["EXTRA_SETTINGS"]["MEASURES"][$arResult["CATALOG_MEASURE"]]["SYMBOL_RUS"])):?>
						<span class="measure"> / <?=$arResult["EXTRA_SETTINGS"]["MEASURES"][$arResult["CATALOG_MEASURE"]]["SYMBOL_RUS"]?></span>
					<?endif;?>
					<?if(!empty($arResult["PROPERTIES"]["BONUS"]["VALUE"])):?>
						<span class="purchaseBonus"><span class="theme-color">+ <?=$arResult["PROPERTIES"]["BONUS"]["VALUE"]?></span><?=$arResult["PROPERTIES"]["BONUS"]["NAME"]?></span>
					<?endif;?>
				</a>
			<?else:?>
				<a class="price changePrice">
					<?if(!empty($arResult["PRICE"]["DISCOUNT"])):?>
						<span class="priceBlock">
							<span class="oldPriceLabel"><s class="discount"><?=CCurrencyLang::CurrencyFormat($arResult["PRICE"]["RESULT_PRICE"]["BASE_PRICE"], $arResult["EXTRA_SETTINGS"]["CURRENCY"], true)?></s></span>
							<?if(!empty($arResult["PRICE"]["RESULT_PRICE"]["DISCOUNT"])):?>
								<span class="oldPriceLabel"><?=GetMessage("OLD_PRICE_DIFFERENCE_LABEL")?> <span class="economy"><?=CCurrencyLang::CurrencyFormat($arResult["PRICE"]["RESULT_PRICE"]["DISCOUNT"], $arResult["EXTRA_SETTINGS"]["CURRENCY"], true)?></span></span>
							<?endif;?>
						</span>
					<?endif;?>
					<span class="priceContainer">
						<span class="priceVal"><?=CCurrencyLang::CurrencyFormat($arResult["PRICE"]["DISCOUNT_PRICE"], $arResult["EXTRA_SETTINGS"]["CURRENCY"], true)?></span>
						<?if($arParams["HIDE_MEASURES"] != "Y" && !empty($arResult["EXTRA_SETTINGS"]["MEASURES"][$arResult["CATALOG_MEASURE"]]["SYMBOL_RUS"])):?>
							<span class="measure"> / <?=$arResult["EXTRA_SETTINGS"]["MEASURES"][$arResult["CATALOG_MEASURE"]]["SYMBOL_RUS"]?></span>
						<?endif;?>
					</span>
					<?if(!empty($arResult["PROPERTIES"]["BONUS"]["VALUE"])):?>
						<span class="purchaseBonus"><span class="theme-color">+ <?=$arResult["PROPERTIES"]["BONUS"]["VALUE"]?></span><?=$arResult["PROPERTIES"]["BONUS"]["NAME"]?></span>
					<?endif;?>
				</a>
			<?endif;?>
		<?else:?>
			<a class="price changePrice"><?=GetMessage("REQUEST_PRICE_LABEL")?></a>
		<?endif;?>
		<div class="columnRowWrap">
			<div class="row columnRow">
				<?if(!empty($arResult["PRICE"])):?>
					<?if($arResult["CATALOG_AVAILABLE"] != "Y"):?>
						<?if($arResult["CATALOG_SUBSCRIBE"] == "Y"):?>
							<a href="#" class="addCart subscribe changeID changeQty changeCart" data-id="<?=$arResult["ID"]?>" data-quantity="<?=$arResult["EXTRA_SETTINGS"]["BASKET_STEP"]?>"><img src="<?=SITE_TEMPLATE_PATH?>/images/subscribe.png" alt="<?=GetMessage("SUBSCRIBE_LABEL")?>" class="icon"><?=GetMessage("SUBSCRIBE_LABEL")?></a>
						<?else:?>
							<a href="#" class="addCart changeID changeQty changeCart disabled" data-id="<?=$arResult["ID"]?>" data-quantity="<?=$arResult["EXTRA_SETTINGS"]["BASKET_STEP"]?>"><img src="<?=SITE_TEMPLATE_PATH?>/images/incart.png" alt="<?=GetMessage("ADDCART_LABEL")?>" class="icon"><?=GetMessage("ADDCART_LABEL")?></a>
						<?endif;?>
					<?else:?>
						<a href="#" class="addCart changeID changeQty changeCart" data-id="<?=$arResult["ID"]?>" data-quantity="<?=$arResult["EXTRA_SETTINGS"]["BASKET_STEP"]?>"><img src="<?=SITE_TEMPLATE_PATH?>/images/incart.png" alt="<?=GetMessage("ADDCART_LABEL")?>" class="icon"><?=GetMessage("ADDCART_LABEL")?></a>
					<?endif;?>
				<?else:?>
					<a href="#" class="addCart changeID changeQty changeCart disabled requestPrice" data-id="<?=$arResult["ID"]?>" data-quantity="<?=$arResult["EXTRA_SETTINGS"]["BASKET_STEP"]?>"><img src="<?=SITE_TEMPLATE_PATH?>/images/request.png" alt="<?=GetMessage("REQUEST_PRICE_BUTTON_LABEL")?>" class="icon"><?=GetMessage("REQUEST_PRICE_BUTTON_LABEL")?></a>
				<?endif;?>
			</div>
			<div class="row columnRow">
				<a href="#" class="fastBack label changeID<?if(empty($arResult["PRICE"]) || $arResult["CATALOG_AVAILABLE"] != "Y"):?> disabled<?endif;?>" data-id="<?=$arResult["ID"]?>"><img src="<?=SITE_TEMPLATE_PATH?>/images/fastBack.png" alt="<?=GetMessage("FASTBACK_LABEL")?>" class="icon"><?=GetMessage("FASTBACK_LABEL")?></a>
			</div>
		</div>
	</div>
	<div class="secondTool">
		<div class="qtyBlock row">
			<img src="<?=SITE_TEMPLATE_PATH?>/images/qty.png" alt="" class="icon">
            <label class="label"><?=GetMessage("QUANTITY_LABEL")?> </label> <a href="#" class="minus"></a><input type="text" class="qty"<?if(!empty($arResult["PRICE"]["EXTENDED_PRICES"])):?> data-extended-price='<?=\Bitrix\Main\Web\Json::encode($arResult["PRICE"]["EXTENDED_PRICES"])?>'<?endif;?> value="<?=$arResult["EXTRA_SETTINGS"]["BASKET_STEP"]?>" data-step="<?=$arResult["EXTRA_SETTINGS"]["BASKET_STEP"]?>" data-max-quantity="<?=$arResult["CATALOG_QUANTITY"]?>" data-enable-trace="<?=(($arResult["CATALOG_QUANTITY_TRACE"] == "Y" && $arResult["CATALOG_CAN_BUY_ZERO"] == "N") ? "Y" : "N")?>"><a href="#" class="plus"></a>
        </div>
		<?if(!empty($arParams["DISPLAY_CHEAPER"]) && $arParams["DISPLAY_CHEAPER"] == "Y" && !empty($arParams["CHEAPER_FORM_ID"])):?>
			<div class="row">
				<a href="#" class="cheaper label openWebFormModal<?if(empty($arResult["PRICE"]) || $arResult["CATALOG_AVAILABLE"] != "Y"):?> disabled<?endif;?>" data-id="<?=$arParams["CHEAPER_FORM_ID"]?>"><img src="<?=SITE_TEMPLATE_PATH?>/images/cheaper.png" alt="<?=GetMessage("CHEAPER_LABEL")?>" class="icon"><?=GetMessage("CHEAPER_LABEL")?></a>
			</div>
		<?endif;?>
		<?if(empty($arParams["HIDE_DELIVERY_CALC"]) || !empty($arParams["HIDE_DELIVERY_CALC"]) && $arParams["HIDE_DELIVERY_CALC"] == "N"):?>
			<div class="row">
				<a href="#" class="deliveryBtn label changeID calcDeliveryButton" data-id="<?=$arResult["ID"]?>"><img src="<?=SITE_TEMPLATE_PATH?>/images/delivery.png" alt="<?=GetMessage("DELIVERY_LABEL")?>" class="icon"><?=GetMessage("DELIVERY_LABEL")?></a>
			</div>
		<?endif;?>
		<div class="row">
			<?if($arResult["CATALOG_QUANTITY"] > 0):?>
				<?if(!empty($arResult["EXTRA_SETTINGS"]["STORES"]) && $arResult["EXTRA_SETTINGS"]["STORES_MAX_QUANTITY"] > 0):?>
					<a href="#" data-id="<?=$arResult["ID"]?>" class="inStock label eChangeAvailable getStoresWindow"><img src="<?=SITE_TEMPLATE_PATH?>/images/inStock.png" alt="<?=GetMessage("AVAILABLE")?>" class="icon"><span><?=GetMessage("AVAILABLE")?></span></a>
				<?else:?>
					<span class="inStock label eChangeAvailable"><img src="<?=SITE_TEMPLATE_PATH?>/images/inStock.png" alt="<?=GetMessage("AVAILABLE")?>" class="icon"><span><?=GetMessage("AVAILABLE")?></span></span>
				<?endif;?>
			<?else:?>
				<?if($arResult["CATALOG_AVAILABLE"] == "Y"):?>
					<a class="onOrder label eChangeAvailable"><img src="<?=SITE_TEMPLATE_PATH?>/images/onOrder.png" alt="<?=GetMessage("ON_ORDER")?>" class="icon"><?=GetMessage("ON_ORDER")?></a>
				<?else:?>
					<a class="outOfStock label eChangeAvailable"><img src="<?=SITE_TEMPLATE_PATH?>/images/outOfStock.png" alt="<?=GetMessage("CATALOG_NO_AVAILABLE")?>" class="icon"><?=GetMessage("CATALOG_NO_AVAILABLE")?></a>
				<?endif;?>
			<?endif;?>
		</div>
		<div class="row">
			<div class="ya-share-label"><?=GetMessage("SHARE_LABEL")?></div>
			<div class="ya-share2" data-services="vkontakte,facebook,odnoklassniki,moimir,twitter"></div>
		</div>
	</div>