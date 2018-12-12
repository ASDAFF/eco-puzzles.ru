<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();
$this->setFrameMode(true);
?>
<?if(!empty($arResult["ITEMS"])):?>
	<?$startIndex = 0;?>
	<div class="global-information-block">
		<div class="other-news">
			<div class="other-news-title h3"><?=GetMessage("NEWS_BLOG_MORE_ITEMS_LABEL")?></div>
			<div class="other-news-list">
				<?foreach($arResult["ITEMS"] as $arNextElement):?>
					<?
						//for edit buttons
						$this->AddEditAction($arNextElement["ID"], $arNextElement["EDIT_LINK"], CIBlock::GetArrayByID($arNextElement["IBLOCK_ID"], "ELEMENT_EDIT"));
						$this->AddDeleteAction($arNextElement["ID"], $arNextElement["DELETE_LINK"], CIBlock::GetArrayByID($arNextElement["IBLOCK_ID"], "ELEMENT_DELETE"), array("CONFIRM" => GetMessage("CT_BNL_ELEMENT_DELETE_CONFIRM")));
						
						//get picture resize
						if(!empty($arNextElement["DETAIL_PICTURE"])){
							$arNextElement["RESIZE_PICTURE"] = CFile::ResizeImageGet($arNextElement["DETAIL_PICTURE"], array("width" => 200, "height" => 150), BX_RESIZE_IMAGE_PROPORTIONAL, false, false, false, 85);
						}
					?>

					<div class="news" id="<?=$this->GetEditAreaId($arNextElement["ID"]);?>">
						<?if(empty($startIndex)):?>
							<?if(!empty($arNextElement["RESIZE_PICTURE"])):?>
								<div class="image"><a href="<?=$arNextElement["DETAIL_PAGE_URL"]?>"><img src="<?=$arNextElement["RESIZE_PICTURE"]["src"]?>" alt="<?=$arNextElement["NAME"]?>"></a></div>
							<?endif;?>
						<?endif;?>
						<?if(!empty($arNextElement["NAME"])):?>
							<a href="<?=$arNextElement["DETAIL_PAGE_URL"]?>" class="name"><?=$arNextElement["NAME"]?></a>
						<?endif;?>
						<?if(!empty($arNextElement["PREVIEW_TEXT"])):?>
							<div class="announcement"><?=$arNextElement["PREVIEW_TEXT"]?></div>
						<?endif;?>
						<?if(!empty($arNextElement["DETAIL_PAGE_URL"])):?>
							<a href="<?=$arNextElement["DETAIL_PAGE_URL"]?>" class="theme-link-dashed"><?=GetMessage("NEWS_BLOG_DETAIL_BUTTON")?></a>
						<?endif;?>
					</div>

					<?$startIndex++;?>
				<?endforeach;?>
			</div>
		</div>
	</div>
<?endif;?>