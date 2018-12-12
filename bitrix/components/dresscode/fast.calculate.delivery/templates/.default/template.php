<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) die();?>
<?$this->setFrameMode(true);?>
<?if(!empty($arResult["DELIVERY_ITEMS"])):?>
	<div class="delivery-modal">
		<div class="delivery-modal-offset">
			<div class="delivery-modal-container">
				<div class="delivery-modal-exit"></div>
				<div class="h3"><?=GetMessage("DELIVERY_CITY_LABEL")?><?if(!empty($_SESSION["USER_GEO_POSITION"]["city"])):?><?=GetMessage("DELIVERY_CITY_IN_LABEL")?> <span><?=$_SESSION["USER_GEO_POSITION"]["city"]?></span><?endif;?></div>
				<div class="delivery-modal-qty">
					<div class="qtyBlock">
						<label class="label"><?=GetMessage("DELIVERY_QUANTITY_LABEL")?> </label> <a href="#" class="minus"></a><input type="text" class="qty" value="<?=$arResult["MEASURE_RATIO"]?>" data-step="<?=$arResult["MEASURE_RATIO"]?>" data-max-quantity="0" data-enable-trace="N"><a href="#" class="plus"></a>
					</div>
					<div class="delivery-count-cart">
						<input type="checkbox" name="delivery-modal-calc-checkbox" id="delivery-modal-calc-checkbox">
						<label for="delivery-modal-calc-checkbox"><?=GetMessage("DELIVERY_ALL_CART_PRODUCTS")?></label>
					</div>
				</div>
				<div class="delivery-items">
					<?include($_SERVER["DOCUMENT_ROOT"]."/".$templateFolder."/include/delivery_items.php");?>
				</div>
			</div>
			<link rel="stylesheet" href="<?=$templateFolder?>/ajax_styles.css" class="fastDeliveryModalStyles">
			<?if($arParams["LOAD_SCRIPT"] == "Y"):?>
				<script type="text/javascript" src="<?=$templateFolder?>/ajax_script.js"></script>
			<?endif;?>
			<script type="text/javascript">
				var fastCalcDeliveryAjaxDir = "<?=$templateFolder?>";
				var fastCalcDeliveryTemplatePath = "<?=$templateFolder?>";
				var fastCalcDeliveryParams = <?=\Bitrix\Main\Web\Json::encode(array("PARAMS" => $arParams));?>
			</script>
		</div>
	</div>
<?endif;?>