<?if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true) die();?>
<?$frame = $this->createFrame()->begin("");?>
<div class="wrap">
	<a<?if(!empty($arResult["COUNT_ITEMS"])):?> href="<?=SITE_DIR?>compare/"<?endif;?> class="icon<?if(!empty($arResult["COUNT_ITEMS"])):?> active<?endif;?>"></a>
	<div class="nf">
		<a<?if(!empty($arResult["COUNT_ITEMS"])):?> href="<?=SITE_DIR?>compare/"<?endif;?> class="heading"><?=GetMessage("COMPARE_HEADING")?></a>
		<?if(!empty($arResult["COUNT_ITEMS"])):?>
			<a href="<?=SITE_DIR?>compare/" class="link"><?=GetMessage("COMPARE_COUNT")?> <?=$arResult["COUNT_ITEMS"]?></a>
		<?else:?>
			<span class="text"><?=GetMessage("COMPARE_EMPTY")?></span>
		<?endif;?>
	</div>
</div>
<?$frame->end();?>