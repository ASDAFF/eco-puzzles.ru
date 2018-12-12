<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();
$this->setFrameMode(true);?>
<?if(!empty($arResult["ITEMS"])):?>
	<div class="detail-text-wrap new-list-items-wrap">
		<?if(!empty($arParams["DISPLAY_HEADING"]) && $arParams["DISPLAY_HEADING"] == "Y"):?>
			<div class="heading"><?=GetMessage("SERVICES_LABEL")?></div>
		<?endif;?>
		<div class="new-list-items">
			<?foreach ($arResult["ITEMS"] as $index => $arElement):?>
				<?$APPLICATION->IncludeComponent(
					"dresscode:catalog.item", 
					"service", 
					array(
						"CACHE_TIME" => $arParams["CACHE_TIME"],
						"CACHE_TYPE" => $arParams["CACHE_TYPE"],
						"HIDE_MEASURES" => $arParams["HIDE_MEASURES"],
						"HIDE_NOT_AVAILABLE" => $arParams["HIDE_NOT_AVAILABLE"],
						"IBLOCK_ID" => $arParams["IBLOCK_ID"],
						"IBLOCK_TYPE" => $arParams["IBLOCK_TYPE"],
						"PRODUCT_ID" => $arElement["ID"],
						"PICTURE_HEIGHT" => "60",
						"PICTURE_WIDTH" => "60",
						"PRODUCT_PRICE_CODE" => $arParams["PRICE_CODE"],
						"CONVERT_CURRENCY" => $arParams["CONVERT_CURRENCY"],
						"CURRENCY_ID" => $arParams["CURRENCY_ID"]
					),
					false,
					array("HIDE_ICONS" => "Y")
				);?>
			<?endforeach;?>
		</div>
	</div>
	<?
		if ($arParams["DISPLAY_BOTTOM_PAGER"]){
			?><? echo $arResult["NAV_STRING"]; ?><?
		}
	?>
<?endif;?>