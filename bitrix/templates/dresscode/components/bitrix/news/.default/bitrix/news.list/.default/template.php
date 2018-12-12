<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true) die();
	$this->setFrameMode(true);
?>
	<?if(!empty($arResult["ITEMS"])):?>
		<div id="news">
			<div id="newsContainer">
				<div class="items column">
					<?foreach ($arResult["ITEMS"] as $key => $arElement):?>
						<?
							$this->AddEditAction($arElement["ID"], $arElement["EDIT_LINK"], CIBlock::GetArrayByID($arElement["IBLOCK_ID"], "ELEMENT_EDIT"));
							$this->AddDeleteAction($arElement["ID"], $arElement["DELETE_LINK"], CIBlock::GetArrayByID($arElement["IBLOCK_ID"], "ELEMENT_DELETE"), array("CONFIRM" => GetMessage('CT_BNL_ELEMENT_DELETE_CONFIRM')));
						?>
						<?$image =  CFile::ResizeImageGet($arElement["PREVIEW_PICTURE"], array("width" => 430, "height" => 180), BX_RESIZE_IMAGE_PROPORTIONAL, false);?>
						<div class="item" id="<?=$this->GetEditAreaId($arElement["ID"]);?>">
							<div class="wrap">
								<?if(!empty($image["src"])):?>
									<div class="bigPicture"><a href="<?=$arElement["DETAIL_PAGE_URL"]?>"><img src="<?=$image["src"]?>" alt="<?=$arElement["NAME"]?>"></a></div>
								<?endif;?>
								<div class="title"><a href="<?=$arElement["DETAIL_PAGE_URL"]?>"><span class="middle"><?=$arElement["NAME"]?></span></a></div>
								<?if(!empty($arElement["PREVIEW_TEXT"])):?>
									<div class="description"><span class="middle"><?=$arElement["PREVIEW_TEXT"]?></span></div>
								<?endif;?>
								<a href="<?=$arElement["DETAIL_PAGE_URL"]?>" class="more"><?=GetMessage("NEWS_MORE")?></a>
							</div>
						</div>
					<?endforeach;?>
				</div>
			</div>
			<?if($arParams["DISPLAY_BOTTOM_PAGER"]):?>
				<br /><?=$arResult["NAV_STRING"]?>
			<?endif;?>
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
