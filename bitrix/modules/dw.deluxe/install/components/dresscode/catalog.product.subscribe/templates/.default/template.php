<?if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) die(); ?>
<?$this->setFrameMode(true);?>
<?if(!empty($arParams["PRODUCT_ID"])):?>
	<div class="catalog-subscribe">
		<div class="catalog-subscribe-container">
			<div class="catalog-subscribe-container__picture">
				<img src="<?=$templateFolder?>/images/catalogSubscribe.png" alt="<?=GetMessage("CATALOG_SUBSCRIBE_HEADING")?>" title="<?=GetMessage("CATALOG_SUBSCRIBE_HEADING")?>" class="subscribe-container-picture__image">
			</div>
			<div class="catalog-subscribe-container__heading"><?=GetMessage("CATALOG_SUBSCRIBE_HEADING")?></div>
			<div class="catalog-subscribe-container__text"><?=GetMessage("CATALOG_SUBSCRIBE_TEXT")?></div>
			<form action="#" name="catalog-subscribe-form" method="get" class="catalog-subscribe-container__form" id="bind__catalog-subscribe-form">
				<div class="subscribe-container-form__field">
					<input type="text" name="subscribe-form-email" placeholder="<?=GetMessage("CATALOG_SUBSCRIBE_FORM_EMAIL")?>" value="<?if(!empty($arResult["USER_EMAIL"])):?><?=$arResult["USER_EMAIL"]?><?endif;?>" data-email="yes" data-required="yes">
				</div>
				<div class="subscribe-container-form__field">
					<input type="hidden" name="subscribe-form-product-id" value="<?=$arParams["PRODUCT_ID"]?>">
					<input type="submit" name="subscribe-form-submit" class="hidden">
					<a href="#" class="container-form-field__submit btn-simple" id="bind__catalog-subscribe-submit"><?=GetMessage("CATALOG_SUBSCRIBE_SUBMIT")?></a>
				</div>
			</form>
			<a href="#" class="catalog-subscribe-container__exit"><span class="subscribe-container-exit__button"></span></a>
		</div>
		<div class="catalog-subscribe-success">
			<div class="catalog-subscribe-success__icon">
				<img src="<?=$templateFolder?>/images/catalogSubscribeSuccess.png" class="subscribe-success-icon__image" alt="<?=GetMessage("CATALOG_SUBSCRIBE_SUCCESS_HEADING")?>" title="<?=GetMessage("CATALOG_SUBSCRIBE_SUCCESS_HEADING")?>">
			</div>		
			<div class="catalog-subscribe-success__heading"><?=GetMessage("CATALOG_SUBSCRIBE_SUCCESS_HEADING")?></div>
			<div class="catalog-subscribe-success__text"><?=GetMessage("CATALOG_SUBSCRIBE_SUCCESS_TEXT")?></div>
			<div class="catalog-subscribe-success__close"><a href="#" class="subscribe-success-close__button btn-simple"><?=GetMessage("CATALOG_SUBSCRIBE_SUCCESS_CLOSE")?></a></div>
		</div>
		<div class="catalog-subscribe-error">
			<div class="catalog-subscribe-success__icon">
				<img src="<?=$templateFolder?>/images/catalogSubscribeError.png" class="subscribe-error-icon__image" alt="<?=GetMessage("CATALOG_SUBSCRIBE_ERROR_HEADING")?>" title="<?=GetMessage("CATALOG_SUBSCRIBE_ERROR_HEADING")?>">
			</div>		
			<div class="catalog-subscribe-error__heading"><?=GetMessage("CATALOG_SUBSCRIBE_ERROR_HEADING")?></div>
			<div class="catalog-subscribe-error__text"><?=GetMessage("CATALOG_SUBSCRIBE_ERROR_TEXT")?></div>
			<div class="catalog-subscribe-error__close"><a href="#" class="subscribe-error-close__button btn-simple" data-reload="yes"><?=GetMessage("CATALOG_SUBSCRIBE_ERROR_CLOSE")?></a></div>
		</div>
	</div>
	<link rel="stylesheet" href="<?=$templateFolder?>/ajax_styles.css" class="subscribeStyles">
	<script type="text/javascript" src="<?=$templateFolder?>/ajax_script.js"></script>
	<script type="text/javascript">
		var subscribeAjaxDir = "<?=$componentPath;?>";
	</script>
<?endif;?>