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
	<div class="list-item-wrap" id="<?=$this->GetEditAreaId($arResult["ID"]);?>">
		<div class="list-item">
			<div class="tb">
				<div class="image tc">
					<a href="<?=$arResult["DETAIL_PAGE_URL"]?>" class="image-container">
						<img src="<?=$arResult["PICTURE"]["src"]?>" alt="<?if(!empty($arResult["IPROPERTY_VALUES"]["ELEMENT_PREVIEW_PICTURE_FILE_ALT"])):?><?=$arResult["IPROPERTY_VALUES"]["ELEMENT_PREVIEW_PICTURE_FILE_ALT"]?><?else:?><?=$arResult["NAME"]?><?endif;?>" title="<?if(!empty($arResult["IPROPERTY_VALUES"]["ELEMENT_PREVIEW_PICTURE_FILE_TITLE"])):?><?=$arResult["IPROPERTY_VALUES"]["ELEMENT_PREVIEW_PICTURE_FILE_TITLE"]?><?else:?><?=$arResult["NAME"]?><?endif;?>">
					</a>
				</div>
				<div class="text tc">
					<a href="<?=$arResult["DETAIL_PAGE_URL"]?>" class="name theme-color-hover"><?=$arResult["NAME"]?></a>
					<div class="price-wrap">
						<?if(!empty($arResult["PRICE"])):?>
							<?if($arResult["EXTRA_SETTINGS"]["COUNT_PRICES"] > 1):?>
								<a class="price getPricesWindow" data-id="<?=$arResult["ID"]?>">
									<span class="priceIcon"></span><?=CCurrencyLang::CurrencyFormat($arResult["PRICE"]["DISCOUNT_PRICE"], $arResult["EXTRA_SETTINGS"]["CURRENCY"], true)?>
									<?if($arParams["HIDE_MEASURES"] != "Y" && !empty($arResult["EXTRA_SETTINGS"]["MEASURES"][$arResult["CATALOG_MEASURE"]]["SYMBOL_RUS"])):?>
										<span class="measure"> / <?=$arResult["EXTRA_SETTINGS"]["MEASURES"][$arResult["CATALOG_MEASURE"]]["SYMBOL_RUS"]?></span>
									<?endif;?>
									<?if(!empty($arResult["PRICE"]["DISCOUNT"])):?>
										<s class="old-price"><?=CCurrencyLang::CurrencyFormat($arResult["PRICE"]["RESULT_PRICE"]["BASE_PRICE"], $arResult["EXTRA_SETTINGS"]["CURRENCY"], true)?></s>
									<?endif;?>
								</a>
							<?else:?>
								<a class="price"><?=CCurrencyLang::CurrencyFormat($arResult["PRICE"]["DISCOUNT_PRICE"], $arResult["EXTRA_SETTINGS"]["CURRENCY"], true)?>
									<?if($arParams["HIDE_MEASURES"] != "Y" && !empty($arResult["EXTRA_SETTINGS"]["MEASURES"][$arResult["CATALOG_MEASURE"]]["SYMBOL_RUS"])):?>
										<span class="measure"> / <?=$arResult["EXTRA_SETTINGS"]["MEASURES"][$arResult["CATALOG_MEASURE"]]["SYMBOL_RUS"]?></span>
									<?endif;?>
									<?if(!empty($arResult["PRICE"]["DISCOUNT"])):?>
										<s class="old-price"><?=CCurrencyLang::CurrencyFormat($arResult["PRICE"]["RESULT_PRICE"]["BASE_PRICE"], $arResult["EXTRA_SETTINGS"]["CURRENCY"], true)?></s>
									<?endif;?>
								</a>
							<?endif;?>
						<?else:?>
							<a class="price requestPrice" data-id="<?=$arResult["ID"]?>"><?=GetMessage("REQUEST_PRICE_LABEL")?></a>
						<?endif;?>
						<?if(!empty($arResult["PRICE"])):?>
							<?if($arResult["CATALOG_AVAILABLE"] != "Y"):?>
								<?if($arResult["CATALOG_SUBSCRIBE"] == "Y"):?>
									<a href="#" class="active-link theme-color addCart subscribe" data-id="<?=$arResult["ID"]?>"><?=GetMessage("SUBSCRIBE_LABEL")?></a>
								<?else:?>
									<a href="#" class="active-link theme-color addCart disabled" data-id="<?=$arResult["ID"]?>"><?=GetMessage("ADDCART_DISABLED")?></a>
								<?endif;?>
							<?else:?>
								<a href="#" class="active-link theme-color addCart" data-id="<?=$arResult["ID"]?>"><?=GetMessage("ADDCART")?></a>
							<?endif;?>
						<?endif;?>
					</div>
				</div>
			</div>
		</div>
	</div>
<?endif;?>