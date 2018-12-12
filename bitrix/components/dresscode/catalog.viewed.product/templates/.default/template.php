<?if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();?>
<?$this->setFrameMode(true);?>
<?if(!empty($arResult["ITEMS"])):?>
	<?$this->SetViewTarget("sale_viewed_product_view_content_tab");?><div class="item"><a href="#"><?=GetMessage("VIEW_HEADER")?></a></div><?$this->EndViewTarget();?>
	<div class="tab item">
		<div id="viewedProduct">
			<div class="wrap">
				<ul class="slideBox productList">
					<?foreach($arResult["ITEMS"] as $arElement):?>
						<li>
							<?$APPLICATION->IncludeComponent(
								"dresscode:catalog.item", 
								"short", 
								array(
									"PICTURE_HEIGHT" => $arParams["PICTURE_HEIGHT"],
									"PICTURE_WIDTH" => $arParams["PICTURE_WIDTH"],
									"HIDE_NOT_AVAILABLE" => $arParams["HIDE_NOT_AVAILABLE"],
									"PRODUCT_PRICE_CODE" => $arParams["PRODUCT_PRICE_CODE"],
									"CONVERT_CURRENCY" => $arParams["CONVERT_CURRENCY"],
									"HIDE_MEASURES" => $arParams["HIDE_MEASURES"],
									"CURRENCY_ID" => $arParams["CURRENCY_ID"],
									"IBLOCK_TYPE" => $arParams["IBLOCK_TYPE"],
									"IBLOCK_ID" => $arParams["IBLOCK_ID"],
									"PRODUCT_ID" => $arElement["ID"],
									"CACHE_TIME" => 36000000, // not delete
									"CACHE_TYPE" => "Y", // not delete
								),
								false,
								array("HIDE_ICONS" => "Y")
							);?>
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
				countElement: 8,
				resizeElement: true,
				<?if(!empty($arParams["ADAPTIVE_VERSION"]) && $arParams["ADAPTIVE_VERSION"] == "V2"):?>
					resizeAutoParams: {
						10240: 5,
						5120: 5,
						2560: 5,
						1920: 5,
						1700: 5,
						1500: 4,
						1200: 3,
						850: 2
					}
				<?else:?>
					resizeAutoParams: {
						2560: 8,
						1920: 6,
						1700: 5,
						1500: 4,
						1200: 3,
						850: 2
					}
				<?endif;?>
			});
		</script>
	</div>
<?endif;?>