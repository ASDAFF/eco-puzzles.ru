<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true) die();
	$this->setFrameMode(true);
?>
	<?if(!empty($arResult["ITEMS"])):?>
		<?$this->SetViewTarget("main_news_view_content_tab");?><div class="item"><a href="#"><?=$arResult["NAME"]?></a></div><?$this->EndViewTarget();?>
		<div class="tab">
			<div class="mainService">
				<div class="limiter">
					<div id="mainNewsCarousel" class="mainServiceContainer">
						<div class="slideContainer">
							<ul class="slideBox items">
								<?foreach($arResult["ITEMS"] as $ixd => $arElement):?>
									<?
										$this->AddEditAction($arElement["ID"], $arElement["EDIT_LINK"], CIBlock::GetArrayByID($arElement["IBLOCK_ID"], "ELEMENT_EDIT"));
										$this->AddDeleteAction($arElement["ID"], $arElement["DELETE_LINK"], CIBlock::GetArrayByID($arElement["IBLOCK_ID"], "ELEMENT_DELETE"), array("CONFIRM" => GetMessage('CT_BNL_ELEMENT_DELETE_CONFIRM')));
									?>
									<?$image =  CFile::ResizeImageGet($arElement["PREVIEW_PICTURE"], array("width" => 430, "height" => 250), BX_RESIZE_IMAGE_PROPORTIONAL, false);?>  
									<li class="item" id="<?=$this->GetEditAreaId($arElement["ID"]);?>">
										<div class="wrap">
											<?if(!empty($image["src"])):?>
												<div class="bigPicture"><a href="<?=$arElement["DETAIL_PAGE_URL"]?>"><img class="lazy" src="<?=SITE_TEMPLATE_PATH?>/images/loading.svg" data-src="<?=$image["src"]?>" alt="<?=$arElement["NAME"]?>"></a></div>
											<?endif;?>
											<div class="title"><a href="<?=$arElement["DETAIL_PAGE_URL"]?>"><span><?=$arElement["NAME"]?></span></a></div>
											<a href="<?=$arElement["DETAIL_PAGE_URL"]?>" class="more"><?=GetMessage("MAIN_NEWS_MORE")?></a>
										</div>
									</li>
								<?endforeach;?>
							</ul>
						</div>
						<a href="#" class="mainNewsBtnLeft btnLeft"></a>
						<a href="#" class="mainNewsBtnRight btnRight"></a>
					</div>
					<script>
						$("#mainNewsCarousel").dwCarousel({
							leftButton: ".mainNewsBtnLeft",
							rightButton: ".mainNewsBtnRight",
							countElement: 4,
							resizeElement: true,
							resizeAutoParams: {
								1920: 4,
								1024: 3,
								550: 2
							}
						});
					</script>
				</div>
			</div>
		</div>
	<?endif;?>
