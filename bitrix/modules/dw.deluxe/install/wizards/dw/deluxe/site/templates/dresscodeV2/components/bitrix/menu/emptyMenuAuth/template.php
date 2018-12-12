<?if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();?>
<?$this->setFrameMode(true);?>
<?if (!empty($arResult)):?>
	<div class="emptyTitle"><?=GetMessage("EMPTY_TITLE")?></div>
	<ul class="emptyMenu">
		<?foreach($arResult as $arItem):
			if($arParams["MAX_LEVEL"] == 1 && $arItem["DEPTH_LEVEL"] > 1) 
				continue;
		?>
			<?if($arItem["SELECTED"]):?>
				<li><a href="<?=$arItem["LINK"]?>" class="selected"><?if(!empty($arItem["PARAMS"]["PICTURE"]["src"])):?><img src="<?=$arItem["PARAMS"]["PICTURE"]["src"]?>" alt="<?=$arItem["TEXT"]?>"><?endif;?><?=$arItem["TEXT"]?></a></li>
			<?else:?>
				<li><a href="<?=$arItem["LINK"]?>"><?if(!empty($arItem["PARAMS"]["PICTURE"]["src"])):?><img src="<?=$arItem["PARAMS"]["PICTURE"]["src"]?>" alt="<?=$arItem["TEXT"]?>"><?endif;?><?=$arItem["TEXT"]?></a></li>
			<?endif?>
			
		<?endforeach?>
	</ul>
<?endif?>