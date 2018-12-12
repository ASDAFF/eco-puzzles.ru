<?if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();
if(strlen($arResult["ERROR_MESSAGE"]) > 0)
	ShowError($arResult["ERROR_MESSAGE"]);
	
	$arPlacemarks = array();
	$showPictures = 0;
	$gpsN = '';
	$gpsS = '';

?>
	<?if(is_array($arResult["STORES"]) && !empty($arResult["STORES"])):?>
		<?foreach($arResult["STORES"] as $pid=>$arProperty):?>
			<?if($arProperty["GPS_S"] != 0 && $arProperty["GPS_N"] != 0){
				$gpsN = substr(doubleval($arProperty["GPS_N"]), 0, 15);
				$gpsS = substr(doubleval($arProperty["GPS_S"]), 0, 15);
				$arPlacemarks[] = array("LON" => $gpsS,"LAT" => $gpsN,"TEXT" => $arProperty["TITLE"]);
				if(!empty($arProperty["DETAIL_IMG"])){
					$showPictures = true;
				}
			}
			?>
		<?endforeach;?>
		<?
		if ($arResult['VIEW_MAP']):?>
			<div id="storeListMap">
				<?if($arResult["MAP"] == 0){
					$APPLICATION->IncludeComponent("bitrix:map.yandex.view", ".default", array(
							"INIT_MAP_TYPE" => "MAP",
							"MAP_DATA" => serialize(array("yandex_lat"=>$gpsN,"yandex_lon"=>$gpsS,"yandex_scale"=>10,"PLACEMARKS" => $arPlacemarks)),
							"MAP_WIDTH" => "auto",
							"MAP_HEIGHT" => "500",
							"CONTROLS" => array(
								0 => "ZOOM",
							),
							"OPTIONS" => array(
								// 0 => "ENABLE_SCROLL_ZOOM",
								1 => "ENABLE_DBLCLICK_ZOOM",
								2 => "ENABLE_DRAGGING",
							),
							"MAP_ID" => ""
						),
						$component,
						array("HIDE_ICONS" => "Y")
					);
				}else{
					$APPLICATION->IncludeComponent("bitrix:map.google.view", ".default", array(
							"INIT_MAP_TYPE" => "MAP",
							"MAP_DATA" => serialize(array("google_lat"=>$gpsN,"google_lon"=>$gpsS,"google_scale"=>10,"PLACEMARKS" => $arPlacemarks)),
							"MAP_WIDTH" => "auto",
							"MAP_HEIGHT" => "500",
							"CONTROLS" => array(
								0 => "ZOOM",
							),
							"OPTIONS" => array(
								0 => "ENABLE_SCROLL_ZOOM",
								1 => "ENABLE_DBLCLICK_ZOOM",
								2 => "ENABLE_DRAGGING",
							),
							"MAP_ID" => ""
						),
						$component,
						array("HIDE_ICONS" => "Y")
					);
				}?>
			</div>
		<?endif;?>
		<div id="storesList">
			<?foreach ($arResult["STORES"] as $ins => $arNextStore):?>
				<div class="storesListItem">
					<div class="storesListItemLeft">
						<div class="storesListItemContainer">
							<?
								if(!empty($arNextStore["DETAIL_IMG"])){
									$arNextStoreImage = CFile::ResizeImageGet($arNextStore["DETAIL_IMG"], array("width" => 170, "height" => 100), BX_RESIZE_IMAGE_PROPORTIONAL, false);
								}else{
									$arNextStoreImage["src"] = $templateFolder."/images/empty.png";
								}
							?>
							<?if(!empty($showPictures)):?>
								<div class="storesListItemPicture">
									<a href="<?=$arNextStore["URL"]?>" class="storesListTableLink"><img src="<?=$arNextStoreImage["src"]?>" alt="<?=$arNextStore["TITLE"]?>" title="<?=$arNextStore["TITLE"]?>"></a>
								</div>
							<?endif;?>
							<div class="storesListItemName">
								<a href="<?=$arNextStore["URL"]?>" class="storesListTableLink"><?=$arNextStore["TITLE"]?></a>
								<?if(!empty($arNextStore["DESCRIPTION"])):?>
									<p class="storeItemDescription"><?=$arNextStore["DESCRIPTION"]?></p>
								<?endif;?>
								<div class="storesListItemScheduleSmall">
									<img src="<?=$templateFolder."/images/timeSmall.png";?>" alt="<?=$arNextStore["SCHEDULE"]?>" title="<?=$arNextStore["SCHEDULE"]?>" class="storeListIconSmall">
									<span class="storesListItemScheduleLabel"><?=$arNextStore["SCHEDULE"]?></span>
								</div>
							</div>
						</div>
					</div>
					<div class="storesListItemRight">
						<div class="storesListItemContainer">
							<div class="storesListItemSchedule">
								<img src="<?=$templateFolder."/images/time.png";?>" alt="<?=$arNextStore["SCHEDULE"]?>" title="<?=$arNextStore["SCHEDULE"]?>" class="storeListIcon">
								<span class="storesListItemLabel"><?=$arNextStore["SCHEDULE"]?></span>
							</div>
							<div class="storesListItemPhone">
								<span class="storesListItemPhoneLabel"><?=GetMessage("STORES_LIST_TELEPHONE")?></span>
								<img src="<?=$templateFolder."/images/phone.png";?>" alt="<?=$arNextStore["PHONE"]?>" title="<?=$arNextStore["PHONE"]?>" class="storeListIcon">
								<span class="storesListItemLabel"><?=$arNextStore["PHONE"]?></span>
							</div>
							<div class="storesListItemEmail">
								<img src="<?=$templateFolder."/images/mail.png";?>" alt="<?=$arNextStore["EMAIL"]?>" title="<?=$arNextStore["EMAIL"]?>" class="storeListIcon">
								<span class="storesListItemLabel">
									<a href="mailto:<?=$arNextStore["EMAIL"]?>" class="storesListTableMailLink"><?=$arNextStore["EMAIL"]?></a>
								</span>
							</div>
						</div>
					</div>
				</div>
			<?endforeach;?>
		</div>
	<?endif;?>