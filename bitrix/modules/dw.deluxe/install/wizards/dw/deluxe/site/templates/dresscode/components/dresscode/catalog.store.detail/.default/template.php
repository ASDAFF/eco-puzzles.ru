<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();?>
<div id="storeDetail">
	<div class="storeDetailContainer">
		<?if(!empty($arResult["IMAGE_ID"])):?>
			<?$arStoreImage = CFile::ResizeImageGet($arResult["IMAGE_ID"], array("width" => 600, "height" => 600), BX_RESIZE_IMAGE_PROPORTIONAL, false);?>
			<?$bigPicturePath = CFile::GetPath($arResult["IMAGE_ID"]);?>
			<div class="storePictureContainer">
				<a href="#" data-path="<?=$bigPicturePath?>" class="oZoomer"><img src="<?=$arStoreImage["src"]?>" alt="<?=$arResult["TITLE"]?>" title="<?=$arResult["TITLE"]?>"></a>
			</div>
		<?endif;?>
		<div class="storeInformation">
			<?if(!empty($arResult["ADDRESS"])):?>
				<div class="storeInformationName"><?=$arResult["ADDRESS"]?></div>
			<?endif;?>
			<?if(!empty($arResult["DESCRIPTION"])):?>
				<div class="storeInformationDescription"><?=$arResult["DESCRIPTION"]?></div>
			<?endif;?>
			<?if(($arResult["GPS_N"]) != 0 && ($arResult["GPS_S"]) != 0):?>
				<div class="showByMap"><a href="#" class="showByMapLink" title="<?=$arResult["ADDRESS"]?>"><?=GetMessage("S_SHOW_BY_MAP")?></a></div>
			<?endif;?>
		</div>
	</div>
	<div class="storeTools">
		<div class="storeToolsContainer">
			<?if(!empty($arResult["SCHEDULE"])):?>
				<div class="storeToolsItem">
					<div class="storeToolsItemTable">
						<div class="storeToolsItemColumn">
							<img src="<?=$templateFolder."/images/time.png";?>" alt="<?=$arResult["SCHEDULE"]?>" title="<?=$arResult["SCHEDULE"]?>" class="storeListIcon">
						</div>
						<div class="storeToolsItemColumn">
							<span class="storeToolsItemLabel"><?=GetMessage("S_SCHEDULE")?></span>
							<?=$arResult["SCHEDULE"]?>
						</div>
					</div>
				</div>
			<?endif;?>
			<?if(!empty($arResult["PHONE"])):?>
				<div class="storeToolsItem">
					<div class="storeToolsItemTable">
						<div class="storeToolsItemColumn">
							<img src="<?=$templateFolder."/images/phone.png";?>" alt="<?=$arResult["PHONE"]?>" title="<?=$arResult["PHONE"]?>" class="storeListIcon">
						</div>
						<div class="storeToolsItemColumn">
							<span class="storeToolsItemLabel"><?=GetMessage("S_PHONE")?></span>
							<?=$arResult["PHONE"]?>
						</div>
					</div>
				</div>
			<?endif;?>
			<?if(!empty($arResult["EMAIL"])):?>
				<div class="storeToolsItem">
					<div class="storeToolsItemTable">
						<div class="storeToolsItemColumn">
							<img src="<?=$templateFolder."/images/mail.png";?>" alt="<?=$arResult["EMAIL"]?>" title="<?=$arResult["EMAIL"]?>" class="storeListIcon">
						</div>
						<div class="storeToolsItemColumn">
							<span class="storeToolsItemLabel"><?=GetMessage("S_EMAIL")?></span>
							<a href="mailto:<?=$arResult["EMAIL"]?>"><?=$arResult["EMAIL"]?></a>
						</div>
					</div>
				</div>
			<?endif;?>
		</div>
	</div>

	<?if(($arResult["GPS_N"]) != 0 && ($arResult["GPS_S"]) != 0):?>
		<div class="storeDetailMap">
			<?
			$gpsN = substr($arResult["GPS_N"], 0, 15);
			$gpsS = substr($arResult["GPS_S"], 0, 15);
			$gpsText = $arResult["ADDRESS"];
			$gpsTextLen = strlen($arResult["ADDRESS"]);

			if($arResult["MAP"] == 0){
				$APPLICATION->IncludeComponent("bitrix:map.yandex.view", ".default", array(
						"INIT_MAP_TYPE" => "MAP",
						"MAP_DATA" => serialize(array("yandex_lat" => $gpsN,"yandex_lon" => $gpsS,"yandex_scale" => 11,"PLACEMARKS" => array( 0=>array("LON" => $gpsS,"LAT" => $gpsN,"TEXT" => $arResult["ADDRESS"])))),
						"MAP_WIDTH" => "auto",
						"MAP_HEIGHT" => "500",
						"CONTROLS" => array(
							0 => "ZOOM",
						),
						"OPTIONS" => array(
							0 => "ENABLE_DBLCLICK_ZOOM",
							1 => "ENABLE_DRAGGING",
						),
						"MAP_ID" => ""
					),
					false
				);
			}else{
				$APPLICATION->IncludeComponent(
					"bitrix:map.google.view", 
					".default", 
					array(
						"INIT_MAP_TYPE" => "TERRAIN",
						"MAP_DATA" => serialize(array("google_lat"=>$gpsN,"google_lon"=>$gpsS,"google_scale"=>11,"PLACEMARKS"=>array(0=>array("LON"=>$gpsS,"LAT"=>$gpsN,"TEXT"=>$arResult["ADDRESS"])))),
						"MAP_WIDTH" => "auto",
						"MAP_HEIGHT" => "auto",
						"CONTROLS" => array(
							0 => "SMALL_ZOOM_CONTROL",
							1 => "TYPECONTROL",
							2 => "SCALELINE",
						),
						"OPTIONS" => array(
							0 => "ENABLE_DBLCLICK_ZOOM",
							1 => "ENABLE_DRAGGING",
						),
						"MAP_ID" => "",
						"COMPONENT_TEMPLATE" => ".default"
					),
					false
				);
			}?>
		</div>
	<?endif;?>
	<div class="allStores">
		<span class="allStoresText"><?=GetMessage("S_CONTACT")?></span> <a href="/stores/" class="storesMoreLink"><?=GetMessage("S_ALL_STORES")?></a>
	</div>
</div>