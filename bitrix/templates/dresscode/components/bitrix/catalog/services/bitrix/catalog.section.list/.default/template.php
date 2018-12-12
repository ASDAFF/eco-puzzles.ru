<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true) die();
$this->setFrameMode(true);?>
<?if(!empty($arResult["SECTIONS"])):?>
	<div class="banners-list services-banners-list">
		<?foreach($arResult["SECTIONS"] as $ii => $arNextSection):?>
			<?
				$this->AddEditAction($arNextSection["ID"], $arNextSection["EDIT_LINK"], CIBlock::GetArrayByID($arNextSection["IBLOCK_ID"], "ELEMENT_EDIT"));
				$this->AddDeleteAction($arNextSection["ID"], $arNextSection["DELETE_LINK"], CIBlock::GetArrayByID($arNextSection["IBLOCK_ID"], "ELEMENT_DELETE"), array("CONFIRM" => GetMessage("CT_BNL_ELEMENT_DELETE_CONFIRM")));
				$arNextSection["RESIZE_DETAIL_PICTURE"] = CFile::ResizeImageGet($arNextSection["DETAIL_PICTURE"], array("width" => 400, "height" => 300), BX_RESIZE_IMAGE_PROPORTIONAL, false, false, false, 100);
				$arNextSection["RESIZE_PICTURE"] = CFile::ResizeImageGet($arNextSection["PICTURE"], array("width" => 800, "height" => 300), BX_RESIZE_IMAGE_PROPORTIONAL, false, false, false, 100);
			?>
				<div class="banner-wrap" id="<?=$this->GetEditAreaId($arNextSection["ID"]);?>">
					<div class="banner-elem"<?if(!empty($arNextSection["RESIZE_PICTURE"])):?> style="background: url('<?=$arNextSection["RESIZE_PICTURE"]["src"]?>') center center / cover no-repeat;"<?endif;?>>
						<div class="tb">
							<div class="text-wrap tc">
								<a href="<?=$arNextSection["SECTION_PAGE_URL"]?>" class="theme-color-hover h2 ff-bold"><?=$arNextSection["NAME"]?></a>
								<?if(!empty($arNextSection["UF_SMALL_TEXT"])):?>
									<div class="price theme-color ff-medium"><?=$arNextSection["UF_SMALL_TEXT"]?></div>
								<?endif;?>
								<?if(!empty($arNextSection["UF_TEXT"])):?>
									<div class="descr"><?=$arNextSection["UF_TEXT"]?></div>
								<?endif;?>
								<a href="<?=$arNextSection["SECTION_PAGE_URL"]?>" class="btn-simple btn-micro"><?=GetMessage("SECTION_MORE_BUTTON_LABEL")?></a>
							</div>
							<?if(!empty($arNextSection["RESIZE_DETAIL_PICTURE"])):?>
								<div class="image tc">
									<a href="<?=$arNextSection["SECTION_PAGE_URL"]?>" class="image-link">
										<img src="<?=$arNextSection["RESIZE_DETAIL_PICTURE"]["src"]?>" alt="<?=$arNextSection["NAME"]?>">
									</a>
								</div>
							<?endif;?>
						</div>
					</div>
				</div>
		<?endforeach;?>
	</div>
<?endif;?>