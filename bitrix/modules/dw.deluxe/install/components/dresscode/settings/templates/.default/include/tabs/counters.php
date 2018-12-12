<div class="switcherCountersSettings switcherTab">
	<div class="switcherRowBlock">
		<div class="switcherIcons">
			<img src="<?=$templateFolder?>/images/switcherCountesIcon.jpg" alt="<?=GetMessage("SETTINGS_COUNTERS_TITLE")?>" title="<?=GetMessage("SETTINGS_COUNTERS_TITLE")?>">
		</div>
		<div class="switcherHeading"><?=GetMessage("SETTINGS_COUNTERS_HEADING")?></div>
		<div class="switcherDescription"><?=GetMessage("SETTINGS_COUNTERS_DESC")?></div>	
	</div>
	<div class="switcherRowBlock">
		<div class="switcherHeading2"><?=GetMessage("SETTINGS_GOOGLE_CODE_LABEL")?></div>
		<div class="switcherThemes switcherInputTextB64" data-id="TEMPLATE_GOOGLE_CODE">
			<input type="text" name="settingsGoogleCode" value='<?if(!empty($arResult["CURRENT_SETTINGS"]["TEMPLATE_GOOGLE_CODE"])):?><?=htmlspecialchars($arResult["CURRENT_SETTINGS"]["TEMPLATE_GOOGLE_CODE"], ENT_QUOTES)?><?endif;?>'>
		</div>
	</div>
	<div class="switcherRowBlock">
		<div class="switcherHeading2"><?=GetMessage("SETTINGS_METRICA_CODE_LABEL")?></div>
		<div class="switcherThemes switcherInputTextB64" data-id="TEMPLATE_METRICA_CODE">
			<input type="text" name="settingsMetricaCode" value='<?if(!empty($arResult["CURRENT_SETTINGS"]["TEMPLATE_METRICA_CODE"])):?><?=htmlspecialchars($arResult["CURRENT_SETTINGS"]["TEMPLATE_METRICA_CODE"], ENT_QUOTES)?><?endif;?>'>
		</div>
	</div>
	<div class="switcherRowBlock">
		<div class="switcherHeading2"><?=GetMessage("SETTINGS_METRICA_ID_LABEL")?></div>
		<div class="switcherThemes switcherInputText" data-id="TEMPLATE_METRICA_ID">
			<input type="number" name="settingsMetricaId" value="<?if(!empty($arResult["CURRENT_SETTINGS"]["TEMPLATE_METRICA_ID"])):?><?=$arResult["CURRENT_SETTINGS"]["TEMPLATE_METRICA_ID"]?><?endif;?>">
		</div>
	</div>
	<div class="switcherRowBlock">
		<div class="switcherHeading2"><?=GetMessage("SETTINGS_COUNTERS_CODE_LABEL")?></div>
		<div class="switcherThemes switcherInputTextB64" data-id="TEMPLATE_COUNTERS_CODE">
			<input type="text" name="settingsCountersCode" value='<?if(!empty($arResult["CURRENT_SETTINGS"]["TEMPLATE_COUNTERS_CODE"])):?><?=htmlspecialchars($arResult["CURRENT_SETTINGS"]["TEMPLATE_COUNTERS_CODE"], ENT_QUOTES)?><?endif;?>'>
		</div>
	</div>
	<div class="switcherRowBlock">
		<div class="switcherIcons">
			<img src="<?=$templateFolder?>/images/switcherMetricaIcon.jpg" alt="<?=GetMessage("SETTINGS_METRICA_TITLE")?>" title="<?=GetMessage("SETTINGS_METRICA_TITLE")?>">
		</div>
		<div class="switcherHeading"><?=GetMessage("SETTINGS_METRICA_HEADING")?></div>
		<div class="switcherDescription"><?=GetMessage("SETTINGS_METRICA_DESC")?></div>	
	</div>
	<div class="switcherRowBlock">
		<div class="switcherHeading2"><?=GetMessage("SETTINGS_METRICA_ADD_CART_LABEL")?></div>
		<div class="switcherThemes switcherInputText" data-id="TEMPLATE_METRICA_ADD_CART">
			<input type="text" name="settingsMetricaAddCart" value="<?if(!empty($arResult["CURRENT_SETTINGS"]["TEMPLATE_METRICA_ADD_CART"])):?><?=$arResult["CURRENT_SETTINGS"]["TEMPLATE_METRICA_ADD_CART"]?><?endif;?>">
		</div>
	</div>
	<div class="switcherRowBlock">
		<div class="switcherHeading2"><?=GetMessage("SETTINGS_METRICA_FAST_BUY_LABEL")?></div>
		<div class="switcherThemes switcherInputText" data-id="TEMPLATE_METRICA_FAST_BUY">
			<input type="text" name="settingsMetricaFastBuy" value="<?if(!empty($arResult["CURRENT_SETTINGS"]["TEMPLATE_METRICA_FAST_BUY"])):?><?=$arResult["CURRENT_SETTINGS"]["TEMPLATE_METRICA_FAST_BUY"]?><?endif;?>">
		</div>
	</div>
	<div class="switcherRowBlock">
		<div class="switcherHeading2"><?=GetMessage("SETTINGS_METRICA_FAST_CART_LABEL")?></div>
		<div class="switcherThemes switcherInputText" data-id="TEMPLATE_METRICA_FAST_CART">
			<input type="text" name="settingsMetricaFastCart" value="<?if(!empty($arResult["CURRENT_SETTINGS"]["TEMPLATE_METRICA_FAST_CART"])):?><?=$arResult["CURRENT_SETTINGS"]["TEMPLATE_METRICA_FAST_CART"]?><?endif;?>">
		</div>
	</div>
	<div class="switcherRowBlock">
		<div class="switcherHeading2"><?=GetMessage("SETTINGS_METRICA_ORDER_LABEL")?></div>
		<div class="switcherThemes switcherInputText" data-id="TEMPLATE_METRICA_ORDER">
			<input type="text" name="settingsMetricaOrder" value="<?if(!empty($arResult["CURRENT_SETTINGS"]["TEMPLATE_METRICA_ORDER"])):?><?=$arResult["CURRENT_SETTINGS"]["TEMPLATE_METRICA_ORDER"]?><?endif;?>">
		</div>
	</div>
	<div class="switcherRowBlock">
		<div class="switcherHeading2"><?=GetMessage("SETTINGS_METRICA_SUBSCRIBE_LABEL")?></div>
		<div class="switcherThemes switcherInputText" data-id="TEMPLATE_METRICA_SUBSCRIBE">
			<input type="text" name="settingsMetricaSubscribe" value="<?if(!empty($arResult["CURRENT_SETTINGS"]["TEMPLATE_METRICA_SUBSCRIBE"])):?><?=$arResult["CURRENT_SETTINGS"]["TEMPLATE_METRICA_SUBSCRIBE"]?><?endif;?>">
		</div>
	</div>
	<div class="switcherRowBlock">
		<div class="switcherHeading2"><?=GetMessage("SETTINGS_METRICA_REVIEW_MAGAZINE_LABEL")?></div>
		<div class="switcherThemes switcherInputText" data-id="TEMPLATE_METRICA_REVIEW_MAGAZINE">
			<input type="text" name="settingsMetricaReviewMagazine" value="<?if(!empty($arResult["CURRENT_SETTINGS"]["TEMPLATE_METRICA_REVIEW_MAGAZINE"])):?><?=$arResult["CURRENT_SETTINGS"]["TEMPLATE_METRICA_REVIEW_MAGAZINE"]?><?endif;?>">
		</div>
	</div>
	<div class="switcherRowBlock">
		<div class="switcherHeading2"><?=GetMessage("SETTINGS_METRICA_REVIEW_PRODUCT_LABEL")?></div>
		<div class="switcherThemes switcherInputText" data-id="TEMPLATE_METRICA_REVIEW_PRODUCT">
			<input type="text" name="settingsMetricaReviewProduct" value="<?if(!empty($arResult["CURRENT_SETTINGS"]["TEMPLATE_METRICA_REVIEW_PRODUCT"])):?><?=$arResult["CURRENT_SETTINGS"]["TEMPLATE_METRICA_REVIEW_PRODUCT"]?><?endif;?>">
		</div>
	</div>
</div>