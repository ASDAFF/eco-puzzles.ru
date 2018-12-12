<?if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();?>
<?$this->setFrameMode(true);?>
<script type="text/javascript">
	var ajaxDir = "<?=$this->GetFolder();?>";
</script>
<?if(!empty($arResult["GROUPS"])):?>

	<?if(empty($arParams["AJAX"])):?>
		<div id="homeCatalog">
			<?if(!empty($arResult["PROPERTY_ENUM"])):?>
				<div class="captionList">
					<div id="captionCarousel">
						<ul class="slideBox">
							<?foreach ($arResult["PROPERTY_ENUM"] as $ipe => $arPropEnum):?>
								<?if(!empty($arResult["GROUPS"][$ipe]["ITEMS"])):?>
									<li class="cItem">
										<div class="caption<?if($arPropEnum["SELECTED"] == "Y"):?> selected<?endif;?>"><a href="#" data-name="<?=$arPropEnum["PROP_NAME"]?>" data-group="<?=$arPropEnum["ID"]?>" data-page="1" data-sheet="N" class="getProductByGroup"><?=$arPropEnum["VALUE"]?></a></div>
									</li>
								<?endif;?>
							<?endforeach;?>
						</ul>
						<a href="#" class="captionBtnLeft"></a>
						<a href="#" class="captionBtnRight"></a>
					</div>
					<script type="text/javascript">
						$("#captionCarousel").dwCarousel({
							leftButton: ".captionBtnLeft",
							rightButton: ".captionBtnRight",
							countElement: 5,
							resizeElement: true,
							resizeAutoParams: {
								1920: 5,
								600: 4,
								500: 3,
								380: 2,
							}
						});
					</script>
				</div>
			<?endif;?>
		<?endif;?> 
			<?foreach ($arResult["GROUPS"] as $itg => $arItemsGroup):?>
				<?if(!empty($arItemsGroup["ITEMS"])):?>
					<?if(empty($arParams["AJAX"])):?>
						<div class="ajaxContainer">
					<?endif;?>
						<div class="items productList">
							<?foreach ($arItemsGroup["ITEMS"] as $index => $arElement):?>
								<?$APPLICATION->IncludeComponent(
									"dresscode:catalog.item", 
									$arParams["CATALOG_ITEM_TEMPLATE"], 
									array(
										"CACHE_TIME" => $arParams["CACHE_TIME"],
										"CACHE_TYPE" => $arParams["CACHE_TYPE"],
										"HIDE_MEASURES" => $arParams["HIDE_MEASURES"],
										"HIDE_NOT_AVAILABLE" => $arParams["HIDE_NOT_AVAILABLE"],
										"IBLOCK_ID" => $arParams["IBLOCK_ID"],
										"IBLOCK_TYPE" => $arParams["IBLOCK_TYPE"],
										"PRODUCT_ID" => $arElement["ID"],
										"PICTURE_HEIGHT" => $arParams["PICTURE_HEIGHT"],
										"PICTURE_WIDTH" => $arParams["PICTURE_WIDTH"],
										"CONVERT_CURRENCY" => $arParams["CONVERT_CURRENCY"],
										"CURRENCY_ID" => $arParams["CURRENCY_ID"],
										"PRODUCT_PRICE_CODE" => $arParams["PRODUCT_PRICE_CODE"],
									),
									false,
									array("HIDE_ICONS" => "Y")
								);?>
							<?endforeach;?>
							<?if(!empty($arResult["HIDE_LAST_ELEMENT"])):?>
								<div class="item product last">
									<a href="#" class="showMore">
										<span class="wp">
											<span class="icon">
												<img class="iconBig" src="<?=SITE_TEMPLATE_PATH?>/images/showMore.png" alt="<?=GetMessage("SHOW_MORE")?>">
												<img class="iconSmall" src="<?=SITE_TEMPLATE_PATH?>/images/showMoreSmall.png" alt="<?=GetMessage("SHOW_MORE")?>">
											</span>
											<span class="ps"><?=GetMessage("SHOW_MORE")?></span><span class="value"><?=$arParams["NEXT_ELEMENTS_COUNT"]?></span>
											<span class="small"><?=GetMessage("SHOWS")?> <?=$arParams["~ELEMENTS_COUNT"]?> <?=GetMessage("FROM")?> <?=$arResult["FIRST_ITEMS_ALL_COUNT"]?></span>
										</span>
									</a>
								</div>
							<?endif;?>
							<div class="clear"></div>
						</div>
					<?if(empty($arParams["AJAX"])):?>
						</div>
					<?endif;?>
					<?break(1);?>
				<?endif;?>
			<?endforeach;?>
	<?if(empty($arParams["AJAX"])):?>
		</div>
	<?endif;?>

	<script type="text/javascript">
		var offersProductParams = '<?=\Bitrix\Main\Web\Json::encode($arParams);?>';
	</script>

<?endif;?>	