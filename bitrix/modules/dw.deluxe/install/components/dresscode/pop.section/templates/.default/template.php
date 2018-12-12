<?if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();?>
<?$this->setFrameMode(true);?>
<?if(!empty($arResult["ITEMS"])):?>
	<?if(empty($arParams["AJAX"])):?>
	<div id="popSection" data-page="1">
		<a href="<?=SITE_DIR?>catalog/"><span class="heading"><?=GetMessage("POP_SECTION_HEADING")?></span></a>
			<div class="ajaxContainer">
	<?endif;?>
				<div class="items">
					<?foreach($arResult["ITEMS"] as $arElement):?>
						<?
							$this->AddEditAction($arElement["ID"], $arElement["EDIT_LINK"], CIBlock::GetArrayByID($arElement["IBLOCK_ID"], "ELEMENT_EDIT"));
							$this->AddDeleteAction($arElement["ID"], $arElement["DELETE_LINK"], CIBlock::GetArrayByID($arElement["IBLOCK_ID"], "ELEMENT_DELETE"), array());
							$picture = CFile::ResizeImageGet($arElement["UF_IMAGES"], array("width" => $arParams["PICTURE_WIDTH"], "height" => $arParams["PICTURE_HEIGHT"]), BX_RESIZE_IMAGE_PROPORTIONAL, false);
							$picture["src"] = !empty($picture["src"]) ? $picture["src"] : SITE_TEMPLATE_PATH."/images/empty.png";
						?>
						<div class="item" id="<?=$this->GetEditAreaId($arElement["ID"]);?>">
							<div class="tabloid">
								<?if(!empty($arElement["UF_MARKER"])):?>
									<div class="markerContainer">
										<div class="marker"><?=$arElement["UF_MARKER"]?></div>
									</div>
								<?endif;?>
								<a href="<?=$arElement["SECTION_PAGE_URL"]?>" class="picture">
									<img src="<?=$picture["src"]?>" alt="<?=$arElement["NAME"]?>">
								</a>
								<a href="<?=$arElement["SECTION_PAGE_URL"]?>" class="name"><?=$arElement["NAME"]?></a>
							</div>
						</div>
					<?endforeach;?>
					<?if(!empty($arResult["HIDE_LAST_ELEMENT"])):?>
						<div class="item last">
							<a href="#" class="showMore">
								<span class="wp">
									<span class="icon"><img src="<?=SITE_TEMPLATE_PATH?>/images/showMore.png" alt="<?=GetMessage("POP_SECTION_SHOW_MORE")?>"></span>
									<span class="ps"><?=GetMessage("POP_SECTION_SHOW_MORE")?></span><span class="value"><?=$arResult["NEXT_ELEMENTS_COUNT"]?></span>
									<span class="small"><?=GetMessage("POP_SECTION_SHOWS")?> <?=$arResult["ELEMENTS_COUNT_SHOW"]?> <?=GetMessage("POP_SECTION_FROM")?> <?=$arResult["ITEMS_ALL_COUNT"]?></span>
								</span>
							</a>
						</div>
					<?endif;?>
				</div>
		<?if(empty($arParams["AJAX"])):?>
				</div>
			</div>
		<?endif;?>
	<?if(empty($arParams["AJAX"])):?>
		<script type="text/javascript">
			var ajaxDirPopSection = "<?=$this->GetFolder();?>";
			var popSectionParams = '<?=json_encode($arParams);?>';
		</script>
	<?endif;?>
<?endif;?>