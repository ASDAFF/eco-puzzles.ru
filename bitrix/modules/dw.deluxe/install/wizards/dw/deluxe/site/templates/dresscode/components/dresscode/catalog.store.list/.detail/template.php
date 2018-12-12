<?if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) die();

	if(strlen($arResult["ERROR_MESSAGE"]) > 0){
		ShowError($arResult["ERROR_MESSAGE"]);
	}

	$arPlacemarks = array();
	$showPictures = 0;
	$gpsN = '';
	$gpsS = '';

?>
	<?if(is_array($arResult["STORES"]) && !empty($arResult["STORES"])):?>
		<?foreach($arResult["STORES"] as $pid=>$arProperty):?>
			<?if(!empty($arProperty["DETAIL_IMG"])){
				$showPictures = true;
			}?>
		<?endforeach;?>
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