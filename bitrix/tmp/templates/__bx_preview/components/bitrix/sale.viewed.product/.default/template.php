<?if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();?>
<?$this->setFrameMode(true);?>
<?if(!empty($arResult)):?>
	<?$this->SetViewTarget("sale_viewed_product_view_content_tab");?><div class="item"><a href="#"><?=GetMessage("VIEW_HEADER")?></a></div><?$this->EndViewTarget();?>
	<?$OPTION_CURRENCY  = CCurrency::GetBaseCurrency();?>
	<div class="tab item">
		<div id="viewedProduct">
			<div class="wrap">
				<ul class="slideBox productList">
					<?foreach($arResult["ITEMS"] as $arElement):?>
						<li>
							<div class="item product">						
								<div class="tabloid">
									<?if(!empty($arElement["PROPERTIES"]["OFFERS"]["VALUE"])):?>
										<div class="markerContainer">
											<?foreach ($arElement["PROPERTIES"]["OFFERS"]["VALUE"] as $ifv => $marker):?>
											    <div class="marker" style="background-color: <?=strstr($arElement["PROPERTIES"]["OFFERS"]["VALUE_XML_ID"][$ifv], "#") ? $arElement["PROPERTIES"]["OFFERS"]["VALUE_XML_ID"][$ifv] : "#424242"?>"><?=$marker?></div>
											<?endforeach;?>
										</div>
									<?endif;?>
									<?if(isset($arElement["PROPERTIES"]["RATING"]["VALUE"])):?>
									    <div class="rating">
									      <i class="m" style="width:<?=($arElement["PROPERTIES"]["RATING"]["VALUE"] * 100 / 5)?>%"></i>
									      <i class="h"></i>
									    </div>
								    <?endif;?>
									<a href="<?=$arElement["DETAIL_PAGE_URL"]?>" class="picture">
										<img src="<?=$arElement["PICTURE"]["src"]?>" alt="<?=$arElement["NAME"]?>">
										<span class="getFastView" data-id="<?=$arElement["PRODUCT_ID"]?>"><?=GetMessage("FAST_VIEW_PRODUCT_LABEL")?></span>
									</a>
									<a href="<?=$arElement["DETAIL_PAGE_URL"]?>" class="name"><span class="middle"><?=$arElement["NAME"]?></span></a>
									<?if(!empty($arElement["ARRAY_PRICE"])):?>
										<a class="price"><?=CCurrencyLang::CurrencyFormat($arElement["ARRAY_PRICE"]["DISCOUNT_PRICE"], $OPTION_CURRENCY)?>
											<?if($arElement["ARRAY_PRICE"]["DISCOUNT_PRICE"] != $arElement["ARRAY_PRICE"]["RESULT_PRICE"]["BASE_PRICE"]):?>
												<s class="discount"><?=CCurrencyLang::CurrencyFormat($arElement["ARRAY_PRICE"]["RESULT_PRICE"]["BASE_PRICE"], $OPTION_CURRENCY)?></s>
											<?endif;?>
										</a>
									<?else:?>
										<a class="price"><?=GetMessage("REQUEST_PRICE_LABEL")?></a>
									<?endif;?>
								</div>
							</div>
						</li>
					<?endforeach;?>
				</ul>
				<a href="#" class="viewedBtnLeft"></a>
				<a href="#" class="viewedBtnRight"></a>
			</div>
		</div>
		<script>
			$("#viewedProduct").dwCarousel({
				leftButton: ".viewedBtnLeft",
				rightButton: ".viewedBtnRight",
				countElement: 5,
				resizeElement: true,
				resizeAutoParams: {
					1920: 5,
					1500: 4,
					1200: 3,
					850: 2
				}
			});
		</script>
	</div>
<?endif;?>