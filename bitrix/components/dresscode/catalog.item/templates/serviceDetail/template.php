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
<div class="global-block-container">
	<div class="global-content-block">
		<div class="new-service-detail" id="<?=$this->GetEditAreaId($arResult["ID"]);?>">
			<div class="tb">
				<div class="image tc">
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
				<div class="text tc">
					<h1 class="ff-medium"><?=$APPLICATION->GetPageProperty("title")?></h1>
					<div class="detail-text-wrap">
						<div class="price-container">
							<div class="price-wrap"><?=GetMessage("SERVICE_PRICE")?> <br>
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
							</div>
							<?if(!empty($arResult["PRICE"])):?>
								<?if($arResult["CATALOG_AVAILABLE"] != "Y"):?>
									<?if($arResult["CATALOG_SUBSCRIBE"] == "Y"):?>
										<a href="#" class="btn-simple btn-medium addCart subscribe" data-id="<?=$arResult["ID"]?>"><?=GetMessage("SUBSCRIBE_LABEL")?></a>
									<?else:?>
										<a href="#" class="btn-simple btn-medium addCart disabled" data-id="<?=$arResult["ID"]?>"><?=GetMessage("SERVICE_ADDCART_DISABLED")?></a>
									<?endif;?>
								<?else:?>
									<a href="#" class="btn-simple btn-medium addCart" data-id="<?=$arResult["ID"]?>"><?=GetMessage("SERVICE_ADDCART")?></a>
								<?endif;?>
							<?endif;?>
						</div>
						<?if(!empty($arResult["DETAIL_TEXT"])):?>
							<?=$arResult["DETAIL_TEXT"]?>
						<?endif;?>
					</div>
				</div>
			</div>
		</div>
		<?if(!empty($arResult["SECTION"]["SECTION_PAGE_URL"])):?>
			<a href="<?=$arResult["SECTION"]["SECTION_PAGE_URL"]?>" class="btn-simple btn-small"><?=GetMessage("SERVICE_ELEMENT_BACK")?></a>
		<?endif;?>
	</div>
	<div class="global-information-block">
		<?$APPLICATION->IncludeComponent(
			"bitrix:main.include", 
			".default", 
			array(
				"COMPONENT_TEMPLATE" => ".default",
				"AREA_FILE_SHOW" => "sect",
				"AREA_FILE_SUFFIX" => "information_block",
				"AREA_FILE_RECURSIVE" => "Y",
				"EDIT_TEMPLATE" => ""
			),
			false
		);?>
	</div>
</div>
<?endif;?>