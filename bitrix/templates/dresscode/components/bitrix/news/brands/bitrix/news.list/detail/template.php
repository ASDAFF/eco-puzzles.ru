<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true) die();
	$this->setFrameMode(true);
?>
	<div class="newsHeading"><?=GetMessage("NEWS_TITLE")?></div>
	<?if(!empty($arResult["ITEMS"])):?>
		<?foreach($arResult["ITEMS"] as $ixd => $arElement):?>
			<?$arColumns[$ixd%3]["ITEMS"][] = $arElement;?>
		<?endforeach;?>

		<div id="newsContainer">
			<?foreach ($arColumns as $key => $arColumn):?>
				<?
					$this->AddEditAction($arElement["ID"], $arElement["EDIT_LINK"], CIBlock::GetArrayByID($arElement["IBLOCK_ID"], "ELEMENT_EDIT"));
					$this->AddDeleteAction($arElement["ID"], $arElement["DELETE_LINK"], CIBlock::GetArrayByID($arElement["IBLOCK_ID"], "ELEMENT_DELETE"), array("CONFIRM" => GetMessage('CT_BNL_ELEMENT_DELETE_CONFIRM')));
				?>
				<div class="column">
					<?if(!empty($arColumn["ITEMS"])):?>
						<div class="items">
							<?foreach ($arColumn["ITEMS"] as $ix => $arElement):?>
								<?$image =  CFile::ResizeImageGet($arElement["PREVIEW_PICTURE"], array("width" => 430, "height" => 250), BX_RESIZE_IMAGE_PROPORTIONAL, false);?>  
								<div class="item" id="<?=$this->GetEditAreaId($arElement["ID"]);?>">
									<?if(!empty($image["src"])):?>
										<div class="bigPicture"><a href="<?=$arElement["DETAIL_PAGE_URL"]?>"><img src="<?=$image["src"]?>" alt="<?=$arElement["NAME"]?>"></a></div>
									<?endif;?>
									<div class="title"><a href="<?=$arElement["DETAIL_PAGE_URL"]?>"><?=$arElement["NAME"]?></a></div>
									<?if(!empty($arElement["DISPLAY_ACTIVE_FROM"])):?>
										<div class="newsDate"><?=$arElement["DISPLAY_ACTIVE_FROM"]?></div>
									<?endif;?>
									<?if(!empty($arElement["PREVIEW_TEXT"])):?>
										<div class="description"><?=$arElement["PREVIEW_TEXT"]?></div>
									<?endif;?>
									<a href="<?=$arElement["DETAIL_PAGE_URL"]?>" class="more"><?=GetMessage("NEWS_MORE")?></a>
								</div>
							<?endforeach;?>
						</div>
					<?endif;?>
				</div>
			<?endforeach;?>
		</div>
		<?if($arParams["DISPLAY_BOTTOM_PAGER"]):?>
			<br /><?=$arResult["NAV_STRING"]?>
		<?endif;?>

	<?endif;?>
