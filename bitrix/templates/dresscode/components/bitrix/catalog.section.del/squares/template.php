<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();
$this->setFrameMode(true);?>

<?if (!empty($arResult["ITEMS"])):?>
<?
	if ($arParams["DISPLAY_TOP_PAGER"]){
		?><? echo $arResult["NAV_STRING"]; ?><?
	}
?>
	<div class="items productList">
		<?foreach ($arResult["ITEMS"] as $index => $arElement):?>
			<?$APPLICATION->IncludeComponent(
				"dresscode:catalog.item", 
				".default", 
				array(
					"CACHE_TIME" => $arParams["CACHE_TIME"],
					"CACHE_TYPE" => $arParams["CACHE_TYPE"],
					"HIDE_MEASURES" => $arParams["HIDE_MEASURES"],
					"HIDE_NOT_AVAILABLE" => $arParams["HIDE_NOT_AVAILABLE"],
					"IBLOCK_ID" => $arParams["IBLOCK_ID"],
					"IBLOCK_TYPE" => $arParams["IBLOCK_TYPE"],
					"PRODUCT_ID" => $arElement["ID"],
					"PICTURE_HEIGHT" => "",
					"PICTURE_WIDTH" => "",
					"PRODUCT_PRICE_CODE" => $arParams["PRICE_CODE"],
				),
				false
			);?>			
		<?endforeach;?>
		<div class="clear"></div>
	</div>
	<?
		if ($arParams["DISPLAY_BOTTOM_PAGER"]){
			?><? echo $arResult["NAV_STRING"]; ?><?
		}
	?>

	<?if(empty($_GET["PAGEN_1"])):?>
		<div><?=$arResult["~DESCRIPTION"]?></div>
	<?endif;?>

<?else:?>
	<div id="empty">
		<div class="emptyWrapper">
			<div class="pictureContainer">
				<img src="<?=SITE_TEMPLATE_PATH?>/images/emptyFolder.png" alt="<?=GetMessage("EMPTY_HEADING")?>" class="emptyImg">
			</div>
			<div class="info">
				<h3><?=GetMessage("EMPTY_HEADING")?></h3>
				<p><?=GetMessage("EMPTY_TEXT")?></p>
				<a href="<?=SITE_DIR?>" class="back"><?=GetMessage("MAIN_PAGE")?></a>
			</div>
		</div>
		<?$APPLICATION->IncludeComponent("bitrix:menu", "emptyMenu", Array(
			"ROOT_MENU_TYPE" => "left",
				"MENU_CACHE_TYPE" => "N",
				"MENU_CACHE_TIME" => "3600",
				"MENU_CACHE_USE_GROUPS" => "Y",
				"MENU_CACHE_GET_VARS" => "",
				"MAX_LEVEL" => "1",
				"CHILD_MENU_TYPE" => "left",
				"USE_EXT" => "Y",
				"DELAY" => "N",
				"ALLOW_MULTI_SELECT" => "N",
			),
			false
		);?>
	</div>
<?endif;?>