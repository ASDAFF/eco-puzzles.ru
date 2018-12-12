<?if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();
$frame = $this->createFrame()->begin();
?>

<a<?if(!empty($arResult["COUNT_ITEMS"])):?> href="<?=SITE_DIR?>compare/"<?endif;?> class="text<?if(!empty($arResult["COUNT_ITEMS"])):?> active<?endif;?>">
	<span class="icon"></span>
	<span class="value"><?if(!empty($arResult["COUNT_ITEMS"])):?><?=$arResult["COUNT_ITEMS"]?><?else:?>0<?endif;?></span>
</a>

<script type="text/javascript">
	window.compareTemplate = "version4";
</script>

<?$frame->end();?>