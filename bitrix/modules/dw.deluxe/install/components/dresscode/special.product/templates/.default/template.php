<?if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();?>
<?$this->setFrameMode(true);?>
<?if(!empty($arResult["ITEMS"]) && !empty($arResult["PROPERTY_HEADING"])):?>
	<div id="specialBlock">
		<div id="specialProduct">
			<div class="specialProductHeading"><?=$arResult["PROPERTY_HEADING"]?></div>
			<div id="specialProductSlider">
				<ul class="productList slideBox">
					<?foreach ($arResult["ITEMS"] as $inx => $arElement):?>
						<?$APPLICATION->IncludeComponent(
							"dresscode:catalog.item", 
							"special", 
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
					<?endforeach;?>
				</ul>
				<a href="#" class="specialSlideBtnLeft"></a>
				<a href="#" class="specialSlideBtnRight"></a>
			</div>
			<script type="text/javascript">
				$(document).ready(function(){
					$("#specialProductSlider").dwSlider({
						leftButton: ".specialSlideBtnLeft",
						rightButton: ".specialSlideBtnRight",
						autoMove: false,
						touch: false,
						delay: 5000,
						speed: 200
					});
				});
			</script>
		</div>
	</div>
<?endif;?>