<?if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();
$this->setFrameMode(true);$arPlacemarks = array();?>
<?if(!empty($arResult["STORES"])):?>
	<div id="fastViewStores">
		<div class="fastViewStoresContainer">
			<div class="fastViewStoresHeading"><?=GetMessage("STORES_HEADING")?> <a href="#" class="fastViewStoresExit"></a></div>
			<?foreach($arResult["STORES"] as $pid => $arProperty):?>
				<?if(!empty($arProperty["COORDINATES"]["GPS_S"]) && !empty($arProperty["COORDINATES"]["GPS_N"])){
					$gpsN = substr(doubleval($arProperty["COORDINATES"]["GPS_N"]), 0, 15);
					$gpsS = substr(doubleval($arProperty["COORDINATES"]["GPS_S"]), 0, 15);
					$arPlacemarks[] = array("LON" => $gpsS, "LAT"=>$gpsN, "TEXT"=>$arProperty["TITLE"]);
				}
				?>
			<?endforeach;?>
			<?if(!empty($arPlacemarks)):?>
				<div class="fastViewStoresMap">
					<?$APPLICATION->IncludeComponent(
						"bitrix:map.yandex.view", 
						"fastView", 
						array(
							"INIT_MAP_TYPE" => "MAP",
							"MAP_DATA" => serialize(array("yandex_lat" => $gpsN, "yandex_lon" => $gpsS, "yandex_scale" => 8, "PLACEMARKS" => $arPlacemarks)),
							"DEV_MODE"=>"Y",
							"YANDEX_MAP_VERSION" => $arParams["YANDEX_MAP_VERSION"],
							"MAP_WIDTH" => "776",
							"MAP_HEIGHT" => "300",
							"CONTROLS" => array(
								0 => "ZOOM",
								1 => "MINIMAP",
								2 => "TYPECONTROL",
								3 => "SCALELINE",
							),
							"OPTIONS" => array(
								0 => "ENABLE_SCROLL_ZOOM",
								1 => "ENABLE_DBLCLICK_ZOOM",
								2 => "ENABLE_DRAGGING",
							),
							"MAP_ID" => randString(9)
						),
						false
					);?>
				</div>
			<?endif;?>
			<div class="storeTable">
				<div class="storeTableContainer">
					<div class="storeTableContainerOverflow">
						<table class="storeTableList">
							<tbody>
							<tr>
								<th class="name"><?=GetMessage("STORES_NAME")?></th>
								<th><?=GetMessage("STORES_PHONE")?></th>
								<th class="amount"><?=GetMessage("STORES_AMOUNT")?></th>
							</tr>
								<?foreach($arResult["STORES"] as $pid => $arProperty):?>
									<?if($arParams["SHOW_EMPTY_STORE"] == "N" && isset($arProperty["REAL_AMOUNT"]) && $arProperty["REAL_AMOUNT"] <= 0):?>
									<?else:?>
										<tr>
											<td class="name"><a href="<?=$arProperty["URL"]?>"><span><?=$arProperty["TITLE"]?></span></a></td>
											<td><?=$arProperty["PHONE"]?></td>
											<td<?if($arProperty["REAL_AMOUNT"] > 0):?> class="amount green"<?else:?> class="amount red"<?endif;?>><img src="<?=SITE_TEMPLATE_PATH?>/images/<?if($arProperty["REAL_AMOUNT"] > 0):?>inStock<?else:?>outOfStock<?endif;?>.png" alt="<?=$arProperty["AMOUNT"]?>" class="icon"><?=$arProperty["AMOUNT"]?></td>
										</tr>
									<?endif;?>
								<?endforeach;?>
							</tbody>
						</table>
					</div>
				</div>
			</div>
		</div>
		<link rel="stylesheet" href="<?=$templateFolder?>/ajax_styles.css">
	</div>
<?endif;?>