<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();
$this->setFrameMode(true);
?>
<?if(!empty($arResult["ITEMS"])):?>
	<div class="sideBlock" id="collectionBlock">
		<a class="heading" href="<?=SITE_DIR?>collection/"><?=GetMessage("COLLECTION_HEADING")?></a>
		<div class="sideBlockContent">
			<?foreach($arResult["ITEMS"] as $ix => $arItem):?>
				<?
					$image = CFile::ResizeImageGet($arItem["PREVIEW_PICTURE"], array("width" => 70, "height" => 70), BX_RESIZE_IMAGE_PROPORTIONAL, false); 
					$image["src"] = !empty($image["src"]) ? $image["src"] : SITE_TEMPLATE_PATH."/images/empty.png";
					$this->AddEditAction($arItem["ID"], $arItem["EDIT_LINK"], CIBlock::GetArrayByID($arItem["IBLOCK_ID"], "ELEMENT_EDIT"));
					$this->AddDeleteAction($arItem["ID"], $arItem["DELETE_LINK"], CIBlock::GetArrayByID($arItem["IBLOCK_ID"], "ELEMENT_DELETE"), array("CONFIRM" => GetMessage('CT_BNL_ELEMENT_DELETE_CONFIRM')));
				?>
				<div class="item" id="<?=$this->GetEditAreaId($arItem["ID"]);?>">
					<div class="picBlock">
						<a href="<?=$arItem["DETAIL_PAGE_URL"]?>" class="picture"><img src="<?=$image["src"]?>" alt="<?=$arItem["NAME"]?>"></a>	
					</div>
					<div class="tools">
						<a href="<?=$arItem["DETAIL_PAGE_URL"]?>" class="name"><?=$arItem["NAME"]?></a>
						<?if(!empty($arItem["PREVIEW_TEXT"])):?>
							<div class="description"><?=$arItem["PREVIEW_TEXT"]?></div>
						<?endif;?>
					</div>
				</div>
			<?endforeach;?>		
		</div>
	</div>
<?endif;?>