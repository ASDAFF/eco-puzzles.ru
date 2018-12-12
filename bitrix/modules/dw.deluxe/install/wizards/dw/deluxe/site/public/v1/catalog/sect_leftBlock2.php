<div id="left">
	<a href="/catalog/" class="heading opener orange <?$APPLICATION->ShowViewContent("hide");?>"><?=GetMessage("DRESS_CATALOG")?><ins class="arrow"></ins></a>
	<div class="collapsed">
		<?$APPLICATION->IncludeComponent("bitrix:menu", "leftMenu", Array(
			"ROOT_MENU_TYPE" => "left",
				"MENU_CACHE_TYPE" => "N",
				"MENU_CACHE_TIME" => "3600000",
				"MENU_CACHE_USE_GROUPS" => "Y",
				"MENU_CACHE_GET_VARS" => "",
				"MAX_LEVEL" => "4",
				"CHILD_MENU_TYPE" => "left",
				"USE_EXT" => "Y",
				"DELAY" => "N",
				"ALLOW_MULTI_SELECT" => "N",
			),
			false
		);?>
		<?$APPLICATION->IncludeComponent("bitrix:menu", "leftSubMenu", Array(
			"ROOT_MENU_TYPE" => "left2",
				"MENU_CACHE_TYPE" => "N",
				"MENU_CACHE_TIME" => "3600000",
				"MENU_CACHE_USE_GROUPS" => "Y",
				"MENU_CACHE_GET_VARS" => "",
				"MAX_LEVEL" => "1",
				"CHILD_MENU_TYPE" => "left2",
				"USE_EXT" => "Y",
				"DELAY" => "N",
				"ALLOW_MULTI_SELECT" => "N",
			),
			false
		);?>
	</div>
	<?$APPLICATION->ShowViewContent("filter");?>
</div>