<?if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();?>
<?$this->setFrameMode(true);?>
<?if(!empty($arResult)):?>
	<?
		$uniqID = CAjax::GetComponentID($this->__component->__name, $this->__component->__template->__name, false);
	?>
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
	<li class="slideItem<?if(empty($arResult["EXTRA_SETTINGS"]["SHOW_TIMER"])):?> noTimer<?endif;?>">
		<?if(!empty($arResult["EXTRA_SETTINGS"]["SHOW_TIMER"])):?>
			<div class="specialTimeHeading"><?=GetMessage("SPECIAL_TIME_LEFT")?></div>
			<div class="specialTime" id="timer_<?=$arResult["EXTRA_SETTINGS"]["TIMER_UNIQ_ID"];?>_<?=$uniqID?>">
				<div class="specialTimeItem">
					<div class="specialTimeItemValue timerDayValue">0</div>
					<div class="specialTimeItemlabel"><?=GetMessage("TIMER_DAY_LABEL")?></div>
				</div>
				<div class="specialTimeItem">
					<div class="specialTimeItemValue timerHourValue">0</div>
					<div class="specialTimeItemlabel"><?=GetMessage("TIMER_HOUR_LABEL")?></div>
				</div>
				<div class="specialTimeItem">
					<div class="specialTimeItemValue timerMinuteValue">0</div>
					<div class="specialTimeItemlabel"><?=GetMessage("TIMER_MINUTE_LABEL")?></div>
				</div>
				<div class="specialTimeItem">
					<div class="specialTimeItemValue timerSecondValue">0</div>
					<div class="specialTimeItemlabel"><?=GetMessage("TIMER_SECOND_LABEL")?></div>
				</div>
			</div>
		<?endif;?>
		<div class="productItem item" id="<?=$this->GetEditAreaId($arResult["ID"]);?>" data-product-iblock-id="<?=$arParams["IBLOCK_ID"]?>" data-from-cache="<?=$arResult["FROM_CACHE"]?>" data-convert-currency="<?=$arParams["CONVERT_CURRENCY"]?>" data-currency-id="<?=$arParams["CURRENCY_ID"]?>" data-product-id="<?=!empty($arResult["~ID"]) ? $arResult["~ID"] : $arResult["ID"]?>" data-iblock-id="<?=$arResult["SKU_INFO"]["IBLOCK_ID"]?>" data-prop-id="<?=$arResult["SKU_INFO"]["SKU_PROPERTY_ID"]?>" data-product-width="<?=$arParams["PICTURE_WIDTH"]?>" data-product-height="<?=$arParams["PICTURE_HEIGHT"]?>" data-hide-measure="<?=$arParams["HIDE_MEASURES"]?>" data-currency="<?=$arResult["EXTRA_SETTINGS"]["CURRENCY"]?>" data-hide-not-available="<?=$arParams["HIDE_NOT_AVAILABLE"]?>" data-price-code="<?=implode("||", $arParams["PRODUCT_PRICE_CODE"])?>">
			<a href="<?=$arResult["DETAIL_PAGE_URL"]?>" class="picture"><img src="<?=$arResult["PICTURE"]["src"]?>" alt="<?=$arResult["NAME"]?>"><span class="getFastView" data-id="<?=$arResult["ID"]?>"><?=GetMessage("FAST_VIEW_PRODUCT_LABEL")?></span></a>
			<a href="<?=$arResult["DETAIL_PAGE_URL"]?>" class="name"><span class="middle"><?=$arResult["NAME"]?></span></a>
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
				<a class="price"><?=GetMessage("REQUEST_PRICE_LABEL")?></a>
			<?endif;?>
			<a href="<?=$arResult["DETAIL_PAGE_URL"]?>" class="more" data-id="<?=$arResult["ID"]?>"><img src="<?=SITE_TEMPLATE_PATH?>/images/moreLink.png" alt="<?=GetMessage("MORE_LINK_LABEL")?>" class="icon"><?=GetMessage("MORE_LINK_LABEL")?></a>
		</div>
		<?if(!empty($arResult["PROPERTIES"]["TIMER_LOOP"]["VALUE"])):?>
			<script type="text/javascript">
				$(document).ready(function(){
					$("#timer_<?=$arResult["EXTRA_SETTINGS"]["TIMER_UNIQ_ID"];?>_<?=$uniqID?>").dwTimer({
						timerLoop: "<?=$arResult["PROPERTIES"]["TIMER_LOOP"]["VALUE"]?>",
						<?if(empty($arResult["PROPERTIES"]["TIMER_START_DATE"]["VALUE"])):?>
							startDate: "<?=MakeTimeStamp($arResult["DATE_CREATE"], "DD.MM.YYYY HH:MI:SS")?>"
						<?else:?>
							startDate: "<?=MakeTimeStamp($arResult["PROPERTIES"]["TIMER_START_DATE"]["VALUE"], "DD.MM.YYYY HH:MI:SS")?>"
						<?endif;?>
					});
				});
			</script>
		<?elseif(!empty($arResult["EXTRA_SETTINGS"]["SHOW_TIMER"]) && !empty($arResult["PROPERTIES"]["TIMER_DATE"]["VALUE"])):?>
			<script type="text/javascript">
				$(document).ready(function(){
					$("#timer_<?=$arResult["EXTRA_SETTINGS"]["TIMER_UNIQ_ID"];?>_<?=$uniqID?>").dwTimer({
						endDate: "<?=MakeTimeStamp($arResult["PROPERTIES"]["TIMER_DATE"]["VALUE"], "DD.MM.YYYY HH:MI:SS")?>"
					});
				});
			</script>
		<?endif;?>
	</li>
<?endif;?>