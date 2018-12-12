<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();
$this->setFrameMode(true);
?>
<h1><?=$arResult["NAME"]?></h1>
<?if(!empty($arResult)):?>
	<div id="newsDetail">
		<?$image = CFile::ResizeImageGet($arResult["DETAIL_PICTURE"], array("width" => 650, "height" => 800), BX_RESIZE_IMAGE_PROPORTIONAL, false);?>  
		<?if(!empty($image["src"])):?>
			<div class="bigPicture">
				<img src="<?=$image["src"]?>" alt="<?=$arResult["NAME"]?>">
			</div>
		<?endif;?>
		<?if(!empty($arResult["DISPLAY_ACTIVE_FROM"])):?>
			<div class="newsDate"><?=$arResult["DISPLAY_ACTIVE_FROM"]?></div>
		<?endif;?>
		<?if(!empty($arResult["DETAIL_TEXT"])):?>
			<div class="description"><?=$arResult["DETAIL_TEXT"]?></div>
		<?endif;?>
		<a href="<?=$arResult["LIST_PAGE_URL"]?>" class="more"><?=GetMessage("NEWS_BACK")?></a>
	</div>
<?endif;?>

