<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();
$this->setFrameMode(true);?>
<?if(!empty($arResult["SECTIONS"])):?>
	<div id="sectionList">
		<div class="items">
		<?foreach ($arResult["ITEMS"] as $is => $arSection):?>
			<?$image = CFile::ResizeImageGet($arSection["DETAIL_PICTURE"], array("width" => 300, "height" => 300), BX_RESIZE_IMAGE_PROPORTIONAL, false);?>
			<?$image["src"] = !empty($image["src"]) ? $image["src"] : SITE_TEMPLATE_PATH."/images/empty.png";?>
			<div class="item">
				<div class="tabloid"> 
					<?if(!empty($arSection["UF_MARKER"])):?>
						<div class="markerContainer">
							<div class="marker"><?=$arSection["UF_MARKER"]?></div>
						</div>
					<?endif;?>
					<a href="<?=$arSection["SECTION_PAGE_URL"]?>" class="picture"><img src="<?=$image["src"]?>" alt="<?=$arSection["NAME"]?>"></a>
					<a href="<?=$arSection["SECTION_PAGE_URL"]?>" class="name"><?=$arSection["NAME"]?> <span class="count">(<?=$arSection["ELEMENT_CNT"]?>)</span></a>
					<?if(!empty($arSection["ELEMENTS"])):?>
						<div class="innerList">
							<?foreach ($arSection["ELEMENTS"] as $ise => $arSectionElements):?>
								<div class="element">
									<a href="<?=$arSectionElements["SECTION_PAGE_URL"]?>" class="name"><?=$arSectionElements["NAME"]?></a>
								</div>
							<?endforeach;?>
						</div>
					<?endif;?>
				</div>
			</div>
		<?endforeach;?>
		</div>
	</div>
<?endif;?>
