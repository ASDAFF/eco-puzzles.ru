<?if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();
$frame = $this->createFrame()->begin();
?>
<div class="wrap">
	<a<?if(!empty($_SESSION["WISHLIST_LIST"]["ITEMS"])):?> href="<?=SITE_DIR?>wishlist/"<?endif;?> class="icon<?if(!empty($_SESSION["WISHLIST_LIST"]["ITEMS"])):?> active<?endif;?>" title="<?=GetMessage("FAVORITE_HEADING")?>"></a>
	<a<?if(!empty($_SESSION["WISHLIST_LIST"]["ITEMS"])):?> href="<?=SITE_DIR?>wishlist/"<?endif;?> class="text<?if(!empty($_SESSION["WISHLIST_LIST"]["ITEMS"])):?> active<?endif;?>"><?if(!empty($_SESSION["WISHLIST_LIST"]["ITEMS"])):?><?=count($_SESSION["WISHLIST_LIST"]["ITEMS"])?><?else:?>0<?endif;?></a>
</div>
<script type="text/javascript">
	window.wishListTemplate = "version2";
</script>
<?$frame->end();?>