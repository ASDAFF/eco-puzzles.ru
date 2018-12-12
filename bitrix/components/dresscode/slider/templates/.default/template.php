<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) die();?>
<?$this->setFrameMode(true);?>
<?if(!empty($arResult["ITEMS"])):?>
<div id="sliderBlock">
	<div id="slider">
		<ul class="slideBox">
			<?foreach($arResult["ITEMS"] as $i => $arElement):?>
				<?
					$this->AddEditAction($arElement['ID'], $arElement['EDIT_LINK'], CIBlock::GetArrayByID($arParams["IBLOCK_ID"], "ELEMENT_EDIT"));
					$this->AddDeleteAction($arElement['ID'], $arElement['DELETE_LINK'], CIBlock::GetArrayByID($arParams["IBLOCK_ID"], "ELEMENT_DELETE"), array());
				?>
				<li id="<?=$this->GetEditAreaId($arElement['ID']);?>">
					<?if($arElement["PROPERTIES"]["LINK"]["VALUE"]):?>
						<a href="<?=str_replace("#SITE_DIR#", SITE_DIR, $arElement["PROPERTIES"]["LINK"]["VALUE"])?>">
					<?endif;?>		
					<span data-large="<?=$arElement['DETAIL_PICTURE']["src"]?>" data-normal="<?=$arElement['PREVIEW_PICTURE']["src"]?>"></span>			
					<?if($arElement["PROPERTIES"]["LINK"]["VALUE"]):?>
						</a>
					<?endif;?>
				</li>
			<?endforeach;?>
		</ul>
		<a href="#" class="sliderBtnLeft"></a>
		<a href="#" class="sliderBtnRight"></a>
	</div>
</div>
<?endif;?>
