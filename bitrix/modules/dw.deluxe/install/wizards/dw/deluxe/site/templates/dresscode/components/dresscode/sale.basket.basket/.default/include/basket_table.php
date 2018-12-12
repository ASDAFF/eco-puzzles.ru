<table class="productTable">
	<thead>
		<tr>
			<th><?=GetMessage("TOP_IMAGE")?></th>
			<th><?=GetMessage("TOP_NAME")?></th>														
			<th><?=GetMessage("TOP_QTY")?></th>
			<th><?=GetMessage("TOP_AVAILABLE")?></th>
			<th><?=GetMessage("TOP_PRICE")?></th>
			<th><?=GetMessage("TOP_SUM")?></th>													
			<th><?=GetMessage("TOP_DELETE")?></th>
		</tr>
	</thead>
	<tbody>
		<?foreach ($arResult["ITEMS"] as $key => $arElement):?>
		<?$countPos += $arElement["QUANTITY"] ?>
			<tr class="basketItemsRow parent" data-product-iblock-id="<?=$arElement["INFO"]["IBLOCK_ID"]?>" data-id="<?=$arElement["INFO"]["ID"]?>" data-cart-id="<?=$arElement["ID"]?>"> 
				<td><a href="<?=$arElement["INFO"]["DETAIL_PAGE_URL"]?>" target="_blank" class="pic" target="_blank"><img src="<?=!empty($arElement["INFO"]["PICTURE"]["src"]) ? $arElement["INFO"]["PICTURE"]["src"] : SITE_TEMPLATE_PATH."/images/empty.png"?>" alt="<?=$arElement["INFO"]["NAME"]?>"></a></td>
				<td class="name"><a href="<?=$arElement["INFO"]["DETAIL_PAGE_URL"]?>" target="_blank" target="_blank"><?=$arElement["INFO"]["NAME"]?></a></td>
				<td class="bQty">		
					<div class="basketQty">
						<a href="#" class="minus" data-id="<?=$arElement["ID"]?>"></a>
							<input name="qty" type="text" value="<?=doubleval($arElement["QUANTITY"])?>" class="qty" <?if($arResult["OPTION_QUANTITY_TRACE"] == "Y"):?>data-max-quantity="<?=$arElement["INFO"]["CATALOG_QUANTITY"]?>"<?endif;?> data-id="<?=$arElement["ID"]?>" data-ratio="<?=$arElement["INFO"]["ADDBASKET_QUANTITY_RATIO"]?>" />
							<a href="#" class="plus" data-id="<?=$arElement["ID"]?>"></a> 
						</div>
					</td>
				<td>                            
					<?if($arElement["INFO"]["CATALOG_QUANTITY"] > 0):?>
						<?if(!empty($arElement["INFO"]["STORES"])):?>
							<a href="#" data-id="<?=$arElement["INFO"]["ID"]?>" class="inStock label changeAvailable getStoresWindow"><img src="<?=SITE_TEMPLATE_PATH?>/images/inStock.png" alt="<?=GetMessage("AVAILABLE")?>" class="icon"><span><?=GetMessage("AVAILABLE")?></span></a>
						<?else:?>
							<span class="inStock label changeAvailable"><img src="<?=SITE_TEMPLATE_PATH?>/images/inStock.png" alt="<?=GetMessage("AVAILABLE")?>" class="icon"><span><?=GetMessage("AVAILABLE")?></span></span>
						<?endif;?>
					<?else:?>
						<?if(!empty($arElement["CAN_BUY"]) && $arElement["CAN_BUY"] == "Y"):?>
							<a class="onOrder label changeAvailable"><img src="<?=SITE_TEMPLATE_PATH?>/images/onOrder.png" alt="" class="icon"><?=GetMessage("ON_ORDER")?></a>
						<?else:?>
							<a class="outOfStock label changeAvailable"><img src="<?=SITE_TEMPLATE_PATH?>/images/outOfStock.png" alt="" class="icon"><?=GetMessage("NOAVAILABLE")?></a>
						<?endif;?>
					<?endif;?>		
        		</td>
				<td>
					<span class="price">		      
						<?=(((abs($arElement["INFO"]["OLD_PRICE"] - $arElement["PRICE"])) > 0.01) ? '<s>'.FormatCurrency($arElement["INFO"]["OLD_PRICE"], $OPTION_CURRENCY).'</s>' : '')?>
  						<?=FormatCurrency($arElement["PRICE"], $OPTION_CURRENCY);?> 
	  					<?if($arParams["HIDE_MEASURES"] != "Y" && !empty($arResult["MEASURES"][$arElement["INFO"]["CATALOG_MEASURE"]]["SYMBOL_RUS"])):?>
							<span class="measure"> / <?=$arResult["MEASURES"][$arElement["INFO"]["CATALOG_MEASURE"]]["SYMBOL_RUS"]?></span>
						<?endif;?>
  					</span>
  				</td>
  				<td>
  					<span class="sum" data-price="<?=doubleval($arElement["PRICE"])?>"><?=FormatCurrency($arElement["PRICE"] * doubleval($arElement["QUANTITY"]), $OPTION_CURRENCY);?> </span>
  				</td>
				<td class="elementDelete"><a href="#" class="delete" data-id="<?=$arElement["ID"]?>"></a></td>
			</tr>
		<?endforeach;?>
	</tbody>
</table>