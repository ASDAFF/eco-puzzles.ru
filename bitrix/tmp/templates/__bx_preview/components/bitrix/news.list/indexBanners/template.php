<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();
$this->setFrameMode(true);
$arColumnParams = array(true, true, false, true, true, false, true);
$currentIndex = 0;
?>
<?if(!empty($arResult["ITEMS"])):?>
	<div class="limiter">
		<div class="index-banners-wrap">
			<div class="index-banners">
				<?foreach($arResult["ITEMS"] as $arNextElement):?>
					<?
						//get edit actions
						$this->AddEditAction($arNextElement["ID"], $arNextElement["EDIT_LINK"], CIBlock::GetArrayByID($arNextElement["IBLOCK_ID"], "ELEMENT_EDIT"));
						$this->AddDeleteAction($arNextElement["ID"], $arNextElement["DELETE_LINK"], CIBlock::GetArrayByID($arNextElement["IBLOCK_ID"], "ELEMENT_DELETE"), array("CONFIRM" => GetMessage("CT_BNL_ELEMENT_DELETE_CONFIRM")));
						//get resize picture
						if(!empty($arNextElement["DETAIL_PICTURE"])){
							$arNextElement["RESIZE_PICTURE"] = CFile::ResizeImageGet($arNextElement["DETAIL_PICTURE"], array("width" => 500, "height" => 500), BX_RESIZE_IMAGE_PROPORTIONAL, false, false, false, 100);
						}
					?>
				<?if($arColumnParams[$currentIndex]):?>
					<div class="elem-column">
				<?endif;?>
						<div class="elem-wrap" id="<?=$this->GetEditAreaId($arNextElement["ID"]);?>">
							<div class="elem">
								<div class="bg" style="background-image: url('<?=$arNextElement["RESIZE_PICTURE"]["src"]?>');"></div>
								<div class="text-wrap">
									<?=$arNextElement["~DETAIL_TEXT"]?>
								</div>
							</div>
						</div>
				<?if($arColumnParams[$currentIndex + 1]):?>
					</div>
				<?endif;?>
					<?$currentIndex++;?>
				<?endforeach;?>
			</div>
		</div>
	</div>
<?endif;?>