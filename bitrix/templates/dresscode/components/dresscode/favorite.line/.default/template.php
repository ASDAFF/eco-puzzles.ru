<?if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true) die();?>
<?$frame = $this->createFrame()->begin("");?>
<div class="wrap">
	<a<?if(!empty($_SESSION["WISHLIST_LIST"]["ITEMS"])):?> href="<?=SITE_DIR?>wishlist/"<?endif;?> class="icon<?if(!empty($_SESSION["WISHLIST_LIST"]["ITEMS"])):?> active<?endif;?>"></a>
	<div class="nf">
		<a<?if(!empty($_SESSION["WISHLIST_LIST"]["ITEMS"])):?> href="<?=SITE_DIR?>wishlist/"<?endif;?> class="heading"><?=GetMessage("FAVORITE_HEADING")?></a>
		<?if(!empty($_SESSION["WISHLIST_LIST"]["ITEMS"])):?>
			<a href="<?=SITE_DIR?>wishlist/" class="link"><?=GetMessage("FAVORITE_COUNT")?> <?=count($_SESSION["WISHLIST_LIST"]["ITEMS"])?></a>
		<?else:?>
			<span class="text"><?=GetMessage("FAVORITE_EMPTY")?></span>
		<?endif;?>
	</div>
</div>
<?$frame->end();?>