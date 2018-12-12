<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true) die();
	$this->setFrameMode(true);
?>
<?if ($arParams["DISPLAY_TOP_PAGER"]){?><? echo $arResult["NAV_STRING"];?><?}?>
<?if(!empty($arResult["ITEMS"])):?>
		<div id="brandList">
			<div class="items">
				<?foreach($arResult["ITEMS"] as $arElement):?>
					<?
						$this->AddEditAction($arElement["ID"], $arElement["EDIT_LINK"], CIBlock::GetArrayByID($arElement["IBLOCK_ID"], "ELEMENT_EDIT"));
						$this->AddDeleteAction($arElement["ID"], $arElement["DELETE_LINK"], CIBlock::GetArrayByID($arElement["IBLOCK_ID"], "ELEMENT_DELETE"), array());
						$picture = CFile::ResizeImageGet($arElement["DETAIL_PICTURE"], array("width" => 150, "height" => 120), BX_RESIZE_IMAGE_PROPORTIONAL, false); 
						$picture["src"] = !empty($picture["src"]) ? $picture["src"] : SITE_TEMPLATE_PATH."/images/empty.png"; 
					?>
					<div class="item" id="<?=$this->GetEditAreaId($arElement["ID"]);?>">
						<div class="tabloid">
							<?if(!empty($arElement["PROPERTIES"]["OFFERS"]["VALUE"])):?>
								<div class="markerContainer">
									<?foreach ($arElement["PROPERTIES"]["OFFERS"]["VALUE"] as $ifv => $marker):?>
									    <div class="marker" style="background-color: <?=strstr($arElement["PROPERTIES"]["OFFERS"]["VALUE_XML_ID"][$ifv], "#") ? $arElement["PROPERTIES"]["OFFERS"]["VALUE_XML_ID"][$ifv] : "#424242"?>"><?=$marker?></div>
									<?endforeach;?>
								</div>
							<?endif;?>
							<a href="<?=$arElement["DETAIL_PAGE_URL"]?>" class="picture">
								<img src="<?=$picture["src"]?>" alt="<?=$arElement["NAME"]?>">
							</a>
						</div>
					</div>
				<?endforeach;?>
			</div>
		</div>
	<?else:?>
		<div id="empty">
			<div class="emptyWrapper">
				<div class="pictureContainer">
					<img src="<?=SITE_TEMPLATE_PATH?>/images/emptyFolder.png" alt="<?=GetMessage("EMPTY_HEADING")?>" class="emptyImg">
				</div>
				<div class="info">
					<h3><?=GetMessage("EMPTY_HEADING")?></h3>
					<p><?=GetMessage("EMPTY_TEXT")?></p>
					<a href="<?=SITE_DIR?>" class="back"><?=GetMessage("MAIN_PAGE")?></a>
				</div>
			</div>
			<?$APPLICATION->IncludeComponent("bitrix:menu", "emptyMenu", Array(
				"ROOT_MENU_TYPE" => "left",
					"MENU_CACHE_TYPE" => "N",
					"MENU_CACHE_TIME" => "3600",
					"MENU_CACHE_USE_GROUPS" => "Y",
					"MENU_CACHE_GET_VARS" => "",
					"MAX_LEVEL" => "1",
					"CHILD_MENU_TYPE" => "left",
					"USE_EXT" => "Y",
					"DELAY" => "N",
					"ALLOW_MULTI_SELECT" => "N",
				),
				false
			);?>
		</div>
	<?endif;?>
	<br />
<?if ($arParams["DISPLAY_BOTTOM_PAGER"]){?><? echo $arResult["NAV_STRING"];?><?}?>