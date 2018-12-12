<?if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();
$frame = $this->createFrame()->begin();
?>
	<a<?if(!empty($arResult["NUM_PRODUCTS"])):?> href="<?=SITE_DIR?>personal/cart/"<?endif;?> class="countLink<?if(!empty($arResult["NUM_PRODUCTS"])):?> active<?endif;?>">
		<span class="count"><?if(!empty($arResult["NUM_PRODUCTS"])):?><?=$arResult["NUM_PRODUCTS"]?><?else:?>0<?endif;?></span>
	</a>
	<a<?if(!empty($arResult["NUM_PRODUCTS"])):?> href="<?=SITE_DIR?>personal/cart/"<?endif;?> class="heading<?if(!empty($arResult["NUM_PRODUCTS"])):?> active<?endif;?>">
		<span class="cartLabel">
			<?=GetMessage("CART_LABEL")?>
		</span>
		<span class="total">
			<?if(!empty($arResult["NUM_PRODUCTS"])):?>
				<?=$arResult["TOTAL_PRICE"]?>
			<?else:?>
				<?=GetMessage("CART_IS_EMPTY")?>
			<?endif;?>
		</span>
	</a>
<script type="text/javascript">
	window.topCartTemplate = "topCart5";
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