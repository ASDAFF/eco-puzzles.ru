<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true) die();
$this->setFrameMode(true);?>
<?if(!empty($arResult["SECTIONS"])):?>
	<div class="catalog-section-list-pictures">
		<?foreach($arResult["SECTIONS"] as $arElement):?>
			<div class="catalog-section-list-item">
				<div class="catalog-section-list-item-wp">
					<?if(!empty($arElement["PICTURE"])):?>
						<?$picture = CFile::ResizeImageGet($arElement["PICTURE"], array("width" => 140, "height" => 100), BX_RESIZE_IMAGE_PROPORTIONAL, true);?>
						<a href="<?=$arElement["SECTION_PAGE_URL"]?>" class="catalog-section-list-picture"><img src="<?=$picture["src"]?>" alt="<?=$arElement["NAME"]?>" title="<?=$arElement["NAME"]?>"></a>
					<?endif;?>
					<a href="<?=$arElement["SECTION_PAGE_URL"]?>" class="catalog-section-list-link"><span><?=$arElement["NAME"]?></span></a>
				</div>
			</div>
		<?endforeach;?>
	</div>
<?endif;?>
