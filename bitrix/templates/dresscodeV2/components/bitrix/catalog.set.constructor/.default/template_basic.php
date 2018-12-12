<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();
$this->setFrameMode(true);
?>
<?if(!empty($arResult["SET_ITEMS"]["DEFAULT"])):?>
	<div id="set">
		<span class="heading"><?=GetMessage("SNT_HEADING")?></span>

			<div class="setList">
				<div class="general setElement setMainElement changeID" data-quantity="<?=$arResult["BASKET_QUANTITY"][$arResult["ELEMENT"]["ID"]]?>" data-id="<?=$arResult["ELEMENT"]["ID"]?>">
					<div class="wrap">
						<a href="<?=$arResult["ELEMENT"]["DETAIL_PAGE_URL"]?>" class="picture changePicture" target="_blank">
							<img src="<?=$arResult["ELEMENT"]["DETAIL_PICTURE"]["src"]?>" alt="<?=$arResult["ELEMENT"]["NAME"]?>">
						</a>
						<a href="<?=$arResult["ELEMENT"]["DETAIL_PAGE_URL"]?>" class="name changeName" target="_blank"><span class="middle"><?=$arResult["ELEMENT"]["NAME"]?></span></a>
						<span class="price changePriceSet"><?=$arResult["ELEMENT"]["PRICE_PRINT_DISCOUNT_VALUE"]?><?if(!empty($arResult["ELEMENT"]["PRICE_DISCOUNT_DIFFERENCE_VALUE"])):?><s class="discount"><?=$arResult["ELEMENT"]["PRICE_PRINT_VALUE"]?></s><?endif;?></span>
					</div>
				</div>
				<?foreach ($arResult["SET_ITEMS"]["DEFAULT"] as $i => $arElement):?>
					<?if($arElement["ENABLED"]):?>
						<?
							$this->AddEditAction($arElement['ID'], $arElement['EDIT_LINK'], CIBlock::GetArrayByID($arElement["ELEMENT_INFO"]["IBLOCK_ID"], "ELEMENT_EDIT"));
							$this->AddDeleteAction($arElement['ID'], $arElement['DELETE_LINK'], CIBlock::GetArrayByID($arElement["ELEMENT_INFO"]["IBLOCK_ID"], "ELEMENT_DELETE"), array());
						?>
						<div class="setElement setMainElement" data-price="<?=$arElement["PRICE_DISCOUNT_VALUE"]?>" data-discount="<?=$arElement["PRICE_DISCOUNT_DIFFERENCE_VALUE"]?>" data-quantity="<?=$arResult["BASKET_QUANTITY"][$arElement["ID"]]?>" data-id="<?=$arElement["ID"]?>" id="<?=$this->GetEditAreaId($arElement['ID']);?>">
								<span class="sCheck" data-id="<?=$arElement["ID"]?>"></span>
							<div class="wrap">
								<a href="<?=$arElement["DETAIL_PAGE_URL"]?>" class="picture" target="_blank">
									<img src="<?=$arElement["DETAIL_PICTURE"]["src"]?>" alt="<?=$arElement["NAME"]?>">
								</a>
								<a href="<?=$arElement["DETAIL_PAGE_URL"]?>" class="name" target="_blank"><span class="middle"><?=$arElement["NAME"]?></span></a>
								<span class="price"><?=$arElement["PRICE_PRINT_DISCOUNT_VALUE"]?><?if(!empty($arElement["PRICE_DISCOUNT_DIFFERENCE_VALUE"])):?><s class="discount"><?=$arElement["PRICE_PRINT_VALUE"]?></s><?endif;?></span>
							</div>
						</div>
					<?endif;?>
				<?endforeach;?>
			</div>

			<div class="wrap">
				<ul class="setTools">
					<li><span class="heading2"><?=GetMessage("SNT_PRICE")?></span></li>
					<li><span class="price"><span id="setPrice"><?=$arResult["ELEMENT"]["ALL_PRICE"]?><?if(!empty($arResult["ELEMENT"]["ALL_PRICE_DISCOUNT"])):?></span><s id="setDisnt"<?if($arResult["ELEMENT"]["ALL_PRICE"] == $arResult["ELEMENT"]["ALL_PRICE_DISCOUNT"]):?> class="hidden"<?endif;?>><?=$arResult["ELEMENT"]["ALL_PRICE_DISCOUNT"]?></s><?endif;?></span></li>
					<li class="rt"><a href="#" class="addCart multi noWindow" data-selector=".setMainElement" data-text="<?=GetMessage("SNT_ADDCART_BIG")?>"><img src="<?=SITE_TEMPLATE_PATH?>/images/incart.png" alt="" class="icon"><?=GetMessage("SNT_ADDCART_BIG")?></a></li>
					<?if(!empty($arResult["SET_ITEMS"]["OTHER"])):?><li class="hr rt"><a href="#" class="addSet" data-id="<?=$arResult["ELEMENT"]["ID"]?>"><?=GetMessage("SNT_ADDSET_BIG")?></a></li><?endif;?>
				</ul>
			</div>

		<?if(!empty($arResult["SET_ITEMS"]["OTHER"])):?>
			<div id="setWindow">
				<div class="container">
					<div class="heading3">
						<?=GetMessage("SNT_ADDSET_BIG")?>
						<a href="#" class="close"></a>
					</div>
					<table id="setWindowTable">
						<tbody>
							<tr>
								<td class="wElement">
									<div id="wProduct" class="setElement setWindowElement changeID" data-price="<?=$arResult["ELEMENT"]["PRICE_DISCOUNT_VALUE"]?>" data-discount="<?=$arResult["ELEMENT"]["PRICE_DISCOUNT_DIFFERENCE_VALUE"]?>" data-id="<?=$arResult["ELEMENT"]["ID"]?>" data-quantity="<?=$arResult["BASKET_QUANTITY"][$arResult["ELEMENT"]["ID"]]?>">
										<span class="fr"><?=GetMessage("SNT_FOR_SET")?></span>
										<a href="<?=$arResult["ELEMENT"]["DETAIL_PAGE_URL"]?>" class="picture changePicture" target="_blank">
											<img src="<?=$arResult["ELEMENT"]["DETAIL_PICTURE"]["src"]?>" alt="<?=$arResult["ELEMENT"]["NAME"]?>">
										</a>
										<a href="<?=$arResult["ELEMENT"]["DETAIL_PAGE_URL"]?>" class="name changeName" target="_blank"><span class="middle"><?=$arResult["ELEMENT"]["NAME"]?></span></a>
										<span class="price changePriceSet"><?=$arResult["ELEMENT"]["PRICE_PRINT_DISCOUNT_VALUE"]?><?if(!empty($arResult["ELEMENT"]["PRICE_DISCOUNT_DIFFERENCE_VALUE"])):?><s class="discount"><?=$arResult["ELEMENT"]["PRICE_PRINT_VALUE"]?></s><?endif;?></span>
									</div>
								</td>
								<td class="wList">
									<div id="setCarousel">
										<div class="wp">
											<ul class="productList slideBox">
												<?foreach (array_merge($arResult["SET_ITEMS"]["DEFAULT"], $arResult["SET_ITEMS"]["OTHER"]) as $i => $arElement):?>
													<?if($arElement["ENABLED"]):?>
														<?
															$this->AddEditAction($arElement['ID'], $arElement['EDIT_LINK'], CIBlock::GetArrayByID($arElement["ELEMENT_INFO"]["IBLOCK_ID"], "ELEMENT_EDIT"));
															$this->AddDeleteAction($arElement['ID'], $arElement['DELETE_LINK'], CIBlock::GetArrayByID($arElement["ELEMENT_INFO"]["IBLOCK_ID"], "ELEMENT_DELETE"), array());
														?>
														<li class="setElement setWindowElement<?if($arElement["TYPE"] === "OTHER"):?> disabled<?endif;?>" data-price="<?=$arElement["PRICE_DISCOUNT_VALUE"]?>" data-discount="<?=$arElement["PRICE_DISCOUNT_DIFFERENCE_VALUE"]?>" data-id="<?=$arElement["ID"]?>" data-quantity="<?=$arResult["BASKET_QUANTITY"][$arElement["ID"]]?>" id="<?=$this->GetEditAreaId($arElement['ID']);?>">
															<span class="sWindowCheck<?if($arElement["TYPE"] === "OTHER"):?> disabled<?endif;?>" data-id="<?=$arElement["ID"]?>"></span>
															<div class="wrap">
																<a href="<?=$arElement["DETAIL_PAGE_URL"]?>" class="picture" target="_blank">
																	<img src="<?=$arElement["DETAIL_PICTURE"]["src"]?>" alt="<?=$arElement["NAME"]?>">
																</a>
																<a href="<?=$arElement["DETAIL_PAGE_URL"]?>" class="name" target="_blank"><span class="middle"><?=$arElement["NAME"]?></span></a>
																<span class="price"><?=$arElement["PRICE_PRINT_DISCOUNT_VALUE"]?><?if(!empty($arElement["PRICE_DISCOUNT_DIFFERENCE_VALUE"])):?><s class="discount"><?=$arElement["PRICE_PRINT_VALUE"]?></s><?endif;?></span>
															</div>
														</li>
													<?endif;?>
												<?endforeach;?>
											</ul>
										</div>
									 	<a href="#" class="setBtnLeft"></a>
	  									<a href="#" class="setBtnRight"></a>
									</div>
								</td>
							</tr>
						</tbody>
					</table>
					<div id="setWindowPrice">
						<ul>
							<li>
								<span class="heading4"><?=GetMessage("SNT_SUM_PRICE")?></span>
								<span class="price"><span id="setWPrice"><?=$arResult["ELEMENT"]["ALL_PRICE"]?><?if(!empty($arResult["ELEMENT"]["ALL_PRICE_DISCOUNT"])):?></span><s id="setWDisnt" class="discount<?if($arResult["ELEMENT"]["ALL_PRICE"] == $arResult["ELEMENT"]["ALL_PRICE_DISCOUNT"]):?> hidden<?endif;?>"><?=$arResult["ELEMENT"]["ALL_PRICE_DISCOUNT"]?></s><?endif;?></span>
							</li>
							<li>
								<a href="#" class="closeWindow"><img src="<?=SITE_TEMPLATE_PATH?>/images/continue.png" alt=""><span class="text"><?=GetMessage("SNT_CONT")?></span></a>
								<?if(!empty($arResult["SET_ITEMS"]["OTHER"])):?>
									<a href="#" class="addCart multi noWindow" data-selector=".setWindowElement" data-text="<?=GetMessage("SNT_ADDCART_WINDOW")?>"><img src="/bitrix/templates/dresscode/images/incart.png" alt="" class="icon"><?=GetMessage("SNT_ADDCART_WINDOW")?></a>
								<?endif;?>
							</li>
						</ul>
					</div>
				</div>
			</div>
			<script>
				$(function(){
					$("#setCarousel").dwCarousel({
						speed: 500,
						leftButton: ".setBtnLeft",
						rightButton: ".setBtnRight",
						resizeElement: false,
						countElement: 3
					});
				});
			</script>
		<?endif;?>
	</div>
<?endif;?>