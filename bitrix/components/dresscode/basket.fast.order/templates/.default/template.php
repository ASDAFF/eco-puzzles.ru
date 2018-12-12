<?if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) die(); ?>
<?$this->setFrameMode(true);?>
<div class="fast-basket">
	<div class="fast-basket-offset">
		<div class="fast-basket-container">
			<div class="fast-basket-container__icon">
				<img src="<?=$templateFolder?>/images/FastBasket.png" class="basket-container-icon__image" alt="<?=GetMessage("FAST_BASKET_HEADING")?>" title="<?=GetMessage("FAST_BASKET_HEADING")?>">
			</div>
			<div class="fast-basket-container__heading"><?=GetMessage("FAST_BASKET_HEADING")?></div>
			<div class="fast-basket-container__text"><?=GetMessage("FAST_BASKET_TEXT")?></div>
			<form action="#" name="fast-basket-form" method="get" class="fast-basket-container__form" id="bind__fast-basket-form">
				<div class="basket-container-form__field">
					<input type="text" name="basket-form-name" class="container-form__name" placeholder="<?=GetMessage("FAST_BASKET_NAME_PLACEHOLDER")?>">
				</div>
				<div class="basket-container-form__field">
					<input type="text" name="basket-form-telephone" class="container-form__telephone" data-required="yes" placeholder="<?=GetMessage("FAST_BASKET_TELEPHONE_PLACEHOLDER")?>">
				</div>
				<div class="basket-container-form__field">
					<input type="checkbox" name="basket-form-personal-info" id="container-form__personal" data-required="yes">
					<label for="container-form__personal" class="container-form__label"><span><?=GetMessage("FAST_BASKET_PERSONAL_INFO_LABEL")?></span></label>
				</div>
				<div class="basket-container-form__field">
					<input type="submit" name="basket-form-submit" class="hidden">
					<a href="#" class="container-form-field__submit btn-simple" id="bind__fast-basket-submit"><img src="<?=SITE_TEMPLATE_PATH?>/images/incart.png" class="form-field__image" alt="<?=GetMessage("FAST_BASKET_SUBMIT")?>" title="<?=GetMessage("FAST_BASKET_SUBMIT")?>"><?=GetMessage("FAST_BASKET_SUBMIT")?></a>
				</div>
			</form>
			<a href="#" class="fast-basket-container__exit"><span class="basket-container-exit__button"></span></a>
		</div>
		<div class="fast-basket-success">
			<div class="fast-basket-success__icon">
				<img src="<?=$templateFolder?>/images/FastBasketSuccess.png" class="basket-success-icon__image" alt="<?=GetMessage("FAST_BASKET_SUCCESS_HEADING")?>" title="<?=GetMessage("FAST_BASKET_SUCCESS_HEADING")?>">
			</div>		
			<div class="fast-basket-success__heading"><?=GetMessage("FAST_BASKET_SUCCESS_HEADING")?></div>
			<div class="fast-basket-success__text"><?=GetMessage("FAST_BASKET_SUCCESS_TEXT")?></div>
			<div class="fast-basket-success__close"><a href="#" class="basket-success-close__button btn-simple" data-reload="yes"><?=GetMessage("FAST_BASKET_SUCCESS_CLOSE")?></a></div>
		</div>
		<div class="fast-basket-error">
			<div class="fast-basket-success__icon">
				<img src="<?=$templateFolder?>/images/FastBasketError.png" class="basket-error-icon__image" alt="<?=GetMessage("FAST_BASKET_ERROR_HEADING")?>" title="<?=GetMessage("FAST_BASKET_ERROR_HEADING")?>">
			</div>		
			<div class="fast-basket-error__heading"><?=GetMessage("FAST_BASKET_ERROR_HEADING")?></div>
			<div class="fast-basket-error__text"><?=GetMessage("FAST_BASKET_ERROR_TEXT")?></div>
			<div class="fast-basket-error__close"><a href="#" class="basket-error-close__button btn-simple" data-reload="yes"><?=GetMessage("FAST_BASKET_ERROR_CLOSE")?></a></div>
		</div>
	</div>
	<link rel="stylesheet" href="<?=$templateFolder?>/ajax_styles.css" class="fastBasketStyles">
	<script type="text/javascript" src="<?=$templateFolder?>/ajax_script.js"></script>
	<script type="text/javascript">
		var fastBasketAjaxDir = "<?=$componentPath;?>";
	</script>
</div>