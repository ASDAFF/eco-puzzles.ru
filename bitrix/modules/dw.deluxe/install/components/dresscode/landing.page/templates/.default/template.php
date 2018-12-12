<?if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) die();?>
<?$this->setFrameMode(true);?>

<?if(!empty($arResult["PAGE_TOP_TEXT"])):?>
	<?$this->SetViewTarget("landing_page_top_text_container");?>
	<?=$arResult["PAGE_TOP_TEXT"];?>
	<?$this->EndViewTarget();?>
<?endif;?>

<?if(!empty($arResult["PAGE_BOTTOM_TEXT"])):?>
	<?$this->SetViewTarget("landing_page_bottom_text_container");?>
	<?=$arResult["PAGE_BOTTOM_TEXT"];?>
	<?$this->EndViewTarget();?>
<?endif;?>

<?if(!empty($arResult["BANNER"])):?>
	<?$arResult["BANNER"]["BIG_PICTURE"] = CFile::ResizeImageGet($arResult["BANNER"]["BIG_PICTURE"], array("width" => $arParams["BIG_PICTURE_WIDTH"], "height" => $arParams["BIG_PICTURE_HEIGHT"]), BX_RESIZE_IMAGE_PROPORTIONAL, false, false, false, 100);?>
	<?if(!empty($arResult["BANNER"]["SMALL_PICTURE"])):?>
		<?$arResult["BANNER"]["SMALL_PICTURE"] = CFile::ResizeImageGet($arResult["BANNER"]["SMALL_PICTURE"], array("width" => $arParams["SMALL_PICTURE_WIDTH"], "height" => $arParams["SMALL_PICTURE_HEIGHT"]), BX_RESIZE_IMAGE_PROPORTIONAL, false, false, false, 100);?>
	<?endif;?>
	<?$this->SetViewTarget("landing_page_banner_container");?>
		<div class="banner-animated fullscreen-banner arbitrary-banner banner-elem" style="background: url('<?=$arResult["BANNER"]["BIG_PICTURE"]["src"]?>') center center / cover no-repeat;">
			<?if(!empty($arResult["BANNER"]["TEXT"]) || !empty($arResult["BANNER"]["SMALL_PICTURE"])):?>
				<div class="limiter">
					<div class="tb">
						<?if(!empty($arResult["BANNER"]["TEXT"])):?>
							<div class="text-wrap tc">
								<?if(!empty($arResult["BANNER"]["TEXT"]["VALUE"])):?>
									<?if(is_array($arResult["BANNER"]["TEXT"]["VALUE"])):?>
										<?=$arResult["BANNER"]["TEXT"]["~VALUE"]["TEXT"]?>
									<?else:?>
										<?=$arResult["BANNER"]["TEXT"]["VALUE"]?>
									<?endif;?>
								<?endif;?>
							</div>
						<?endif;?>
						<?if(!empty($arResult["BANNER"]["SMALL_PICTURE"])):?>
							<div class="image tc">
								<img src="<?=$arResult["BANNER"]["SMALL_PICTURE"]["src"]?>" alt="">
							</div>
						<?endif;?>
					</div>
				</div>
			<?endif;?>
		</div>
	<?$this->EndViewTarget();?>
<?endif;?>