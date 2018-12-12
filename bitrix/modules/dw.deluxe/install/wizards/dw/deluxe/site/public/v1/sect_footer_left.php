<?if($TEMPLATE_FOOTER_VARIANT == "3" || $TEMPLATE_FOOTER_VARIANT == "4" || $TEMPLATE_FOOTER_VARIANT == "8"):?>
	<div class="logo">
		<?if(MAIN_PAGE === TRUE):?>
			<span><img src="<?=SITE_TEMPLATE_PATH?>/images/logoW.png" alt=""></span>
		<?else:?>
			<a href="<?=SITE_DIR?>"><img src="<?=SITE_TEMPLATE_PATH?>/images/logoW.png" alt=""></a>
		<?endif;?>
	</div>
<?elseif($TEMPLATE_FOOTER_VARIANT == "5" || $TEMPLATE_FOOTER_VARIANT == "6" || $TEMPLATE_FOOTER_VARIANT == "7"):?>
	<div class="logo">
		<?if(MAIN_PAGE === TRUE):?>
			<span><img src="<?=SITE_TEMPLATE_PATH?>/images/logoB.png" alt=""></span>
		<?else:?>
			<a href="<?=SITE_DIR?>"><img src="<?=SITE_TEMPLATE_PATH?>/images/logoB.png" alt=""></a>
		<?endif;?>
	</div>
<?else:?>
	<div class="logo">
		<?if(MAIN_PAGE === TRUE):?>
			<span><img src="<?=SITE_TEMPLATE_PATH?>/images/logo.png" alt=""></span>
		<?else:?>
			<a href="<?=SITE_DIR?>"><img src="<?=SITE_TEMPLATE_PATH?>/images/logo.png" alt=""></a>
		<?endif;?>
	</div>
<?endif;?>
