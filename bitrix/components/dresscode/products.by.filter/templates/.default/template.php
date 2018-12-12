<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();
$this->setFrameMode(true);?>
<?if (!empty($arResult["ITEMS"])):?>
	<?$this->SetViewTarget("catalog_top_view_content_tab");?><div class="item"><a href="#"><?if(!empty($arParams["COMPONENT_TITLE"])):?><?=$arParams["COMPONENT_TITLE"]?><?else:?><?=GetMessage("TOP_PRODUCT_HEADER")?><?endif;?></a></div><?$this->EndViewTarget();?>
	<div class="tab item">
		<div id="topProduct">
			<div class="wrap">
				<ul class="slideBox productList">
					<?foreach ($arResult["ITEMS"] as $index => $arElement):?>
						<li>
							<?$APPLICATION->IncludeComponent(
								"dresscode:catalog.item", 
								"short", 
								array(
									"CACHE_TIME" => $arParams["CACHE_TIME"],
									"CACHE_TYPE" => $arParams["CACHE_TYPE"],
									"HIDE_MEASURES" => $arParams["HIDE_MEASURES"],
									"HIDE_NOT_AVAILABLE" => $arParams["HIDE_NOT_AVAILABLE"],
									"IBLOCK_ID" => $arParams["IBLOCK_ID"],
									"IBLOCK_TYPE" => $arParams["IBLOCK_TYPE"],
									"PRODUCT_ID" => $arElement["ID"],
									"PICTURE_HEIGHT" => "",
									"PICTURE_WIDTH" => "",
									"PRODUCT_PRICE_CODE" => $arParams["PRODUCT_PRICE_CODE"],
									"CONVERT_CURRENCY" => $arParams["CONVERT_CURRENCY"],
									"CURRENCY_ID" => $arParams["CURRENCY_ID"]
								),
								false,
								array("HIDE_ICONS" => "Y")
							);?>		
						</li>
					<?endforeach;?>
				</ul>
				<a href="#" class="topBtnLeft"></a>
				<a href="#" class="topBtnRight"></a>
			</div>
		</div>
		<script>
			$("#topProduct").dwCarousel({
				leftButton: ".topBtnLeft",
				rightButton: ".topBtnRight",
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
