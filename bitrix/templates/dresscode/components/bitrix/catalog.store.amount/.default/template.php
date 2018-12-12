<?if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();
$this->setFrameMode(true);$arPlacemarks = array();?>
<?if(!empty($arResult["STORES"]) && $arResult["SHOW_STORES"] == "Y"):?>
	<div id="stores">
		<div class="heading"><?=GetMessage("STORES_HEADING")?></div>
		<div class="wrap">
			<table class="storeTable">
				<tbody>
				<tr>
					<th class="name"><?=GetMessage("STORES_NAME")?></th>
					<th><?=GetMessage("STORES_GRAPH")?></th>
					<th><?=GetMessage("STORES_PHONE")?></th>
					<th class="amount"><?=GetMessage("STORES_AMOUNT")?></th>
				</tr>
					<?foreach($arResult["STORES"] as $pid => $arProperty):?>
						<?
						if($arProperty["COORDINATES"]["GPS_S"] != 0 && $arProperty["COORDINATES"]["GPS_N"] != 0){
							$gpsN = substr(doubleval($arProperty["COORDINATES"]["GPS_N"]), 0, 15);
							$gpsS = substr(doubleval($arProperty["COORDINATES"]["GPS_S"]), 0, 15);
							$arPlacemarks[] = array("LON" => $gpsS, "LAT"=>$gpsN, "TEXT"=>$arProperty["TITLE"]);
						}
						?>
						<?$image = CFile::ResizeImageGet($arProperty["IMAGE_ID"], array('width' => 50, 'height' => 50), BX_RESIZE_IMAGE_PROPORTIONAL, false);?>
						<?if(!($arParams["SHOW_EMPTY_STORE"] == "N" && isset($arProperty["REAL_AMOUNT"]) && $arProperty["REAL_AMOUNT"] <= 0)):?>
							<tr>
								<td class="name"><a href="<?=$arProperty["URL"]?>"> <?=$arProperty["TITLE"]?></a></td>
								<td><?=$arProperty["SCHEDULE"]?></td>
								<td><?=$arProperty["PHONE"]?></td>
								<td<?if($arProperty["REAL_AMOUNT"] > 0):?> class="amount green"<?else:?> class="amount red"<?endif;?>><img src="<?=SITE_TEMPLATE_PATH?>/images/<?if($arProperty["REAL_AMOUNT"] > 0):?>inStock<?else:?>outOfStock<?endif;?>.png" alt="<?=$arProperty["AMOUNT"]?>" class="icon"><?=$arProperty["AMOUNT"]?></td>
							</tr>
						<?endif;?>
					<?endforeach;?>
				</tbody>
			</table>
		</div>
		<div id="storeMap">
			<?$APPLICATION->IncludeComponent(
						"bitrix:map.yandex.view", 
						"fastView", 
				array(
					"COMPONENT_TEMPLATE" => ".default",
					"CONTROLS" => array(
						"SMALL_ZOOM_CONTROL",
						"TYPECONTROL", 
						"SCALELINE"
					),
					"INIT_MAP_TYPE" => "ROADMAP",
					"MAP_DATA" => serialize(array("yandex_lat" => $gpsN, "yandex_lon" => $gpsS, "yandex_scale" => 10, "PLACEMARKS" => $arPlacemarks)),
					"DEV_MODE"=>"Y",
					"MAP_HEIGHT" => "auto",
					"MAP_WIDTH" => "auto",
					"OPTIONS" => array(
						"ENABLE_DBLCLICK_ZOOM",
						"ENABLE_DRAGGING",
						"ENABLE_KEYBOARD"
					),
					"MAP_ID" => rand(0, 9999999)
				),
				false
			);?>
		</div>
	</div>
<?endif;?>