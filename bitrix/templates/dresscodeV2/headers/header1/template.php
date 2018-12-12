<?$APPLICATION->SetAdditionalCSS(SITE_TEMPLATE_PATH."/headers/header1/css/style.css");?>
<div id="topHeader"<?if($TEMPLATE_HEADER_COLOR != ""):?> class="color_<?=$TEMPLATE_HEADER_COLOR?>"<?endif;?>>
	<div class="limiter">
		<?$APPLICATION->IncludeComponent("bitrix:menu", "topMenu", Array(
			"ROOT_MENU_TYPE" => "top",
				"MENU_CACHE_TYPE" => "N",
				"MENU_CACHE_TIME" => "3600000",
				"MENU_CACHE_USE_GROUPS" => "Y",
				"MENU_CACHE_GET_VARS" => "",
				"MAX_LEVEL" => "1",
				"CHILD_MENU_TYPE" => "top",
				"USE_EXT" => "N",
				"DELAY" => "N",
				"ALLOW_MULTI_SELECT" => "N",
				"CACHE_SELECTED_ITEMS" => "N"
			),
			false
		);?>
		<ul id="topService"<?if($TEMPLATE_SUBHEADER_COLOR != "default"):?> class="color_<?=$TEMPLATE_SUBHEADER_COLOR?>"<?endif;?>>
			<?$APPLICATION->IncludeComponent("dresscode:sale.geo.positiion", "", array(),
				false,
				array(
				"ACTIVE_COMPONENT" => "Y"
				)
			);?>
			<?$APPLICATION->IncludeComponent("bitrix:system.auth.form", "top", Array(
				"REGISTER_URL" => "",
					"FORGOT_PASSWORD_URL" => "",
					"PROFILE_URL" => "",
					"SHOW_ERRORS" => "N",
				),
				false
			);?>
		</ul>
	</div>
</div>
<div id="subHeader"<?if($TEMPLATE_SUBHEADER_COLOR != "default"):?> class="color_<?=$TEMPLATE_SUBHEADER_COLOR?>"<?endif;?>>
	<div class="limiter">
		<div id="logo">
			<?$APPLICATION->IncludeFile(SITE_DIR."sect_top_logo.php", Array(), Array("MODE" => "text", "NAME" => GetMessage("SECT_TOP_LOGO"), "TEMPLATE" => "sect_top_logo.php"));?>
		</div>
		<div id="headerTools">
			<ul class="tools">
				<li class="search">
					<div class="wrap">
						<a href="#" class="icon" id="openSearch"></a>
					</div>
				</li>
				<li class="telephone">
					<div class="wrap">
						<a href="<?=SITE_DIR?>callback/" class="icon callBack"></a>
						<div class="nf">
							<?$APPLICATION->IncludeComponent(
								"bitrix:main.include",
								".default",
								array(
									"AREA_FILE_SHOW" => "sect",
									"AREA_FILE_SUFFIX" => "phone",
									"AREA_FILE_RECURSIVE" => "Y",
									"EDIT_TEMPLATE" => ""
								),
								false
							);?>
						</div>
					</div>
				</li>
				<li class="wishlist">
					<div id="flushTopwishlist">
						<?$APPLICATION->IncludeComponent("dresscode:favorite.line", ".default", Array(
							),
							false
						);?>
					</div>
				</li>
				<li class="compare">
					<div id="flushTopCompare">
						<?$APPLICATION->IncludeComponent("dresscode:compare.line", ".default", Array(

							),
							false
						);?>
					</div>
				</li>
         	 	<li class="cart">
         	 		<div id="flushTopCart"><?$APPLICATION->IncludeComponent(
				"bitrix:sale.basket.basket.line", 
				"topCart", 
				array(
					"HIDE_ON_BASKET_PAGES" => "N",
					"PATH_TO_BASKET" => SITE_DIR."personal/cart/",
					"PATH_TO_ORDER" => SITE_DIR."personal/order/make/",
					"PATH_TO_PERSONAL" => SITE_DIR."personal/",
					"PATH_TO_PROFILE" => SITE_DIR."personal/",
					"PATH_TO_REGISTER" => SITE_DIR."login/",
					"POSITION_FIXED" => "N",
					"SHOW_AUTHOR" => "N",
					"SHOW_EMPTY_VALUES" => "Y",
					"SHOW_NUM_PRODUCTS" => "Y",
					"SHOW_PERSONAL_LINK" => "N",
					"SHOW_PRODUCTS" => "Y",
					"SHOW_TOTAL_PRICE" => "Y",
					"COMPONENT_TEMPLATE" => "topCart",
					"SHOW_DELAY" => "N",
					"SHOW_NOTAVAIL" => "N",
					"SHOW_SUBSCRIBE" => "N",
					"SHOW_IMAGE" => "Y",
					"SHOW_PRICE" => "Y",
					"SHOW_SUMMARY" => "Y"
				),
				false
			);?></div></li>
			</ul>
		</div>
		<?$APPLICATION->IncludeComponent(
			"bitrix:main.include",
			".default",
			array(
				"AREA_FILE_SHOW" => "sect",
				"AREA_FILE_SUFFIX" => "searchLine",
				"AREA_FILE_RECURSIVE" => "Y",
				"EDIT_TEMPLATE" => ""
			),
			false
		);?>
	</div>
</div>
<div class="menuContainerColor<?if(!empty($TEMPLATE_CATALOG_MENU_COLOR) && $TEMPLATE_CATALOG_MENU_COLOR != "default"):?> color_<?=$TEMPLATE_CATALOG_MENU_COLOR?><?endif;?>">
	<?$APPLICATION->IncludeComponent("bitrix:menu", "catalogMenu", array(
		"ROOT_MENU_TYPE" => "left",
			"MENU_CACHE_TYPE" => "N",
			"MENU_CACHE_TIME" => "3600",
			"MENU_CACHE_USE_GROUPS" => "Y",
			"MENU_CACHE_GET_VARS" => "",
			"MAX_LEVEL" => "4",
			"CHILD_MENU_TYPE" => "top",
			"USE_EXT" => "Y",
			"DELAY" => "N",
			"ALLOW_MULTI_SELECT" => "N",
			"CACHE_SELECTED_ITEMS" => "N",
			"COMPONENT_TEMPLATE" => "catalogMenu"
		),
		false,
		array(
		"ACTIVE_COMPONENT" => "Y"
		)
	);?>
</div>