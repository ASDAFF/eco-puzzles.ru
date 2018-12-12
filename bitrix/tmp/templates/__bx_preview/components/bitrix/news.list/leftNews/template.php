<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();
$this->setFrameMode(true);
?>
<?if(!empty($arResult["ITEMS"])):?>
	<div class="sideBlock" id="newsBlock">
		<a class="heading" href="<?=SITE_DIR?>news/"><?=GetMessage("NEWS_HEADING")?></a>
		<div class="sideBlockContent">
			<?foreach($arResult["ITEMS"] as $ix => $arItem):?>
				<?
					$image = CFile::ResizeImageGet($arItem["PREVIEW_PICTURE"], array("width" => 130, "height" => 170), BX_RESIZE_IMAGE_PROPORTIONAL, false); 
					$this->AddEditAction($arItem["ID"], $arItem["EDIT_LINK"], CIBlock::GetArrayByID($arItem["IBLOCK_ID"], "ELEMENT_EDIT"));
					$this->AddDeleteAction($arItem["ID"], $arItem["DELETE_LINK"], CIBlock::GetArrayByID($arItem["IBLOCK_ID"], "ELEMENT_DELETE"), array("CONFIRM" => GetMessage('CT_BNL_ELEMENT_DELETE_CONFIRM')));
				?>
				<div class="newsPreview" id="<?=$this->GetEditAreaId($arItem["ID"]);?>">
					<?if(empty($ix) && !empty($image["src"])):?>
						<div class="newsPic">
							<a href="<?=$arItem["DETAIL_PAGE_URL"]?>"><img src="<?=$image["src"]?>" alt="<?=$arItem["NAME"]?>"></a>
						</div>
					<?endif;?>
					<div class="newsOverview">
						<?if($arParams["DISPLAY_DATE"] != "N" && $arItem["DISPLAY_ACTIVE_FROM"]):?>
							<span><?echo $arItem["DISPLAY_ACTIVE_FROM"]?></span>
						<?endif?>
						<a href="<?=$arItem["DETAIL_PAGE_URL"]?>" class="newsTitle"><?=$arItem["NAME"]?></a>
						<?if(!empty($arItem["PREVIEW_TEXT"])):?>
							<div class="preText">
								<?=$arItem["PREVIEW_TEXT"]?>
							</div>
						<?endif;?>
					</div>
				</div>
			<?endforeach;?>		
		</div>
	</div>
<?endif;?>