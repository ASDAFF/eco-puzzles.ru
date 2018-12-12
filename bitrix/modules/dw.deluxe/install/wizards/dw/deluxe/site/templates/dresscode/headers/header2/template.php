<?$APPLICATION->SetAdditionalCSS(SITE_TEMPLATE_PATH."/headers/header2/css/style.css");?>
<?$APPLICATION->SetAdditionalCSS(SITE_TEMPLATE_PATH."/headers/header2/css/types/".$TEMPLATE_HEADER_TYPE.".css");?>
<div id="topHeader2"<?if($TEMPLATE_HEADER_COLOR != "default"):?> class="color_<?=$TEMPLATE_HEADER_COLOR?>"<?endif;?>>
	<div class="limiter">
		<div class="leftColumn">
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
		</div>
		<div class="rightColumn">
			<ul id="topService">
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
</div>
<div id="subHeader2"<?if($TEMPLATE_SUBHEADER_COLOR != "default"):?> class="color_<?=$TEMPLATE_SUBHEADER_COLOR?>"<?endif;?>>
	<div class="limiter">
		<div class="subTable">
			<div class="subTableColumn">
				<div class="subContainer">
					<div id="logo">
						<?$APPLICATION->IncludeComponent(
							"bitrix:main.include",
							".default",
							array(
								"AREA_FILE_SHOW" => "sect",
								"AREA_FILE_SUFFIX" => "logo",
								"AREA_FILE_RECURSIVE" => "Y",
								"EDIT_TEMPLATE" => ""
							),
							false
						);?>
					</div>
					<div id="topHeading">
						<div class="vertical">
							<?$APPLICATION->IncludeComponent(
								"bitrix:main.include",
								".default",
								array(
									"AREA_FILE_SHOW" => "sect",
									"AREA_FILE_SUFFIX" => "heading",
									"AREA_FILE_RECURSIVE" => "Y",
									"EDIT_TEMPLATE" => ""
								),
								false
							);?>
						</div>
					</div>
					<div id="topCallback" class="telephone">
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
					</div>
				</div>
			</div>
			<div class="subTableColumn">
				<div id="topSearchLine">
					<?$APPLICATION->IncludeComponent(
						"bitrix:main.include",
						".default",
						array(
							"AREA_FILE_SHOW" => "sect",
							"AREA_FILE_SUFFIX" => "searchLine2",
							"AREA_FILE_RECURSIVE" => "Y",
							"EDIT_TEMPLATE" => ""
						),
						false
					);?>
				</div>
			</div>
			<div class="subTableColumn">
				<div class="topWishlist">
					<div id="flushTopwishlist">
						<?$APPLICATION->IncludeComponent("dresscode:favorite.line", "version2", Array(), false);?>
					</div>
				</div>
				<div class="topCompare">
					<div id="flushTopCompare">
						<?$APPLICATION->IncludeComponent("dresscode:compare.line", "version2", Array(),	false);?>
					</div>
				</div>
				<div class="cart">
					<div id="flushTopCart">
						<?$APPLICATION->IncludeComponent(
							"bitrix:sale.basket.basket.line",
							"topCart2",
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
						);?>
					</div>
				</div>
			</div>
		</div>
	</div>
</div>