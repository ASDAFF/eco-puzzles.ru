<?if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();
$frame = $this->createFrame()->begin();
?><div class="wrap">
	<a<?if(!empty($arResult["NUM_PRODUCTS"])):?> href="<?=SITE_DIR?>personal/cart/"<?endif;?> class="heading<?if(!empty($arResult["NUM_PRODUCTS"])):?> active<?endif;?>"><span class="icon"><span class="count"><?if(!empty($arResult["NUM_PRODUCTS"])):?><?=$arResult["NUM_PRODUCTS"]?><?else:?>0<?endif;?></span></span><ins<?if(!empty($arResult["NUM_PRODUCTS"])):?> class="active"<?endif;?>><?if(!empty($arResult["NUM_PRODUCTS"])):?><span class="cartLabel"><?=GetMessage("CART")?>&nbsp;</span><?=$arResult["TOTAL_PRICE"]?><?else:?><?=GetMessage("EMPTY")?><?endif;?></ins></a>
</div>
<script type="text/javascript">
	window.topCartTemplate = "topCart2";
</script>
<?if(!empty($arResult["NUM_PRODUCTS"])):?>
	<script type="text/javascript">
		$(function(){
			//if exist function
			if(typeof changeAddCartButton == "function"){
				//vars
				var jsonComponentResult = <?=\Bitrix\Main\Web\Json::encode($arResult)?>
				//change addCart labels and clases
				changeAddCartButton(jsonComponentResult);
			}
		});
	</script>
<?endif;?>
<?$frame->end();?>