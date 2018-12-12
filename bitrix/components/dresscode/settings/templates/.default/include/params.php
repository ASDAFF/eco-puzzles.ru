<?
	//check product iblock
	if(!empty($arResult["CURRENT_SETTINGS"]["TEMPLATE_PRODUCT_IBLOCK_ID"])){
		$productIblockId = $arResult["CURRENT_SETTINGS"]["TEMPLATE_PRODUCT_IBLOCK_ID"];
	}

	else{
		$productIblockId = key($arResult["PRODUCT_IBLOCKS"]);
	}

	//check sku iblock
	if(!empty($arResult["CURRENT_SETTINGS"]["TEMPLATE_SKU_IBLOCK_ID"])){
		$skuIblockId = $arResult["CURRENT_SETTINGS"]["TEMPLATE_SKU_IBLOCK_ID"];
	}

	else{
		$skuIblockId = key($arResult["SKU_IBLOCKS"]);
	}

	//check active background variant
	if(!empty($arResult["TEMPLATES"]["BACKGROUND_VARIANTS"])){
		if(!empty($arResult["CURRENT_SETTINGS"]["TEMPLATE_BACKGROUND_NAME"])){
			$template_background_name = $arResult["CURRENT_SETTINGS"]["TEMPLATE_BACKGROUND_NAME"];
		}
		else{
			$template_background_name = key($arResult["TEMPLATES"]["BACKGROUND_VARIANTS"]);
		}
	}

	if(!empty($template_background_name)){
		//write themes for bg color
		$arResult["TEMPLATES"]["THEMES"] = $arResult["TEMPLATES"]["THEMES"][$template_background_name];
	}

	//check active theme
	if(!empty($arResult["CURRENT_SETTINGS"]["TEMPLATE_THEME_NAME"])){
		$template_theme = $arResult["CURRENT_SETTINGS"]["TEMPLATE_THEME_NAME"];
	}

	else{
		$template_theme = key($arResult["TEMPLATES"]["THEMES"]);
	}

	//check active header
	if(!empty($arResult["CURRENT_SETTINGS"]["TEMPLATE_HEADER"])){
		$template_header = $arResult["CURRENT_SETTINGS"]["TEMPLATE_HEADER"];
	}

	else{
		$template_header = key($arResult["TEMPLATES"]["HEADERS"]);
	}

	//check active header type
	if(!empty($arResult["CURRENT_SETTINGS"]["TEMPLATE_HEADER_TYPE"])){
		$template_header_type = $arResult["CURRENT_SETTINGS"]["TEMPLATE_HEADER_TYPE"];
	}

	else{
		if(!empty($arResult["TEMPLATES"]["HEADERS"][$template_header]["settings"]["headerType"])){
			$template_header_type = key($arResult["TEMPLATES"]["HEADERS"][$template_header]["settings"]["headerType"]["variants"]);
		}
	}
	
	//check active headerLine
	if(!empty($arResult["CURRENT_SETTINGS"]["TEMPLATE_HEADER_COLOR"])){
		$template_headerLine_color = $arResult["CURRENT_SETTINGS"]["TEMPLATE_HEADER_COLOR"];
	}

	else{
		if(!empty($arResult["TEMPLATES"]["HEADERS"][$template_header]["settings"]["headerLine"])){
			$template_headerLine_color = key($arResult["TEMPLATES"]["HEADERS"][$template_header]["settings"]["headerLine"]["variants"]);
		}
	}

	//check active subHeader
	if(!empty($arResult["CURRENT_SETTINGS"]["TEMPLATE_SUBHEADER_COLOR"])){
		$template_subHeader_color = $arResult["CURRENT_SETTINGS"]["TEMPLATE_SUBHEADER_COLOR"];
	}

	else{
		if(!empty($arResult["TEMPLATES"]["HEADERS"][$template_header]["settings"]["subHeader"])){
			$template_subHeader_color = key($arResult["TEMPLATES"]["HEADERS"][$template_header]["settings"]["subHeader"]["variants"]);
		}
	}

	//check slider height
	if(!empty($arResult["CURRENT_SETTINGS"]["TEMPLATE_SLIDER_HEIGHT"])){
		$template_slider_height = $arResult["CURRENT_SETTINGS"]["TEMPLATE_SLIDER_HEIGHT"];
	}

	else{
		if(!empty($arResult["TEMPLATES"]["HEADERS"][$template_header]["settings"]["sliderHeight"])){
			$template_slider_height = key($arResult["TEMPLATES"]["HEADERS"][$template_header]["settings"]["sliderHeight"]["variants"]);
		}
	}

	//check catalog color
	if(!empty($arResult["CURRENT_SETTINGS"]["TEMPLATE_CATALOG_MENU_COLOR"])){
		$template_catalog_menu_color = $arResult["CURRENT_SETTINGS"]["TEMPLATE_CATALOG_MENU_COLOR"];
	}

	else{
		if(!empty($arResult["TEMPLATES"]["HEADERS"][$template_header]["settings"]["catalogMenu"])){
			$template_catalog_menu_color = key($arResult["TEMPLATES"]["HEADERS"][$template_header]["settings"]["catalogMenu"]["variants"]);
		}
	}

	//check fix topmenu
	if(!empty($arResult["CURRENT_SETTINGS"]["TEMPLATE_TOP_MENU_FIXED"])){
		$template_fix_top_menu = $arResult["CURRENT_SETTINGS"]["TEMPLATE_TOP_MENU_FIXED"];
	}

	else{
		if(!empty($arResult["TEMPLATES"]["HEADERS"][$template_header]["settings"]["fixTopMenu"])){
			$template_fix_top_menu = key($arResult["TEMPLATES"]["HEADERS"][$template_header]["settings"]["fixTopMenu"]["variants"]);
		}
	}

	//check active panels color
	if(!empty($arResult["CURRENT_SETTINGS"]["TEMPLATE_PANELS_COLOR"])){
		$template_panels_color = $arResult["CURRENT_SETTINGS"]["TEMPLATE_PANELS_COLOR"];
	}

	else{
		if(!empty($arResult["TEMPLATES"]["SETTINGS"]["panels_colors"])){
			$template_panels_color = key($arResult["TEMPLATES"]["SETTINGS"]["panels_colors"]);
		}
	}

	//check active footerLine color
	if(!empty($arResult["CURRENT_SETTINGS"]["TEMPLATE_FOOTER_LINE_COLOR"])){
		$template_footer_line_color = $arResult["CURRENT_SETTINGS"]["TEMPLATE_FOOTER_LINE_COLOR"];
	}

	else{
		if(!empty($arResult["TEMPLATES"]["SETTINGS"]["footer_line_colors"])){
			$template_footer_line_color = key($arResult["TEMPLATES"]["SETTINGS"]["footer_line_colors"]);
		}
	}

	//check active footer themes
	if(!empty($arResult["CURRENT_SETTINGS"]["TEMPLATE_FOOTER_VARIANT"])){
		$template_footer_variant = $arResult["CURRENT_SETTINGS"]["TEMPLATE_FOOTER_VARIANT"];
	}

	else{
		if(!empty($arResult["TEMPLATES"]["SETTINGS"]["footer_themes"])){
			$template_footer_variant = key($arResult["TEMPLATES"]["SETTINGS"]["footer_themes"]);
		}
	}

	//themes class name
	$arTemplateThemes = array(
		"color1" => "default",
		"color2" => "pink",
		"color3" => "peach",
		"color4" => "beige",
		"color5" => "brown",
		"color6" => "coral",
		"color7" => "mint",
		"color8" => "green",
		"color9" => "ocean",
		"color10" => "arctic",
		"color11" => "blue",
		"color12" => "ultramarine",
		"color13" => "violet",
		"color14" => "raspberry",
		"color15" => "red"
	);

	//check active price codes
	$template_price_code = array();
	if(!empty($arResult["CURRENT_SETTINGS"]["TEMPLATE_PRICE_CODES"])){
		$template_price_code = explode(", ", $arResult["CURRENT_SETTINGS"]["TEMPLATE_PRICE_CODES"]);
	}

	//bool button
	$arBoolButton = array(
		"Y" => GetMessage("SETTINGS_BOOL_BUTTON_TRUE"),
		"N" => GetMessage("SETTINGS_BOOL_BUTTON_FALSE"),
	);

	//min price max price param
	if(!empty($arResult["CURRENT_SETTINGS"]["TEMPLATE_USE_AUTO_DEACTIVATE_PRODUCTS"])){
		$template_use_auto_deactivate = $arResult["CURRENT_SETTINGS"]["TEMPLATE_USE_AUTO_DEACTIVATE_PRODUCTS"];
	}

	else{
		$template_use_auto_deactivate = "N";
	}

	//min price max price param
	if(!empty($arResult["CURRENT_SETTINGS"]["TEMPLATE_USE_AUTO_SAVE_PRICE"])){
		$template_use_auto_save_price = $arResult["CURRENT_SETTINGS"]["TEMPLATE_USE_AUTO_SAVE_PRICE"];
	}

	else{
		$template_use_auto_save_price = "N";
	}

	//brand auto property
	if(!empty($arResult["CURRENT_SETTINGS"]["TEMPLATE_USE_AUTO_BRAND"])){
		$template_use_auto_brand = $arResult["CURRENT_SETTINGS"]["TEMPLATE_USE_AUTO_BRAND"];
	}

	else{
		$template_use_auto_brand = "N";
	}

	//collection auto property
	if(!empty($arResult["CURRENT_SETTINGS"]["TEMPLATE_USE_AUTO_COLLECTION"])){
		$template_use_auto_collection = $arResult["CURRENT_SETTINGS"]["TEMPLATE_USE_AUTO_COLLECTION"];
	}

	else{
		$template_use_auto_collection = "N";
	}

	//check brand iblock id
	if(!empty($arResult["CURRENT_SETTINGS"]["TEMPLATE_BRAND_IBLOCK_ID"])){
		$brandIblockId = $arResult["CURRENT_SETTINGS"]["TEMPLATE_BRAND_IBLOCK_ID"];
	}

	else{
		$brandIblockId = key($arResult["PRODUCT_IBLOCKS"]);
	}

	//check collection iblock id
	if(!empty($arResult["CURRENT_SETTINGS"]["TEMPLATE_COLLECTION_IBLOCK_ID"])){
		$collectionIblockId = $arResult["CURRENT_SETTINGS"]["TEMPLATE_COLLECTION_IBLOCK_ID"];
	}

	else{
		$collectionIblockId = key($arResult["PRODUCT_IBLOCKS"]);
	}

	//watermark
	if(!empty($arResult["CURRENT_SETTINGS"]["TEMPLATE_USE_AUTO_WATERMARK"])){
		$template_use_watermark = $arResult["CURRENT_SETTINGS"]["TEMPLATE_USE_AUTO_WATERMARK"];
	}

	else{
		$template_use_watermark = "N";
	}

	//watermark types
	$arWatermarkTypes = array(
		"image" => GetMessage("SETTINGS_WATERMARK_TYPE_IMAGE"),
		"text" => GetMessage("SETTINGS_WATERMARK_TYPE_TEXT"),
	);

	//select watermark position
	if(!empty($arResult["CURRENT_SETTINGS"]["TEMPLATE_WATERMARK_TYPE"])){
		$template_watermark_type = $arResult["CURRENT_SETTINGS"]["TEMPLATE_WATERMARK_TYPE"];
	}

	else{
		$template_watermark_type = key($arWatermarkTypes);
	}

	//watermark types
	$arWatermarkSizes = array(
		"big" => GetMessage("SETTINGS_WATERMARK_SIZE_BIG"),
		"medium" => GetMessage("SETTINGS_WATERMARK_TYPE_MEDIUM"),
		"small" => GetMessage("SETTINGS_WATERMARK_SIZE_SMALL"),
		"real" => GetMessage("SETTINGS_WATERMARK_TYPE_REAL"),
	);

	//select watermark position
	if(!empty($arResult["CURRENT_SETTINGS"]["TEMPLATE_WATERMARK_SIZE"])){
		$template_watermark_size = $arResult["CURRENT_SETTINGS"]["TEMPLATE_WATERMARK_SIZE"];
	}

	else{
		$template_watermark_size = key($arWatermarkSizes);
	}

	//watermark fill
	$arWatermarkFill = array(
		"exact" => GetMessage("SETTINGS_WATERMARK_FILL_EXACT"),
		"resize" => GetMessage("SETTINGS_WATERMARK_FILL_RESIZE"),
		"repeat" => GetMessage("SETTINGS_WATERMARK_FILL_REPEAT"),
	);

	//select watermark fill
	if(!empty($arResult["CURRENT_SETTINGS"]["TEMPLATE_WATERMARK_FILL"])){
		$template_watermark_fill = $arResult["CURRENT_SETTINGS"]["TEMPLATE_WATERMARK_FILL"];
	}

	else{
		$template_watermark_fill = key($arWatermarkFill);
	}

	//watermark positions
	$arWatermarkPositions = array(
		"topleft" => GetMessage("SETTINGS_WATERMARK_POSITION_TOPLEFT"),
		"topcenter" => GetMessage("SETTINGS_WATERMARK_POSITION_TOPCENTER"),
		"topright" => GetMessage("SETTINGS_WATERMARK_POSITION_TOPRIGHT"),
		"centerleft" => GetMessage("SETTINGS_WATERMARK_POSITION_CENTERLEFT"),
		"center" => GetMessage("SETTINGS_WATERMARK_POSITION_CENTER"),
		"centerright" => GetMessage("SETTINGS_WATERMARK_POSITION_CENTERRIGHT"),
		"bottomleft" => GetMessage("SETTINGS_WATERMARK_POSITION_BOTTOMLEFT"),
		"bottomcenter" => GetMessage("SETTINGS_WATERMARK_POSITION_BOTTOMCENTER"),
		"bottomright" => GetMessage("SETTINGS_WATERMARK_POSITION_BOTTOMRIGHT")
	);

	//select watermark position
	if(!empty($arResult["CURRENT_SETTINGS"]["TEMPLATE_WATERMARK_POSITION"])){
		$template_watermark_position = $arResult["CURRENT_SETTINGS"]["TEMPLATE_WATERMARK_POSITION"];
	}

	else{
		$template_watermark_position = key($arWatermarkPositions);
	}

?>
