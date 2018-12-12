<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();
$this->setFrameMode(true);?>

<?if (!empty($arResult["ITEMS"])):?>
<?
	if ($arParams["DISPLAY_TOP_PAGER"]){
		?><? echo $arResult["NAV_STRING"]; ?><?
	}
?>
	<div id="catalogLineList">
		<?foreach($arResult["ITEMS"] as $arElement):?>
			<?
				$this->AddEditAction($arElement["ID"], $arElement["EDIT_LINK"], CIBlock::GetArrayByID($arParams["IBLOCK_ID"], "ELEMENT_EDIT"));
				$this->AddDeleteAction($arElement["ID"], $arElement["DELETE_LINK"], CIBlock::GetArrayByID($arParams["IBLOCK_ID"], "ELEMENT_DELETE"), array());
				$arElement["IMAGE"] = CFile::ResizeImageGet($arElement["DETAIL_PICTURE"], array("width" => 290, "height" => 340), BX_RESIZE_IMAGE_PROPORTIONAL, false);
				if(empty($arElement["IMAGE"])){
					$arElement["IMAGE"]["src"] = SITE_TEMPLATE_PATH."/images/empty.png";
				}
			?>
			<div class="itemRow item sku" id="<?=$this->GetEditAreaId($arElement["ID"]);?>" data-product-id="<?=!empty($arElement["~ID"]) ? $arElement["~ID"] : $arElement["ID"]?>" data-iblock-id="<?=$arElement["SKU_INFO"]["IBLOCK_ID"]?>" data-prop-id="<?=$arElement["SKU_INFO"]["SKU_PROPERTY_ID"]?>" data-product-width="290" data-product-height="340" data-hide-measure="<?=$arParams["HIDE_MEASURES"]?>" data-price-code="<?=implode("||", $arParams["PRICE_CODE"])?>">
				<div class="column">
					<a href="#" class="removeFromWishlist" data-id="<?=$arElement["~ID"]?>"></a>
					<?if(!empty($arElement["PROPERTIES"]["OFFERS"]["VALUE"])):?>
						<div class="markerContainer">
							<?foreach ($arElement["PROPERTIES"]["OFFERS"]["VALUE"] as $ifv => $marker):?>
							    <div class="marker" style="background-color: <?=strstr($arElement["PROPERTIES"]["OFFERS"]["VALUE_XML_ID"][$ifv], "#") ? $arElement["PROPERTIES"]["OFFERS"]["VALUE_XML_ID"][$ifv] : "#424242"?>"><?=$marker?></div>
							<?endforeach;?>
						</div>
					<?endif;?>
					<a href="<?=$arElement["DETAIL_PAGE_URL"]?>" class="picture">
						<img src="<?=$arElement["IMAGE"]["src"]?>" alt="<?if(!empty($arElement["IPROPERTY_VALUES"]["ELEMENT_PREVIEW_PICTURE_FILE_ALT"])):?><?=$arElement["IPROPERTY_VALUES"]["ELEMENT_PREVIEW_PICTURE_FILE_ALT"]?><?else:?><?=$arElement["NAME"]?><?endif;?>" title="<?if(!empty($arElement["IPROPERTY_VALUES"]["ELEMENT_PREVIEW_PICTURE_FILE_TITLE"])):?><?=$arElement["IPROPERTY_VALUES"]["ELEMENT_PREVIEW_PICTURE_FILE_TITLE"]?><?else:?><?=$arElement["NAME"]?><?endif;?>">
						<span class="getFastView" data-id="<?=$arElement["ID"]?>"><?=GetMessage("FAST_VIEW_PRODUCT_LABEL")?></span>
					</a>
				</div>
				<div class="column">
					<a href="<?=$arElement["DETAIL_PAGE_URL"]?>" class="name"><span class="middle"><?=$arElement["NAME"]?></span></a>
					<?if(isset($arElement["PROPERTIES"]["RATING"]["VALUE"])):?>
					    <div class="rating">
					      <i class="m" style="width:<?=($arElement["PROPERTIES"]["RATING"]["VALUE"] * 100 / 5)?>%"></i>
					      <i class="h"></i>
					    </div>
				    <?endif;?>
					<?if(!empty($arElement["PREVIEW_TEXT"])):?>
						<div class="description"><?=$arElement["PREVIEW_TEXT"]?></div>
					<?endif;?>
					<?if(empty($arElement["SKU_PROPERTIES"]) && !empty($arElement["PROPERTIES"])):?>
						<table class="prop"><?$i= 0;?>
							<tbody>
								<?foreach ($arElement["DISPLAY_PROPERTIES"] as $key => $arProp):?>
									<?if(!empty($arProp["DISPLAY_VALUE"]) && $arProp["SORT"] <= 5000):?>
										<?if($i++ == 5){ $i = 0; break;	}?>
										<tr>
											<td><span><?=preg_replace("/\[.*\]/", "", $arProp["NAME"])?></span></td>
											<td>
												<?=$arProp["DISPLAY_VALUE"]?>
											</td>
										</tr>
									<?endif;?>
								<?endforeach;?>
							</tbody>
						</table>	
					<?endif;?>
					<?if(!empty($arElement["SKU_PRODUCT"])):?>
						<?if(!empty($arElement["SKU_PROPERTIES"]) && $level = 1):?>
							<?foreach ($arElement["SKU_PROPERTIES"] as $propName => $arNextProp):?>
								<?if(!empty($arNextProp["VALUES"])):?>
									<div class="skuProperty" data-name="<?=$propName?>" data-level="<?=$level++?>" data-highload="<?=$arNextProp["HIGHLOAD"]?>">
										<div class="skuPropertyName"><?=$arNextProp["NAME"]?>:</div>
										<ul class="skuPropertyList">
											<?foreach ($arNextProp["VALUES"] as $xml_id => $arNextPropValue):?>
												<li class="skuPropertyValue<?if($arNextPropValue["DISABLED"] == "Y"):?> disabled<?elseif($arNextPropValue["SELECTED"] == "Y"):?> selected<?endif;?>" data-name="<?=$propName?>" data-value="<?=$arNextPropValue["VALUE"]?>">
													<a href="#" class="skuPropertyLink">
														<?if(!empty($arNextPropValue["IMAGE"])):?>
															<img src="<?=$arNextPropValue["IMAGE"]["src"]?>" alt="">
														<?else:?>
															<?=$arNextPropValue["DISPLAY_VALUE"]?>
														<?endif;?>
													</a>
												</li>
											<?endforeach;?>
										</ul>
									</div>
								<?endif;?>
							<?endforeach;?>
						<?endif;?>
					<?endif;?>
				</div>
				<div class="column">
					<div class="resizeColumn">
						<?if(!empty($arElement["MIN_PRICE"])):?>
							<?if($arElement["COUNT_PRICES"] > 1):?>
								<a href="#" class="price getPricesWindow" data-id="<?=$arElement["ID"]?>">
									<span class="priceIcon"></span><?=$arElement["MIN_PRICE"]["PRINT_DISCOUNT_VALUE"]?>
									<?if($arParams["HIDE_MEASURES"] != "Y" && !empty($arResult["MEASURES"][$arElement["CATALOG_MEASURE"]]["SYMBOL_RUS"])):?>
										<span class="measure"> / <?=$arResult["MEASURES"][$arElement["CATALOG_MEASURE"]]["SYMBOL_RUS"]?></span>
									<?endif;?>
									<?if(!empty($arElement["MIN_PRICE"]["PRINT_DISCOUNT_DIFF"]) && $arElement["MIN_PRICE"]["PRINT_DISCOUNT_DIFF"] > 0):?>
										<s class="discount"><?=$arElement["MIN_PRICE"]["PRINT_VALUE"]?></s>
									<?endif;?>
								</a>
							<?else:?>
								<a class="price"><?=$arElement["MIN_PRICE"]["PRINT_DISCOUNT_VALUE"]?>
									<?if($arParams["HIDE_MEASURES"] != "Y" && !empty($arResult["MEASURES"][$arElement["CATALOG_MEASURE"]]["SYMBOL_RUS"])):?>
										<span class="measure"> / <?=$arResult["MEASURES"][$arElement["CATALOG_MEASURE"]]["SYMBOL_RUS"]?></span>
									<?endif;?>
									<?if(!empty($arElement["MIN_PRICE"]["PRINT_DISCOUNT_DIFF"]) && $arElement["MIN_PRICE"]["PRINT_DISCOUNT_DIFF"] > 0):?>
										<s class="discount"><?=$arElement["MIN_PRICE"]["PRINT_VALUE"]?></s>
									<?endif;?>
								</a>
							<?endif;?>
						<?else:?>
							<a class="price"><?=GetMessage("REQUEST_PRICE_LABEL")?></a>
						<?endif;?>
					</div>
					<div class="resizeColumn">
						<?if(!empty($arElement["MIN_PRICE"])):?>
							<a href="#" class="addCart<?if($arElement["CAN_BUY"] === false || $arElement["CAN_BUY"] === "N"):?> disabled<?endif;?>" data-id="<?=$arElement["ID"]?>"><img src="<?=SITE_TEMPLATE_PATH?>/images/incart.png" alt="" class="icon"><?=GetMessage("ADDCART_LABEL")?></a>
						<?else:?>
							<a href="#" class="addCart disabled requestPrice" data-id="<?=$arElement["ID"]?>"><img src="<?=SITE_TEMPLATE_PATH?>/images/request.png" alt="" class="icon"><?=GetMessage("REQUEST_PRICE_BUTTON_LABEL")?></a>
						<?endif;?>
					</div>
					<div class="resizeColumn last">
						<div class="optional">
							<div class="row">
								<a href="#" class="fastBack label<?if(empty($arElement["MIN_PRICE"]) || $arElement["CAN_BUY"] === "N" || $arElement["CAN_BUY"] === false):?> disabled<?endif;?>" data-id="<?=$arElement["ID"]?>"><img src="<?=SITE_TEMPLATE_PATH?>/images/fastBack.png" alt="" class="icon"><?=GetMessage("FASTBACK_LABEL")?></a>
								<a href="#" class="addWishlist label" data-id="<?=$arElement["~ID"]?>"><img src="<?=SITE_TEMPLATE_PATH?>/images/wishlist.png" alt="" class="icon"><?=GetMessage("WISHLIST_LABEL")?></a>
							</div>
							<div class="row">
								<a href="#" class="addCompare label" data-id="<?=$arElement["ID"]?>"><img src="<?=SITE_TEMPLATE_PATH?>/images/compare.png" alt="" class="icon"><?=GetMessage("COMPARE_LABEL")?></a>
								<?if($arElement["CATALOG_QUANTITY"] > 0):?>
									<?if(!empty($arElement["STORES"])):?>
										<a href="#" data-id="<?=$arElement["ID"]?>" class="inStock label changeAvailable getStoresWindow"><img src="<?=SITE_TEMPLATE_PATH?>/images/inStock.png" alt="<?=GetMessage("AVAILABLE")?>" class="icon"><span><?=GetMessage("AVAILABLE")?></span></a>
									<?else:?>
										<span class="inStock label changeAvailable"><img src="<?=SITE_TEMPLATE_PATH?>/images/inStock.png" alt="<?=GetMessage("AVAILABLE")?>" class="icon"><span><?=GetMessage("AVAILABLE")?></span></span>
									<?endif;?>
								<?else:?>
									<?if($arElement["CAN_BUY"] === true || $arElement["CAN_BUY"] === "Y"):?>
										<a class="onOrder label changeAvailable"><img src="<?=SITE_TEMPLATE_PATH?>/images/onOrder.png" alt="" class="icon"><?=GetMessage("ON_ORDER")?></a>
									<?else:?>
										<a class="outOfStock label changeAvailable"><img src="<?=SITE_TEMPLATE_PATH?>/images/outOfStock.png" alt="" class="icon"><?=GetMessage("NOAVAILABLE")?></a>
									<?endif;?>
								<?endif;?>
							</div>						
					</div>	
					</div>
					<?if(!empty($arElement["PROPERTIES"]["CML2_ARTICLE"]["VALUE"])):?>
						<div class="article">
							<?=GetMessage("CATALOG_ART_LABEL")?><?=$arElement["PROPERTIES"]["CML2_ARTICLE"]["VALUE"]?>
						</div>
					<?endif;?>		
				</div>
			</div>
		<?endforeach;?>
	</div>

<?
	if ($arParams["DISPLAY_BOTTOM_PAGER"]){
		?><? echo $arResult["NAV_STRING"]; ?><?
	}
?>

<?if(empty($_GET["PAGEN_1"])):?>
	<div><?=$arResult["~DESCRIPTION"]?></div>
<?endif;?>

<?else:?>
	<div id="empty">
		<div class="emptyWrapper">
			<div class="pictureContainer">
				<img src="<?=SITE_TEMPLATE_PATH?>/images/emptyFolder.png" alt="<?=GetMessage("EMPTY_HEADING")?>" class="emptyImg">
			</div>
			<div class="info">
				<h3><?=GetMessage("EMPTY_HEADING")?></h3>
				<p><?=GetMessage("EMPTY_TEXT")?></p>
				<a href="<?=SITE_DIR?>" class="back"><?=GetMessage("MAIN_PAGE")?></a>
			</div>
		</div>
		<?$APPLICATION->IncludeComponent("bitrix:menu", "emptyMenu", Array(
			"ROOT_MENU_TYPE" => "left",
				"MENU_CACHE_TYPE" => "N",
				"MENU_CACHE_TIME" => "3600",
				"MENU_CACHE_USE_GROUPS" => "Y",
				"MENU_CACHE_GET_VARS" => "",
				"MAX_LEVEL" => "1",
				"CHILD_MENU_TYPE" => "left",
				"USE_EXT" => "Y",
				"DELAY" => "N",
				"ALLOW_MULTI_SELECT" => "N",
			),
			false
		);?>
	</div>
<?endif;?>