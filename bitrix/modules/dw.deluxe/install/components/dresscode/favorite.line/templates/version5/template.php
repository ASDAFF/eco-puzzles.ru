<?if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();
$frame = $this->createFrame()->begin();
?>

<a<?if(!empty($_SESSION["WISHLIST_LIST"]["ITEMS"])):?> href="<?=SITE_DIR?>wishlist/"<?endif;?> class="text<?if(!empty($_SESSION["WISHLIST_LIST"]["ITEMS"])):?> active<?endif;?>">
	<span class="icon"></span>
	<span class="value"><?if(!empty($_SESSION["WISHLIST_LIST"]["ITEMS"])):?><?=count($_SESSION["WISHLIST_LIST"]["ITEMS"])?><?else:?>0<?endif;?></span>
</a>
<a<?if(!empty($_SESSION["WISHLIST_LIST"]["ITEMS"])):?> href="<?=SITE_DIR?>wishlist/"<?endif;?> class="link<?if(!empty($_SESSION["WISHLIST_LIST"]["ITEMS"])):?> active<?endif;?>">
	<span class="label"><?=GetMessage("FAVORITE_HEADING")?></span>
</a>

<script type="text/javascript">
	window.wishListTemplate = "version5";
</script>

<?$frame->end();?>