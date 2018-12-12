<?if(MAIN_PAGE === TRUE):?>
	<span><img class="lazy" src="<?=SITE_TEMPLATE_PATH?>/images/loading.svg" data-src="<?=SITE_TEMPLATE_PATH?>/images/logo.png" alt="logo"></span>
<?else:?>
	<a href="<?=SITE_DIR?>"><img class="lazy" src="<?=SITE_TEMPLATE_PATH?>/images/loading.svg" data-src="<?=SITE_TEMPLATE_PATH?>/images/logo.png" alt="logo"></a>
<?endif;?>