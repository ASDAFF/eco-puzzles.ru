<?if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();
$frame = $this->createFrame()->begin();
?>
<div class="wrap">
	<a<?if(!empty($arResult["COUNT_ITEMS"])):?> href="<?=SITE_DIR?>compare/"<?endif;?> class="icon<?if(!empty($arResult["COUNT_ITEMS"])):?> active<?endif;?>" title="<?=GetMessage("COMPARE_HEADING")?>"></a>
	<a<?if(!empty($arResult["COUNT_ITEMS"])):?> href="<?=SITE_DIR?>compare/"<?endif;?> class="text<?if(!empty($arResult["COUNT_ITEMS"])):?> active<?endif;?>"><?if(!empty($arResult["COUNT_ITEMS"])):?><?=$arResult["COUNT_ITEMS"]?><?else:?>0<?endif;?></a>
</div>
<script type="text/javascript">
	window.compareTemplate = "version2";
</script>
<?$frame->end();?>