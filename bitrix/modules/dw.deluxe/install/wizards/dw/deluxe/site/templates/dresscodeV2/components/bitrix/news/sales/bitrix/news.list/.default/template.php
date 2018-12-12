<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();
$this->setFrameMode(true);
?>
<?if(!empty($arResult["ITEMS"])):?>
	<?$startIndex = 0;?>
	<div class="tiles-list actions-list">
		<?foreach($arResult["ITEMS"] as $arNextElement):?>
			<?
				//for edit buttons
				$this->AddEditAction($arNextElement["ID"], $arNextElement["EDIT_LINK"], CIBlock::GetArrayByID($arNextElement["IBLOCK_ID"], "ELEMENT_EDIT"));
				$this->AddDeleteAction($arNextElement["ID"], $arNextElement["DELETE_LINK"], CIBlock::GetArrayByID($arNextElement["IBLOCK_ID"], "ELEMENT_DELETE"), array("CONFIRM" => GetMessage("CT_BNL_ELEMENT_DELETE_CONFIRM")));
				
				//get picture resize
				if(!empty($arNextElement["PREVIEW_PICTURE"])){
					$arNextElement["RESIZE_PICTURE"] = CFile::ResizeImageGet($arNextElement["PREVIEW_PICTURE"], array("width" => 600, "height" => 400), BX_RESIZE_IMAGE_PROPORTIONAL, false, false, false, 85);
				}
			?>
			<div class="tile-wrap" id="<?=$this->GetEditAreaId($arNextElement["ID"]);?>">
				<div class="tile<?if(!empty($startIndex)):?><?if(empty($arNextElement["RESIZE_PICTURE"])):?> no-image<?else:?> center-image<?endif;?><?endif;?>">
					<div class="tb">				
						<div class="tc">
							<?if(!empty($arNextElement["RESIZE_PICTURE"])):?>
								<a href="<?=$arNextElement["DETAIL_PAGE_URL"]?>" class="image-wrap">
									<span class="image" style="background-image: url('<?=$arNextElement["RESIZE_PICTURE"]["src"]?>');"></span>
								</a>
							<?endif;?>
							<div class="tile-text">
								<?if(!empty($arNextElement["DISPLAY_PROPERTIES"]["STOCK_DATE"]["VALUE"])):?>
									<div class="tile-time"><?=$arNextElement["DISPLAY_PROPERTIES"]["STOCK_DATE"]["VALUE"]?></div>
								<?endif;?>
								<?if(!empty($arNextElement["NAME"])):?>
									<a href="<?=$arNextElement["DETAIL_PAGE_URL"]?>" class="h3 ff-medium"><?=$arNextElement["NAME"]?></a>
								<?endif;?>
								<?if(!empty($arNextElement["PREVIEW_TEXT"])):?>
									<div class="tile-descr"><?=$arNextElement["PREVIEW_TEXT"]?></div>
								<?endif;?>
								<?if(!empty($arNextElement["DETAIL_PAGE_URL"])):?>
									<a href="<?=$arNextElement["DETAIL_PAGE_URL"]?>" class="btn-simple btn-border btn-micro"><?=GetMessage("NEWS_BLOG_DETAIL_BUTTON")?></a>
								<?endif;?>
							</div>
						</div>
					</div>
				</div>
			</div>
			<?$startIndex++;?>
		<?endforeach;?>
		<?if($arParams["DISPLAY_BOTTOM_PAGER"]):?>
			<br /><?=$arResult["NAV_STRING"]?>
		<?endif;?>
	</div>
<?endif;?>
