<div class="switcherDesignSettings switcherTab">
	<div class="switcherRowBlock">
		<div class="switcherHeading2"><?=GetMessage("SETTINGS_LOGOTIP_LABEL")?></div>
		<div class="switcherThemes switcherFile" data-id="TEMPLATE_LOGOTIP">
			<input type="file" id="settingsLogotipFile" name="settingsLogotipFile" accept="image/*">
		</div>
	</div>
	<div class="switcherRowBlock">
		<div class="switcherHeading2"><?=GetMessage("SETTINGS_FAVICON_LABEL")?></div>
		<div class="switcherThemes switcherFile" data-id="TEMPLATE_FAVICON">
			<input type="file" id="settingsFaviconFile" name="settingsfaviconFile" accept="image/*">
		</div>
	</div>
	<?if(!empty($arResult["TEMPLATES"]["THEMES"]["VARIANTS"])):?>
		<div class="switcherRowBlock">
			<div class="switcherHeading2"><?=GetMessage("SETTINGS_THEME_LABEL")?></div>
			<div class="switcherThemes switchByLink" data-id="TEMPLATE_THEME_NAME">
				<?foreach($arResult["TEMPLATES"]["THEMES"]["VARIANTS"] as $ixn => $arNextVariant):?>
					<?$themeClassName = array_search($ixn, $arTemplateThemes);?>
					<div class="switcherThemesItem switchByLinkItem<?if($arNextVariant == $template_theme):?> selected<?endif;?>"><a href="#" class="<?=!empty($themeClassName) ? $themeClassName : "custom"?>" data-value="<?=$arNextVariant?>" title="<?=$arNextVariant?>"></a></div>
				<?endforeach;?>
			</div>
		</div>
	<?endif;?>
	<?if(!empty($arResult["TEMPLATES"]["BACKGROUND_VARIANTS"])):?>
		<div class="switcherRowBlock">
			<div class="switcherHeading2"><?=GetMessage("SETTINGS_SLIDER_BACKOGROUND")?></div>
			<div class="switcherBackgroundItems switchByLink" data-id="TEMPLATE_BACKGROUND_NAME">
				<?foreach($arResult["TEMPLATES"]["BACKGROUND_VARIANTS"] as $ixn => $arNextVariant):?>
					<div class="switcherBackgroundItem switchByLinkItem<?if($arNextVariant == $template_background_name):?> selected<?endif;?>"><a href="#" class="<?=$arNextVariant?>" data-value="<?=$arNextVariant?>"></a></div>
				<?endforeach;?>
			</div>
		</div>
	<?endif;?>
	<div class="switcherRowBlock">
		<div class="switcherIcons">
			<img src="<?=$templateFolder?>/images/switcherHeadersIcon.png" alt="<?=GetMessage("SETTINGS_HEADER_DESIGN")?>" title="<?=GetMessage("SETTINGS_HEADER_DESIGN")?>">
		</div>
		<div class="switcherHeading"><?=GetMessage("SETTINGS_HEADER_DESIGN")?></div>
		<div class="switcherDescription"><?=GetMessage("SETTINGS_HEADER_DESIGN_DESC")?></div>
	</div>
	<?if(!empty($arResult["TEMPLATES"]["HEADERS"])):?>
		<div class="switcherRowBlock">
			<select class="switcherSelect" data-id="TEMPLATE_HEADER">
				<?foreach($arResult["TEMPLATES"]["HEADERS"] as $inx => $arNextTemplate):?>
					<option value="<?=$inx?>"<?if($inx == $template_header):?> selected="selected"<?endif;?>><?=$arNextTemplate["name"]?></option>
				<?endforeach;?>
			</select>
		</div>
		<?if(!empty($arResult["TEMPLATES"]["HEADERS"][$template_header]["settings"]["subHeader"]["variants"])):?>
			<div class="switcherRowBlock">
				<div class="switcherHeading2"><?=GetMessage("SETTINGS_HEADER_COLOR")?></div>
				<select class="switcherSelect" data-id="TEMPLATE_SUBHEADER_COLOR">
					<?foreach($arResult["TEMPLATES"]["HEADERS"][$template_header]["settings"]["subHeader"]["variants"] as $ixn => $arNextVariant):?>
						<option value="<?=$ixn?>"<?if($ixn == $template_subHeader_color):?> selected="selected"<?endif;?>><?=$arNextVariant?></option>
					<?endforeach;?>
				</select>
			</div>
		<?endif;?>
		<?if(!empty($arResult["TEMPLATES"]["HEADERS"][$template_header]["settings"]["headerLine"]["variants"])):?>
			<div class="switcherRowBlock">
				<div class="switcherHeading2"><?=GetMessage("SETTINGS_HEADER_LINE_COLOR")?></div>
				<select class="switcherSelect" data-id="TEMPLATE_HEADER_COLOR">
					<?foreach($arResult["TEMPLATES"]["HEADERS"][$template_header]["settings"]["headerLine"]["variants"] as $ixn => $arNextVariant):?>
						<option value="<?=$ixn?>"<?if($ixn == $template_headerLine_color):?> selected="selected"<?endif;?>><?=$arNextVariant?></option>
					<?endforeach;?>
				</select>
			</div>
		<?endif;?>
		<?if(!empty($arResult["TEMPLATES"]["HEADERS"][$template_header]["settings"]["headerType"]["variants"])):?>
			<div class="switcherRowBlock">
				<div class="switcherHeading2"><?=GetMessage("SETTINGS_HEADER_TYPE")?></div>
				<select class="switcherSelect" data-id="TEMPLATE_HEADER_TYPE">
					<?foreach($arResult["TEMPLATES"]["HEADERS"][$template_header]["settings"]["headerType"]["variants"] as $ixn => $arNextVariant):?>
						<option value="<?=$ixn?>"<?if($ixn == $template_header_type):?> selected="selected"<?endif;?>><?=$arNextVariant?></option>
					<?endforeach;?>
				</select>
			</div>
		<?endif;?>
		<?if(!empty($arResult["TEMPLATES"]["HEADERS"][$template_header]["settings"]["catalogMenu"]["variants"])):?>
			<div class="switcherRowBlock">
				<div class="switcherHeading2"><?=GetMessage("SETTINGS_CATALOG_MENU_COLOR")?></div>
				<select class="switcherSelect" data-id="TEMPLATE_CATALOG_MENU_COLOR">
					<?foreach($arResult["TEMPLATES"]["HEADERS"][$template_header]["settings"]["catalogMenu"]["variants"] as $ixn => $arNextVariant):?>
						<option value="<?=$ixn?>"<?if($ixn == $template_catalog_menu_color):?> selected="selected"<?endif;?>><?=$arNextVariant?></option>
					<?endforeach;?>
				</select>
			</div>
		<?endif;?>
		<?if(!empty($arResult["TEMPLATES"]["HEADERS"][$template_header]["settings"]["sliderHeight"]["variants"])):?>
			<div class="switcherRowBlock">
				<div class="switcherHeading2"><?=GetMessage("SETTINGS_SLIDER_HEIGHT")?></div>
				<select class="switcherSelect" data-id="TEMPLATE_SLIDER_HEIGHT">
					<?foreach($arResult["TEMPLATES"]["HEADERS"][$template_header]["settings"]["sliderHeight"]["variants"] as $ixn => $arNextVariant):?>
						<option value="<?=$ixn?>"<?if($ixn == $template_slider_height):?> selected="selected"<?endif;?>><?=$arNextVariant?></option>
					<?endforeach;?>
				</select>
			</div>
		<?endif;?>
	<?endif;?>
	<?if(!empty($arResult["TEMPLATES"]["HEADERS"][$template_header]["settings"]["fixTopMenu"]["variants"])):?>
		<div class="switcherRowBlock">
			<div class="switcherHeading2"><?=GetMessage("SETTINGS_TOP_MENU_FIXED")?></div>
			<select class="switcherSelect" data-id="TEMPLATE_TOP_MENU_FIXED">
				<?foreach($arResult["TEMPLATES"]["HEADERS"][$template_header]["settings"]["fixTopMenu"]["variants"] as $ixn => $arNextVariant):?>
					<option value="<?=$ixn?>"<?if($ixn == $template_fix_top_menu):?> selected="selected"<?endif;?>><?=$arNextVariant?></option>
				<?endforeach;?>
			</select>
		</div>
	<?endif;?>
	<div class="switcherRowBlock">
		<div class="switcherIcons">
			<img src="<?=$templateFolder?>/images/switcherPanelsIcon.png" alt="<?=GetMessage("SETTINGS_PANELS_TITLE")?>" title="<?=GetMessage("SETTINGS_PANELS_TITLE")?>">
		</div>
		<div class="switcherHeading"><?=GetMessage("SETTINGS_PANELS_HEADING")?></div>
		<div class="switcherDescription"><?=GetMessage("SETTINGS_PANELS_DESCRIPTION")?></div>
	</div>
	<?if(!empty($arResult["TEMPLATES"]["SETTINGS"]["panels_colors"])):?>
		<div class="switcherRowBlock">
			<div class="switcherHeading2"><?=GetMessage("SETTINGS_PANELS_COLORS")?></div>
			<select class="switcherSelect" data-id="TEMPLATE_PANELS_COLOR">
				<?foreach($arResult["TEMPLATES"]["SETTINGS"]["panels_colors"] as $ixn => $arNextVariant):?>
					<option value="<?=$ixn?>"<?if($ixn == $template_panels_color):?> selected="selected"<?endif;?>><?=$arNextVariant?></option>
				<?endforeach;?>
			</select>
		</div>
	<?endif;?>
	<?if(!empty($arResult["TEMPLATES"]["SETTINGS"]["footer_line_colors"])):?>
		<div class="switcherRowBlock">
			<div class="switcherHeading2"><?=GetMessage("SETTINGS_FOOTER_LINE_COLORS")?></div>
			<select class="switcherSelect" data-id="TEMPLATE_FOOTER_LINE_COLOR">
				<?foreach($arResult["TEMPLATES"]["SETTINGS"]["footer_line_colors"] as $ixn => $arNextVariant):?>
					<option value="<?=$ixn?>"<?if($ixn == $template_footer_line_color):?> selected="selected"<?endif;?>><?=$arNextVariant?></option>
				<?endforeach;?>
			</select>
		</div>
	<?endif;?>
	<?if(!empty($arResult["TEMPLATES"]["SETTINGS"]["footer_themes"])):?>
		<div class="switcherRowBlock">
			<div class="switcherHeading2"><?=GetMessage("SETTINGS_FOOTER_THEMES")?></div>
			<select class="switcherSelect" data-id="TEMPLATE_FOOTER_VARIANT">
				<?foreach($arResult["TEMPLATES"]["SETTINGS"]["footer_themes"] as $ixn => $arNextVariant):?>
					<option value="<?=$ixn?>"<?if($ixn == $template_footer_variant):?> selected="selected"<?endif;?>><?=$arNextVariant?></option>
				<?endforeach;?>
			</select>
		</div>
	<?endif;?>
</div>