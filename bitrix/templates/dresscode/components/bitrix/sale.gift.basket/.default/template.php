<?if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) die();
/** @var CBitrixComponentTemplate $this */
/** @var array $arParams */
/** @var array $arResult */
/** @global CDatabase $DB */
?>

<?$frame = $this->createFrame()->begin()?>
<?if(!empty($arResult["ITEMS"])):?>
	<div class="cartGifts">
		<div class="title"><?=GetMessage("SGB_TPL_BLOCK_TITLE_DEFAULT");?></div>
		<div class="items productList">
			<?foreach ($arResult["ITEMS"] as $inx => $arElement):?>

				<?//offers filter:?>
				<?$offersFilterId = array();?>
				<?if(!empty($arElement["OFFERS"])){
					foreach ($arElement["OFFERS"] as $ixn => $arNextOffer){
						$offersFilterId[] = $arNextOffer["ID"];
					}
				}?>

				<?//end offers filter:?>
				<?$APPLICATION->IncludeComponent(
					"dresscode:catalog.item", 
					"gift", 
					array(
						"CACHE_TIME" => $arParams["CACHE_TIME"],
						"CACHE_TYPE" => $arParams["CACHE_TYPE"],
						"HIDE_MEASURES" => $arParams["HIDE_MEASURES"],
						"HIDE_NOT_AVAILABLE" => $arParams["HIDE_NOT_AVAILABLE"],
						"IBLOCK_ID" => $arElement["IBLOCK_ID"],
						"PRODUCT_ID" => $arElement["ID"],
						"AVAILABLE_OFFERS" => $offersFilterId,
						"PICTURE_HEIGHT" => "",
						"PICTURE_WIDTH" => "",
						"PRODUCT_PRICE_CODE" => $arParams["PRODUCT_PRICE_CODE"],
						"CONVERT_CURRENCY" => $arParams["CONVERT_CURRENCY"],
						"CURRENCY_ID" => $arParams["CURRENCY_ID"],
						"REFRESH_PAGE" => "Y"
					),
					false
				);?>	
									
			<?endforeach;?>
		</div>
		<div class="clear"></div>
	</div>
<?endif;?>
<script type="text/javascript">
	var giftParams = '<?=\Bitrix\Main\Web\Json::encode(
		array(
			"HIDE_NOT_AVAILABLE" => $arParams["HIDE_NOT_AVAILABLE"],
			"PAGE_ELEMENT_COUNT" => $arParams["PAGE_ELEMENT_COUNT"],
			"LINE_ELEMENT_COUNT" => $arParams["LINE_ELEMENT_COUNT"],
			"PRODUCT_PRICE_CODE" => $arParams["PRODUCT_PRICE_CODE"],
			"CONVERT_CURRENCY" => $arParams["CONVERT_CURRENCY"],
			"HIDE_MEASURES" => $arParams["HIDE_MEASURES"],
			"CACHE_GROUPS" => $arParams["CACHE_GROUPS"],
			"CURRENCY_ID" => $arParams["CURRENCY_ID"]
		)
	);?>';
</script>
<?$frame->beginStub();?>
<?$frame->end();?>