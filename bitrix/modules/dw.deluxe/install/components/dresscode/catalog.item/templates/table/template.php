<?if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();?>
<?$this->setFrameMode(true);?>
<?if(!empty($arResult)):?>
	<?
		if(!empty($arResult["PARENT_PRODUCT"]["EDIT_LINK"])){
			$this->AddEditAction($arResult["ID"], $arResult["PARENT_PRODUCT"]["EDIT_LINK"], CIBlock::GetArrayByID($arResult["PARENT_PRODUCT"]["IBLOCK_ID"], "ELEMENT_EDIT"));
			$this->AddDeleteAction($arResult["ID"], $arResult["PARENT_PRODUCT"]["DELETE_LINK"], CIBlock::GetArrayByID($arResult["PARENT_PRODUCT"]["IBLOCK_ID"], "ELEMENT_DELETE"), array("CONFIRM" => GetMessage("CT_BNL_ELEMENT_DELETE_CONFIRM")));
		}
		if(!empty($arResult["EDIT_LINK"])){
			$this->AddEditAction($arResult["ID"], $arResult["EDIT_LINK"], CIBlock::GetArrayByID($arResult["IBLOCK_ID"], "ELEMENT_EDIT"));
			$this->AddDeleteAction($arResult["ID"], $arResult["DELETE_LINK"], CIBlock::GetArrayByID($arResult["IBLOCK_ID"], "ELEMENT_DELETE"), array("CONFIRM" => GetMessage("CT_BNL_ELEMENT_DELETE_CONFIRM")));
		}
	?>
	<div class="itemRow item" id="<?=$this->GetEditAreaId($arResult["ID"]);?>" data-product-iblock-id="<?=$arParams["IBLOCK_ID"]?>" data-from-cache="<?=$arResult["FROM_CACHE"]?>" data-convert-currency="<?=$arParams["CONVERT_CURRENCY"]?>" data-currency-id="<?=$arParams["CURRENCY_ID"]?>" data-hide-not-available="<?=$arParams["HIDE_NOT_AVAILABLE"]?>" data-product-id="<?=!empty($arResult["~ID"]) ? $arResult["~ID"] : $arResult["ID"]?>" data-iblock-id="<?=$arResult["SKU_INFO"]["IBLOCK_ID"]?>" data-prop-id="<?=$arResult["SKU_INFO"]["SKU_PROPERTY_ID"]?>" data-product-width="<?=$arParams["PICTURE_WIDTH"]?>" data-product-height="<?=$arParams["PICTURE_HEIGHT"]?>" data-hide-measure="<?=$arParams["HIDE_MEASURES"]?>" data-currency="<?=$arResult["EXTRA_SETTINGS"]["CURRENCY"]?>" data-price-code="<?=implode("||", $arParams["PRODUCT_PRICE_CODE"])?>">
	<div class="column">
		<a href="<?=$arResult["DETAIL_PAGE_URL"]?>" class="picture">
			<img src="<?=$arResult["PICTURE"]["src"]?>" alt="<?if(!empty($arResult["IPROPERTY_VALUES"]["ELEMENT_PREVIEW_PICTURE_FILE_ALT"])):?><?=$arResult["IPROPERTY_VALUES"]["ELEMENT_PREVIEW_PICTURE_FILE_ALT"]?><?else:?><?=$arResult["NAME"]?><?endif;?>" title="<?if(!empty($arResult["IPROPERTY_VALUES"]["ELEMENT_PREVIEW_PICTURE_FILE_TITLE"])):?><?=$arResult["IPROPERTY_VALUES"]["ELEMENT_PREVIEW_PICTURE_FILE_TITLE"]?><?else:?><?=$arResult["NAME"]?><?endif;?>">
		</a>
	</div>
	<div class="column">
		<a href="<?=$arResult["DETAIL_PAGE_URL"]?>" class="name"><?=$arResult["NAME"]?></a>
	</div>
	<div class="column">
		<?if(isset($arResult["PROPERTIES"]["RATING"]["VALUE"])):?>
		    <div class="rating">
		      <i class="m" style="width:<?=(intval($arResult["PROPERTIES"]["RATING"]["VALUE"]) * 100 / 5)?>%"></i>
		      <i class="h"></i>
		    </div>
	    <?endif;?>
	</div>
	<div class="column">
		<?if(!empty($arResult["PROPERTIES"]["CML2_ARTICLE"]["VALUE"])):?>
			<div class="article">
				<?=GetMessage("CATALOG_ART_LABEL")?><?=$arResult["PROPERTIES"]["CML2_ARTICLE"]["VALUE"]?>
			</div>
		<?endif;?>
	</div>
	<div class="column">
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
		<?else:?>
			<a class="price"><?=GetMessage("TABLE_REQUEST_PRICE_LABEL")?></a>
		<?endif;?>
	</div>
	<div class="column">
		<?if(!empty($arResult["PRICE"])):?>
			<?if($arResult["CATALOG_AVAILABLE"] != "Y"):?>
				<?if($arResult["CATALOG_SUBSCRIBE"] == "Y"):?>
					<a href="#" class="addCart subscribe" data-id="<?=$arResult["ID"]?>"><img src="<?=SITE_TEMPLATE_PATH?>/images/subscribe.png" alt="" class="icon"><?=GetMessage("SUBSCRIBE_LABEL")?></a>
				<?else:?>
					<a href="#" class="addCart disabled" data-id="<?=$arResult["ID"]?>"><img src="<?=SITE_TEMPLATE_PATH?>/images/incart.png" alt="" class="icon"><?=GetMessage("ADDCART_LABEL")?></a>
				<?endif;?>
			<?else:?>
				<a href="#" class="addCart" data-id="<?=$arResult["ID"]?>"><img src="<?=SITE_TEMPLATE_PATH?>/images/incart.png" alt="" class="icon"><?=GetMessage("ADDCART_LABEL")?></a>	
			<?endif;?>
		<?else:?>
			<a href="#" class="addCart disabled requestPrice" data-id="<?=$arResult["ID"]?>"><img src="<?=SITE_TEMPLATE_PATH?>/images/request.png" alt="" class="icon"><?=GetMessage("TABLE_REQUEST_PRICE_BUTTON_LABEL")?></a>
		<?endif;?>
	</div>
	<div class="column">
		<div class="optional">
			<div class="row">
				<a href="#" class="fastBack label<?if(empty($arResult["PRICE"]) || $arResult["CATALOG_AVAILABLE"] != "Y"):?> disabled<?endif;?>" data-id="<?=$arResult["ID"]?>"><img src="<?=SITE_TEMPLATE_PATH?>/images/fastBack.png" alt="" class="icon"><?=GetMessage("FASTBACK_LABEL")?></a>
				<a href="#" class="addCompare label" data-id="<?=$arResult["ID"]?>"><img src="<?=SITE_TEMPLATE_PATH?>/images/compare.png" alt="" class="icon"><?=GetMessage("COMPARE_LABEL")?></a>
			</div>
		</div>
	</div>
	<div class="column">
		<div class="optional">
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
						<a class="onOrder label changeAvailable"><img src="<?=SITE_TEMPLATE_PATH?>/images/onOrder.png" alt="<?=GetMessage("ON_ORDER")?>" class="icon"><?=GetMessage("ON_ORDER")?></a>
					<?else:?>
						<a class="outOfStock label changeAvailable"><img src="<?=SITE_TEMPLATE_PATH?>/images/outOfStock.png" alt="<?=GetMessage("NOAVAILABLE")?>" class="icon"><?=GetMessage("NOAVAILABLE")?></a>
					<?endif;?>
				<?endif;?>
			</div>
		</div>
	</div>
	<div class="column out">
		<a href="#" class="removeFromWishlist" data-id="<?=$arResult["~ID"]?>"></a>
	</div>
</div>
<?endif;?>