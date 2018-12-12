<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();
$this->setFrameMode(true);?>
<?if (!empty($arResult["ITEMS"])):?>
	<?$this->SetViewTarget("catalog_top_view_content_tab");?><div class="item"><a href="#"><?=GetMessage("TOP_PRODUCT_HEADER")?></a></div><?$this->EndViewTarget();?>
	<div class="tab item">
		<div id="topProduct">
			<div class="wrap">
				<ul class="slideBox productList">
					<?foreach ($arResult["ITEMS"] as $index => $arElement):?>
						<?
							$this->AddEditAction($arElement["ID"], $arElement["EDIT_LINK"], CIBlock::GetArrayByID($arElement["IBLOCK_ID"], "ELEMENT_EDIT"));
							$this->AddDeleteAction($arElement["ID"], $arElement["DELETE_LINK"], CIBlock::GetArrayByID($arElement["IBLOCK_ID"], "ELEMENT_DELETE"), array("CONFIRM" => GetMessage('CT_BNL_ELEMENT_DELETE_CONFIRM')));
							$arElement["IMAGE"] = CFile::ResizeImageGet($arElement["DETAIL_PICTURE"], array("width" => 240, "height" => 200), BX_RESIZE_IMAGE_PROPORTIONAL, false);
							if(empty($arElement["IMAGE"])){
								$arElement["IMAGE"]["src"] = SITE_TEMPLATE_PATH."/images/empty.png";
							}
						?>
						<li>
							<div class="item product" data-price-code="<?=implode("||", $arParams["PRICE_CODE"])?>">
						
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
										<img src="<?=(!empty($arElement["IMAGE"]["src"]) ? $arElement["IMAGE"]["src"] : SITE_TEMPLATE_PATH.'/images/empty.png')?>" alt="<?=$arElement["NAME"]?>">
										<span class="getFastView" data-id="<?=$arElement["ID"]?>"><?=GetMessage("FAST_VIEW_PRODUCT_LABEL")?></span>
									</a>
									<a href="<?=$arElement["DETAIL_PAGE_URL"]?>" class="name"><span class="middle"><?=$arElement["NAME"]?></span></a>
									<?if(!empty($arElement["MIN_PRICE"])):?>
										<a class="price"><?=$arElement["MIN_PRICE"]["PRINT_DISCOUNT_VALUE"]?>
											<?if(!empty($arElement["MIN_PRICE"]["PRINT_DISCOUNT_DIFF"]) && $arElement["MIN_PRICE"]["PRINT_DISCOUNT_DIFF"] > 0):?>
												<s class="discount"><?=$arElement["MIN_PRICE"]["PRINT_VALUE"]?></s>
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
				resizeAutoParams: {
					1920: 6,
					1700: 5,
					1500: 4,
					1200: 3,
					850: 2
				}
			});
		</script>
	</div>
<?endif;?>