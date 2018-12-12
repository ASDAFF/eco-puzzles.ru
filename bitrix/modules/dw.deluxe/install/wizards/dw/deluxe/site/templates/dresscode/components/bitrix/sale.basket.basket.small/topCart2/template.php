<?if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();
$allPrice = 0;
$allQuantity = 0;
if(!empty($arResult["ITEMS"])){
	foreach ($arResult["ITEMS"] as $index => $arValue) {
		$allPrice += ($arValue["PRICE"] * $arValue["QUANTITY"]);
		$allQuantity += intval($arValue["QUANTITY"]);
	}
	$allPrice = round($allPrice);
}
if(!function_exists('priceFormat')){
		function priceFormat($data, $str = ""){
		$price = explode(".", $data);
		$strLen = strlen($price[0]);
		for ($i = $strLen; $i > 0 ; $i--) { 
			$str .=	(!($i%3) ? " " : "").$price[0][$strLen - $i];
		}
		return $str.($price[1] > 0 ? ".".$price[1] : "");
	}
}
?>

<div id="flushTopCart">
	<div class="wrap">
		<?$frame = $this->createFrame()->begin();?>
			<a<?if($allQuantity):?> href="<?=SITE_DIR?>personal/cart/"<?endif;?> class="icon<?if($allQuantity):?> active<?endif;?>"></a>
			<div class="nf">
				<a<?if($allQuantity):?> href="<?=SITE_DIR?>personal/cart/"<?endif;?> class="heading"><?=GetMessage("CART")?><ins<?if($allQuantity):?> class="active"<?endif;?>><?=$allQuantity?></ins></a>
				<?if($allQuantity):?>
					<a href="<?=SITE_DIR?>personal/cart/#order" class="link"><?=GetMessage("ORDER");?></a>
				<?else:?>
					<span class="text"><?=GetMessage("EMPTY")?></span>
				<?endif;?>
			</div>
		<?$frame->end();?>
	</div>
</div>

