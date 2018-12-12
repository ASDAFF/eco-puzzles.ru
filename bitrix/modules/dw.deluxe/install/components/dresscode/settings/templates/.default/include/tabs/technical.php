<div class="switcherTechnicalSettings switcherTab active">
	<?if(!empty($arResult["PRODUCT_IBLOCKS"])):?>
		<div class="switcherRowBlock">
			<div class="switcherHeading2"><?=GetMessage("SETTINGS_PRODUCT_IBLOCK_ID")?></div>
			<select class="switcherSelect productIblockSelect" data-id="TEMPLATE_PRODUCT_IBLOCK_ID">
				<?foreach($arResult["PRODUCT_IBLOCKS"] as $iblockId => $arNextIblock):?>
					<option value="<?=$iblockId?>"<?if($iblockId == $productIblockId):?> selected="selected"<?endif;?>><?=$arNextIblock["NAME"]?> [<?=$iblockId?>]</option>
				<?endforeach;?>
			</select>
		</div>
	<?endif;?>
	<div class="switcherAjaxContainer productPropertyAjax">
		<?if(!empty($arResult["PRODUCT_IBLOCKS"][$productIblockId]["PROPERTIES"])):?>
			<?$indx = 0;?>
			<div class="switcherRowBlock">
				<div class="switcherHeading2"><?=GetMessage("SETTINGS_PRODUCT_PROPERTIES_ID")?></div>
				<div class="switcherCheckboxItems" data-id="TEMPLATE_PRODUCT_PROPERTIES">
					<?foreach($arResult["PRODUCT_IBLOCKS"][$productIblockId]["PROPERTIES"] as $arNextProperty):?>
						<div class="switcherCheckboxItem<?if(++$indx > 10):?> hidden<?endif;?>">
							<input type="checkbox" name="TEMPLATE_PRODUCT_PROPERTIES[]" value="<?=$arNextProperty["ID"]?>"<?if($arNextProperty["SORT"] <= 5000):?> checked="checked"<?endif;?> class="switcherMultiCheckItem" id="multiCheck_<?=$arNextProperty["ID"]?>">
							<label for="multiCheck_<?=$arNextProperty["ID"]?>" title="<?=$arNextProperty["NAME"]?>"><?=$arNextProperty["NAME"]?> [<?=$arNextProperty["ID"]?>]</label>
							<a href="/bitrix/admin/iblock_edit_property.php?ID=<?=$arNextProperty["ID"]?>&IBLOCK_ID=<?=$productIblockId?>" target="_blank" class="settingsEdit" title="<?=GetMessage("SETTINGS_PROPERTY_EDIT")?>"></a>
						</div>
					<?endforeach;?>
					<?if($indx > 10):?>
						<a href="#" class="switcherShowAll"><?=GetMessage("SETTINGS_PROPERTY_SHOW_ALL")?></a>
						<a href="#" class="switcherHideAll hidden"><?=GetMessage("SETTINGS_PROPERTY_HIDE_ALL")?></a>
					<?endif;?>
				</div>
			</div>
		<?endif;?>
	</div>
	<?if(!empty($arResult["PRODUCT_IBLOCKS"])):?>
		<div class="switcherRowBlock">
			<div class="switcherHeading2" title="<?=GetMessage("SETTINGS_CREATE_PRODUCT_PROPERTIES_DESC")?>"><?=GetMessage("SETTINGS_CREATE_PRODUCT_PROPERTIES")?></div>
			<a href="#" class="switcherCreateProductProperties" data-iblock-id="<?=$productIblockId?>"><?=GetMessage("SETTINGS_CREATE_PRODUCT_PROPERTIES_LINK")?></a>
		</div>
	<?endif;?>
	<?if(!empty($arResult["PRICE_CODES"])):?>
		<div class="switcherRowBlock">
			<div class="switcherHeading2"><?=GetMessage("SETTINGS_PRICE_CODES")?></div>
			<div class="switcherCheckboxItems" data-id="TEMPLATE_PRICE_CODES">
				<?foreach($arResult["PRICE_CODES"] as $priceId => $arNextPrice):?>
					<div class="switcherCheckboxItem">
						<input type="checkbox" name="TEMPLATE_PRICE_CODES[]" value="<?=$arNextPrice["NAME"]?>"<?if(in_array($arNextPrice["NAME"], $template_price_code)):?> checked="checked"<?endif;?> class="switcherMultiCheckItem" id="multiCheck_<?=$priceId?>">
						<label for="multiCheck_<?=$priceId?>" title="<?=$arNextPrice["NAME"]?>"><?=$arNextPrice["NAME_LANG"]?> [<?=$arNextPrice["NAME"]?>] <?if(!empty($arNextPrice["BASE"]) && $arNextPrice["BASE"] == "Y"):?>&#10004;<?endif;?></label>
						<a href="/bitrix/admin/cat_group_edit.php?ID=<?=$priceId?>&lang=<?=LANGUAGE_ID?>&&filter=Y&set_filter=Y" target="_blank" class="settingsEdit" title="<?=GetMessage("SETTINGS_PRICE_CODE_EDIT")?>"></a>
					</div>
				<?endforeach;?>
			</div>
		</div>
	<?endif;?>
	<?if(!empty($arResult["SKU_IBLOCKS"])):?>
		<div class="switcherRowBlock">
			<div class="switcherHeading2"><?=GetMessage("SETTINGS_SKU_IBLOCK_ID")?></div>
			<select class="switcherSelect skuIblockSelect" data-id="TEMPLATE_SKU_IBLOCK_ID">
				<?foreach($arResult["SKU_IBLOCKS"] as $iblockId => $arNextIblock):?>
					<option value="<?=$iblockId?>"<?if($iblockId == $skuIblockId):?> selected="selected"<?endif;?>><?=$arNextIblock["NAME"]?> [<?=$iblockId?>]</option>
				<?endforeach;?>
			</select>
		</div>
	<?endif;?>
	<div class="switcherAjaxContainer skuPropertyAjax">
		<?if(!empty($arResult["SKU_IBLOCKS"][$skuIblockId]["PROPERTIES"])):?>
			<?$indx = 0;?>
			<div class="switcherRowBlock">
				<div class="switcherHeading2"><?=GetMessage("SETTINGS_SKU_PROPERTIES_ID")?></div>
				<div class="switcherCheckboxItems" data-id="TEMPLATE_SKU_PROPERTIES">
					<?foreach($arResult["SKU_IBLOCKS"][$skuIblockId]["PROPERTIES"] as $arNextProperty):?>
						<div class="switcherCheckboxItem<?if(++$indx > 10):?> hidden<?endif;?>">
							<input type="checkbox" name="TEMPLATE_SKU_PROPERTIES[]" value="<?=$arNextProperty["ID"]?>"<?if($arNextProperty["SORT"] <= 100):?> checked="checked"<?endif;?> class="switcherMultiCheckItem" id="multiCheck_<?=$arNextProperty["ID"]?>">
							<label for="multiCheck_<?=$arNextProperty["ID"]?>" title="<?=$arNextProperty["NAME"]?>"><?=$arNextProperty["NAME"]?> [<?=$arNextProperty["ID"]?>]</label>
							<a href="/bitrix/admin/iblock_edit_property.php?ID=<?=$arNextProperty["ID"]?>&IBLOCK_ID=<?=$skuIblockId?>" target="_blank" class="settingsEdit" title="<?=GetMessage("SETTINGS_PROPERTY_EDIT")?>"></a>
						</div>
					<?endforeach;?>
					<?if($indx > 10):?>
						<a href="#" class="switcherShowAll"><?=GetMessage("SETTINGS_PROPERTY_SHOW_ALL")?></a>
						<a href="#" class="switcherHideAll hidden"><?=GetMessage("SETTINGS_PROPERTY_HIDE_ALL")?></a>
					<?endif;?>
				</div>
			</div>
		<?endif;?>
	</div>
	<?if(!empty($arResult["SKU_IBLOCKS"])):?>
		<div class="switcherRowBlock">
			<div class="switcherHeading2" title="<?=GetMessage("SETTINGS_CREATE_SKU_PROPERTIES_DESC")?>"><?=GetMessage("SETTINGS_CREATE_SKU_PROPERTIES")?></div>
			<a href="#" class="switcherCreateSkuProperties" data-iblock-id="<?=$skuIblockId?>"><?=GetMessage("SETTINGS_CREATE_SKU_PROPERTIES_LINK")?></a>
		</div>
	<?endif;?>
</div>