<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();
$this->setFrameMode(true);
?>
<?if(!empty($arResult)):?>
	<?
		//get banner picture resize
		if(!empty($arResult["DISPLAY_PROPERTIES"]["BG_IMAGE"]["FILE_VALUE"])){
			$arResult["RESIZE_BANNER_PICTURE"] = CFile::ResizeImageGet($arResult["DISPLAY_PROPERTIES"]["BG_IMAGE"]["FILE_VALUE"], array("width" => 1920, "height" => 500), BX_RESIZE_IMAGE_PROPORTIONAL, false, false, false, 85);
		}
		//get detail picture resize
		if(!empty($arResult["DETAIL_PICTURE"])){
			$arResult["RESIZE_DETAIL_PICTURE"] = CFile::ResizeImageGet($arResult["DETAIL_PICTURE"], array("width" => 550, "height" => 500), BX_RESIZE_IMAGE_PROPORTIONAL, false, false, false, 85);
		}
	?>
	<?//include view?>
	<?if(!empty($arResult["RESIZE_BANNER_PICTURE"])):?>
		<?include_once("include/blog-detail-banner.php");?>
	<?elseif(!empty($arResult["DETAIL_PICTURE"])):?>
		<?include_once("include/blog-detail-image.php");?>
	<?else:?>
		<?include_once("include/blog-detail-no-image.php");?>
	<?endif;?>
	<meta property="og:title" content="<?=$arResult["NAME"]?>" />
	<meta property="og:description" content="<?=htmlspecialcharsbx($arResult["PREVIEW_TEXT"])?>" />
	<meta property="og:url" content="<?=$arResult["DETAIL_PAGE_URL"]?>" />
	<meta property="og:type" content="website" />
	<?if(!empty($arResult["RESIZE_DETAIL_PICTURE"])):?>
		<meta property="og:image" content="<?=$arResult["RESIZE_DETAIL_PICTURE"]["src"]?>" />
	<?endif;?>
<?endif;?>
