<?if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) die();?>
<?$this->setFrameMode(true);?>
<?if($arParams["INCLUDE_YANDEX_API"] == "Y"):?>
	<script>
		var getPositionIncludeApi = true;
	</script>
<?endif;?>
<li>
	<div class="user-geo-position">
		<div class="user-geo-position-label"><?=GetMessage("YOU_GEO_LOCATION_LABEL")?></div><div class="user-geo-position-value"><a href="#" class="user-geo-position-value-link"><?if($arParams["INCLUDE_YANDEX_API"] == "Y"):?><?=GetMessage("DETECT_YOU_GEO_LOCATION")?><?else:?><?=$_SESSION["USER_GEO_POSITION"]["city"]?><?endif;?></a></div>
	</div>
</li>
<li class="null">
	<div id="geo-location-window" class="hidden">
		<div class="geo-location-window-container">
			<div class="geo-location-window-container-bg">
				<div class="geo-location-window-heading"><?=GetMessage("YOU_GEO_LOCATION_WINDOW_LABEL")?> <a href="#" class="geo-location-window-exit"></a></div>
				<div class="geo-location-window-wp">
					<?if(!empty($arResult["DEFAULT_LOCATIONS"])):?>
						<div class="geo-location-window-list">
							<?foreach ($arResult["DEFAULT_LOCATIONS"] as $idl => $arNextLocation):?>
								<div class="geo-location-window-list-item">
									<a href="#" class="geo-location-window-list-item-link<?if(!empty($_SESSION["USER_GEO_POSITION"]["locationID"]) && $arNextLocation["ID"] == $_SESSION["USER_GEO_POSITION"]["locationID"]):?> selected<?endif;?>" data-id="<?=$arNextLocation["ID"]?>" data-parse-value="<?=$arNextLocation["NAME"]?>"><span><?=$arNextLocation["NAME"]?></span></a>
								</div>
							<?endforeach;?>
						</div>
					<?endif;?>
					<div class="geo-location-window-search">
						<input type="text" value="<?if(!empty($_SESSION["USER_GEO_POSITION"]["city"])):?><?=$_SESSION["USER_GEO_POSITION"]["city"]?><?endif;?>"<?if(empty($_SESSION["USER_GEO_POSITION"]["city"])):?> placeholder="<?=GetMessage("YOU_GEO_LOCATION_WINDOW_LABEL")?>" <?endif;?>class="geo-location-window-search-input"<?if(!empty($_SESSION["USER_GEO_POSITION"]["locationID"])):?>data-id="<?=$_SESSION["USER_GEO_POSITION"]["locationID"]?>"<?endif;?>>
						<div class="geo-location-window-search-values-cn">
							<div class="geo-location-window-search-values"></div>
						</div>
					</div>
					<?if(!empty($_SESSION["USER_GEO_POSITION"]["city"])):?>
						<div class="geo-location-window-city-container">
							<div class="geo-location-window-city-label"><?=GetMessage("YOU_GEO_LOCATION_CITY_LABEL")?></div>
							<div class="geo-location-window-city-value"><?=$_SESSION["USER_GEO_POSITION"]["city"]?></div>
						</div>
					<?endif;?>
					<div class="geo-location-window-button-container">
						<a href="#" class="geo-location-window-button<?if(empty($_SESSION["USER_GEO_POSITION"]["city"])):?> disabled<?endif;?>"><?=GetMessage("YOU_GEO_LOCATION_BUTTON_LABEL")?>
							<span id="geo-location-window-fast-loader">
								<span class="f_circleG" id="frotateG_01"></span>
								<span class="f_circleG" id="frotateG_02"></span>
								<span class="f_circleG" id="frotateG_03"></span>
								<span class="f_circleG" id="frotateG_04"></span>
								<span class="f_circleG" id="frotateG_05"></span>
								<span class="f_circleG" id="frotateG_06"></span>
								<span class="f_circleG" id="frotateG_07"></span>
								<span class="f_circleG" id="frotateG_08"></span>
							</span>
						</a>
					</div>
				</div>
			</div>
		</div>
	</div>
</li>
<script>
	var geoPositionAjaxDir = "<?=$componentPath?>";
	var geoPositionEngine = "<?=$arParams["GEO_IP_PARAMS"]?>"
</script>