<?if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();
$frame = $this->createFrame()->begin();
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

<?$compareCount = count($_SESSION["COMPARE_LIST"]["ITEMS"])?>
<?$wishlistCount = count($_SESSION["WISHLIST_LIST"]["ITEMS"])?>

<div class="item">
	<a <?if($compareCount > 0):?>href="<?=SITE_DIR?>compare/"<?endif;?> class="compare<?if($compareCount > 0):?> active<?endif;?>"><span class="icon"></span><?=GetMessage("CART_COMPARE_LABEL")?><span class="mark"><?=$compareCount?></span></a>
</div>
<div class="item">
	<a <?if($wishlistCount > 0):?>href="<?=SITE_DIR?>wishlist/"<?endif;?> class="wishlist<?if($wishlistCount > 0):?> active<?endif;?>"><span class="icon"></span><?=GetMessage("CART_WISHLIST_LABEL")?><span class="mark"><?=$wishlistCount?></span></a>
</div>
<div class="item">
	<a <?if($allQuantity > 0):?>href="<?=SITE_DIR?>personal/cart/"<?endif;?> class="cart<?if($allQuantity > 0):?> active<?endif;?>"><span class="icon"></span><?=GetMessage("CART_LABEL")?><span class="mark"><?=$allQuantity?></span></a>
</div>
<?$frame->end();?>