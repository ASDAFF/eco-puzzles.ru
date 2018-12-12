<?if($TEMPLATE_FOOTER_VARIANT == "2" || $TEMPLATE_FOOTER_VARIANT == "6" || $TEMPLATE_FOOTER_VARIANT == "7"):?>
	<div class="logo">
		<?if(MAIN_PAGE === TRUE):?>
			<span><img class="lazy" src="<?=SITE_TEMPLATE_PATH?>/images/loading.svg" data-src="<?=SITE_TEMPLATE_PATH?>/images/logoW.png" alt="logo"></span>
		<?else:?>
			<a href="<?=SITE_DIR?>"><img class="lazy" src="<?=SITE_TEMPLATE_PATH?>/images/loading.svg" data-src="<?=SITE_TEMPLATE_PATH?>/images/logoW.png" alt="logo"></a>
		<?endif;?>
	</div>
<?elseif($TEMPLATE_FOOTER_VARIANT == "5" || $TEMPLATE_FOOTER_VARIANT == "4" || $TEMPLATE_FOOTER_VARIANT == "7"):?>
	<div class="logo">
		<?if(MAIN_PAGE === TRUE):?>
			<span><img class="lazy" src="<?=SITE_TEMPLATE_PATH?>/images/loading.svg" data-src="<?=SITE_TEMPLATE_PATH?>/images/logoB.png" alt="logo"></span>
		<?else:?>
			<a href="<?=SITE_DIR?>"><img class="lazy" src="<?=SITE_TEMPLATE_PATH?>/images/loading.svg" data-src="<?=SITE_TEMPLATE_PATH?>/images/logoB.png" alt="logo"></a>
		<?endif;?>
	</div>
<?else:?>
	<div class="logo">
		<?if(MAIN_PAGE === TRUE):?>
			<span><img class="lazy" src="<?=SITE_TEMPLATE_PATH?>/images/loading.svg" data-src="<?=SITE_TEMPLATE_PATH?>/images/logo.png" alt="logo"></span>
		<?else:?>
			<a href="<?=SITE_DIR?>"><img class="lazy" src="<?=SITE_TEMPLATE_PATH?>/images/loading.svg" data-src="<?=SITE_TEMPLATE_PATH?>/images/logo.png" alt="logo"></a>
		<?endif;?>
	</div>
<?endif;?>
