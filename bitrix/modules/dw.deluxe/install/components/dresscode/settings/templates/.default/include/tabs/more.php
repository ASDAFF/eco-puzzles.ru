<div class="switcherMoreSettings switcherTab">
	<div class="switcherRowBlock">
		<div class="switcherIcons">
			<img src="<?=$templateFolder?>/images/switcherWatermarkIcon.jpg" alt="<?=GetMessage("SETTINGS_WATERMARK_HEADING")?>" title="<?=GetMessage("SETTINGS_WATERMARK_HEADING")?>">
		</div>
		<div class="switcherHeading"><?=GetMessage("SETTINGS_WATERMARK_HEADING")?></div>
		<div class="switcherDescription"><?=GetMessage("SETTINGS_WATERMARK_DESC")?></div>
	</div>
	<div class="switcherRowBlock">
		<div class="switcherHeading2"><?=GetMessage("SETTINGS_USE_AUTO_WATERMARK")?></div>
		<div class="switcherBool switchByLink" data-id="TEMPLATE_USE_AUTO_WATERMARK">
			<?foreach($arBoolButton as $ixn => $nextVariant):?>
				<div class="switcherBoolItem switchByLinkItem<?if($ixn == $template_use_watermark):?> selected<?endif;?>"><a href="#" class="switcherBoolButton" data-value="<?=$ixn?>" title="<?=$nextVariant?>"><?=$nextVariant?></a></div>
			<?endforeach;?>
		</div>
	</div>
	<div class="switcherRowBlock" data-dependence="TEMPLATE_USE_AUTO_WATERMARK">
		<div class="switcherHeading2"><?=GetMessage("SETTINGS_WATERMARK_TYPE")?></div>
		<select class="switcherSelect watermarkTypeSelect" data-id="TEMPLATE_WATERMARK_TYPE">
			<?foreach($arWatermarkTypes as $typeValue => $typeName):?>
				<option value="<?=$typeValue?>"<?if($typeValue == $template_watermark_type):?> selected="selected"<?endif;?>><?=$typeName?> [<?=$typeValue?>]</option>
			<?endforeach;?>
		</select>
	</div>
	<div class="switcherRowBlock" data-dependence='{"TEMPLATE_USE_AUTO_WATERMARK": "Y", "TEMPLATE_WATERMARK_TYPE": "image"}'>
		<div class="switcherHeading2"><?=GetMessage("SETTINGS_WATERMARK_PICTURE")?></div>
		<div class="switcherThemes switcherFile" data-id="TEMPLATE_WATERMARK_PICTURE">
			<input type="file" id="settingsWatermarkPicture" name="settingsWatermarkPicture" accept="image/*"<?if(!empty($arResult["CURRENT_SETTINGS"]["TEMPLATE_WATERMARK_PICTURE"])):?> data-value="<?=$arResult["CURRENT_SETTINGS"]["TEMPLATE_WATERMARK_PICTURE"]?>"<?endif;?>>
			<?if(!empty($arResult["CURRENT_SETTINGS"]["TEMPLATE_WATERMARK_PICTURE"])):?>
				<div class="switcherImageContainer"><a href="<?=DwSettings::clearRootFilePath($arResult["CURRENT_SETTINGS"]["TEMPLATE_WATERMARK_PICTURE"])?>" target="_blank" class="switcherImageContainerLink"><img src="<?=DwSettings::clearRootFilePath($arResult["CURRENT_SETTINGS"]["TEMPLATE_WATERMARK_PICTURE"])?>" class="switcherImageContainerImage"></a></div>
			<?endif;?>
		</div>
	</div>
	<div class="switcherRowBlock" data-dependence="TEMPLATE_USE_AUTO_WATERMARK">
		<div class="switcherHeading2"><?=GetMessage("SETTINGS_WATERMARK_SIZE")?></div>
		<select class="switcherSelect watermarkSizeSelect" data-id="TEMPLATE_WATERMARK_SIZE">
			<?foreach($arWatermarkSizes as $sizeValue => $sizeName):?>
				<option value="<?=$sizeValue?>"<?if($sizeValue == $template_watermark_size):?> selected="selected"<?endif;?>><?=$sizeName?> [<?=$sizeValue?>]</option>
			<?endforeach;?>
		</select>
	</div>
	<div class="switcherRowBlock" data-dependence='{"TEMPLATE_USE_AUTO_WATERMARK": "Y", "TEMPLATE_WATERMARK_TYPE": "image"}'>
		<div class="switcherHeading2"><?=GetMessage("SETTINGS_WATERMARK_FILL")?></div>
		<select class="switcherSelect watermarkFillSelect" data-id="TEMPLATE_WATERMARK_FILL">
			<?foreach($arWatermarkFill as $fillValue => $fillName):?>
				<option value="<?=$fillValue?>"<?if($fillValue == $template_watermark_fill):?> selected="selected"<?endif;?>><?=$fillName?> [<?=$fillValue?>]</option>
			<?endforeach;?>
		</select>
	</div>
	<div class="switcherRowBlock" data-dependence="TEMPLATE_USE_AUTO_WATERMARK">
		<div class="switcherHeading2"><?=GetMessage("SETTINGS_WATERMARK_POSITION")?></div>
		<select class="switcherSelect watermarkPositionSelect" data-id="TEMPLATE_WATERMARK_POSITION">
			<?foreach($arWatermarkPositions as $posValue => $posName):?>
				<option value="<?=$posValue?>"<?if($posValue == $template_watermark_position):?> selected="selected"<?endif;?>><?=$posName?> [<?=$posValue?>]</option>
			<?endforeach;?>
		</select>
	</div>
	<div class="switcherRowBlock" data-dependence="TEMPLATE_USE_AUTO_WATERMARK">
		<div class="switcherHeading2"><?=GetMessage("SETTINGS_WATERMARK_COEFFICIENT")?></div>
		<div class="switcherThemes switcherInputText" data-id="TEMPLATE_WATERMARK_COEFFICIENT">
			<input type="number" name="settingsWatermarkCoefficient" value="<?if(!empty($arResult["CURRENT_SETTINGS"]["TEMPLATE_WATERMARK_COEFFICIENT"])):?><?=$arResult["CURRENT_SETTINGS"]["TEMPLATE_WATERMARK_COEFFICIENT"]?><?endif;?>">
		</div>
	</div>
	<div class="switcherRowBlock" data-dependence='{"TEMPLATE_USE_AUTO_WATERMARK": "Y", "TEMPLATE_WATERMARK_TYPE": "image"}'>
		<div class="switcherHeading2"><?=GetMessage("SETTINGS_WATERMARK_ALPHA_LEVEL")?></div>
		<div class="switcherThemes switcherInputText" data-id="TEMPLATE_WATERMARK_ALPHA_LEVEL">
			<input type="number" name="settingsWatermarkAlphaLevel" value="<?if(!empty($arResult["CURRENT_SETTINGS"]["TEMPLATE_WATERMARK_ALPHA_LEVEL"])):?><?=$arResult["CURRENT_SETTINGS"]["TEMPLATE_WATERMARK_ALPHA_LEVEL"]?><?endif;?>">
		</div>
	</div>
	<div class="switcherRowBlock" data-dependence='{"TEMPLATE_USE_AUTO_WATERMARK": "Y", "TEMPLATE_WATERMARK_TYPE": "text"}'>
		<div class="switcherHeading2"><?=GetMessage("SETTINGS_WATERMARK_TEXT")?></div>
		<div class="switcherThemes switcherInputText" data-id="TEMPLATE_WATERMARK_TEXT">
			<input type="text" name="settingsWatermarkText" value="<?if(!empty($arResult["CURRENT_SETTINGS"]["TEMPLATE_WATERMARK_TEXT"])):?><?=$arResult["CURRENT_SETTINGS"]["TEMPLATE_WATERMARK_TEXT"]?><?endif;?>">
		</div>
	</div>
	<div class="switcherRowBlock" data-dependence='{"TEMPLATE_USE_AUTO_WATERMARK": "Y", "TEMPLATE_WATERMARK_TYPE": "text"}'>
		<div class="switcherHeading2"><?=GetMessage("SETTINGS_WATERMARK_FONT")?></div>
		<div class="switcherThemes switcherInputText" data-id="TEMPLATE_WATERMARK_FONT">
			<input type="text" name="settingsWatermarkFont" value="<?if(!empty($arResult["CURRENT_SETTINGS"]["TEMPLATE_WATERMARK_FONT"])):?><?=$arResult["CURRENT_SETTINGS"]["TEMPLATE_WATERMARK_FONT"]?><?else:?><?=$_SERVER["DOCUMENT_ROOT"]?><?=SITE_TEMPLATE_PATH?>/fonts/roboto/roboto-regular.ttf<?endif;?>">
		</div>
	</div>
	<div class="switcherRowBlock" data-dependence='{"TEMPLATE_USE_AUTO_WATERMARK": "Y", "TEMPLATE_WATERMARK_TYPE": "text"}'>
		<div class="switcherHeading2"><?=GetMessage("SETTINGS_WATERMARK_COLOR")?></div>
		<div class="switcherThemes switcherInputText" data-id="TEMPLATE_WATERMARK_COLOR">
			<input type="text" name="settingsWatermarkColor" value="<?if(!empty($arResult["CURRENT_SETTINGS"]["TEMPLATE_WATERMARK_COLOR"])):?><?=$arResult["CURRENT_SETTINGS"]["TEMPLATE_WATERMARK_COLOR"]?><?endif;?>">
		</div>
	</div>
	<div class="switcherRowBlock">
		<div class="switcherIcons">
			<img src="<?=$templateFolder?>/images/switcherEventIcon.jpg" alt="<?=GetMessage("SETTINGS_USE_AUTO_HEADING")?>" title="<?=GetMessage("SETTINGS_USE_AUTO_HEADING")?>">
		</div>
		<div class="switcherHeading"><?=GetMessage("SETTINGS_USE_AUTO_HEADING")?></div>
		<div class="switcherDescription"><?=GetMessage("SETTINGS_USE_AUTO_DESC")?></div>
	</div>

	<div class="switcherRowBlock">
		<div class="switcherHeading2"><?=GetMessage("SETTINGS_USE_AUTO_DEACTIVATE_PRODUCTS")?></div>
		<div class="switcherBool switchByLink" data-id="TEMPLATE_USE_AUTO_DEACTIVATE_PRODUCTS">
			<?foreach($arBoolButton as $ixn => $nextVariant):?>
				<div class="switcherBoolItem switchByLinkItem<?if($ixn == $template_use_auto_deactivate):?> selected<?endif;?>"><a href="#" class="switcherBoolButton" data-value="<?=$ixn?>" title="<?=$nextVariant?>"><?=$nextVariant?></a></div>
			<?endforeach;?>
		</div>
	</div>
	<div class="switcherRowBlock">
		<div class="switcherHeading2"><?=GetMessage("SETTINGS_USE_AUTO_SAVE_PRICE")?></div>
		<div class="switcherBool switchByLink" data-id="TEMPLATE_USE_AUTO_SAVE_PRICE">
			<?foreach($arBoolButton as $ixn => $nextVariant):?>
				<div class="switcherBoolItem switchByLinkItem<?if($ixn == $template_use_auto_save_price):?> selected<?endif;?>"><a href="#" class="switcherBoolButton" data-value="<?=$ixn?>" title="<?=$nextVariant?>"><?=$nextVariant?></a></div>
			<?endforeach;?>
		</div>
	</div>
	<div class="switcherRowBlock">
		<div class="switcherHeading2"><?=GetMessage("SETTINGS_USE_AUTO_SAVE_BRAND")?></div>
		<div class="switcherBool switchByLink" data-id="TEMPLATE_USE_AUTO_BRAND">
			<?foreach($arBoolButton as $ixn => $nextVariant):?>
				<div class="switcherBoolItem switchByLinkItem<?if($ixn == $template_use_auto_brand):?> selected<?endif;?>"><a href="#" class="switcherBoolButton" data-value="<?=$ixn?>" title="<?=$nextVariant?>"><?=$nextVariant?></a></div>
			<?endforeach;?>
		</div>
	</div>
	<?if(!empty($arResult["PRODUCT_IBLOCKS"])):?>
		<div class="switcherRowBlock" data-dependence="TEMPLATE_USE_AUTO_BRAND">
			<div class="switcherHeading2"><?=GetMessage("SETTINGS_BRAND_IBLOCK_ID")?></div>
			<select class="switcherSelect brandIblockSelect" data-id="TEMPLATE_BRAND_IBLOCK_ID">
				<?foreach($arResult["PRODUCT_IBLOCKS"] as $iblockId => $arNextIblock):?>
					<option value="<?=$iblockId?>"<?if($iblockId == $brandIblockId):?> selected="selected"<?endif;?>><?=$arNextIblock["NAME"]?> [<?=$iblockId?>]</option>
				<?endforeach;?>
			</select>
		</div>
	<?endif;?>
	<div class="switcherRowBlock" data-dependence="TEMPLATE_USE_AUTO_BRAND">
		<div class="switcherHeading2"><?=GetMessage("SETTINGS_BRAND_PROPERTY_CODE")?></div>
		<div class="switcherThemes switcherInputText" data-id="TEMPLATE_BRAND_PROPERTY_CODE">
			<input type="text" name="settingsBrandPropertyCode" value="<?if(!empty($arResult["CURRENT_SETTINGS"]["TEMPLATE_BRAND_PROPERTY_CODE"])):?><?=$arResult["CURRENT_SETTINGS"]["TEMPLATE_BRAND_PROPERTY_CODE"]?><?endif;?>">
		</div>
	</div>
	<div class="switcherRowBlock">
		<div class="switcherHeading2"><?=GetMessage("SETTINGS_USE_AUTO_SAVE_COLLECTION")?></div>
		<div class="switcherBool switchByLink" data-id="TEMPLATE_USE_AUTO_COLLECTION">
			<?foreach($arBoolButton as $ixn => $nextVariant):?>
				<div class="switcherBoolItem switchByLinkItem<?if($ixn == $template_use_auto_collection):?> selected<?endif;?>"><a href="#" class="switcherBoolButton" data-value="<?=$ixn?>" title="<?=$nextVariant?>"><?=$nextVariant?></a></div>
			<?endforeach;?>
		</div>
	</div>
	<?if(!empty($arResult["PRODUCT_IBLOCKS"])):?>
		<div class="switcherRowBlock" data-dependence="TEMPLATE_USE_AUTO_COLLECTION">
			<div class="switcherHeading2"><?=GetMessage("SETTINGS_COLLECTION_IBLOCK_ID")?></div>
			<select class="switcherSelect collectionIblockSelect" data-id="TEMPLATE_COLLECTION_IBLOCK_ID">
				<?foreach($arResult["PRODUCT_IBLOCKS"] as $iblockId => $arNextIblock):?>
					<option value="<?=$iblockId?>"<?if($iblockId == $collectionIblockId):?> selected="selected"<?endif;?>><?=$arNextIblock["NAME"]?> [<?=$iblockId?>]</option>
				<?endforeach;?>
			</select>
		</div>
	<?endif;?>
	<div class="switcherRowBlock" data-dependence="TEMPLATE_USE_AUTO_COLLECTION">
		<div class="switcherHeading2"><?=GetMessage("SETTINGS_COLLECTION_PROPERTY_CODE")?></div>
		<div class="switcherThemes switcherInputText" data-id="TEMPLATE_COLLECTION_PROPERTY_CODE">
			<input type="text" name="settingsCollectionPropertyCode" value="<?if(!empty($arResult["CURRENT_SETTINGS"]["TEMPLATE_COLLECTION_PROPERTY_CODE"])):?><?=$arResult["CURRENT_SETTINGS"]["TEMPLATE_COLLECTION_PROPERTY_CODE"]?><?endif;?>">
		</div>
	</div>
</div>