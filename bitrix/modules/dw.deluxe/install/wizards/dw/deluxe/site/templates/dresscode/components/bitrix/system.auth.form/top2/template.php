<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();?>
<?$this->setFrameMode(true);?>
<a href="#" class="topAuthIcon"><span class="status<?if($arResult["FORM_TYPE"] != "login"):?> active<?endif;?>"><?if($arResult["FORM_TYPE"] == "login"):?>&#10010;<?else:?>&#10004;<?endif;?></span></a>
<div id="topAuth">
	<ul>
		<?if($arResult["FORM_TYPE"] == "login"):?>
			<li class="top-auth-login"><a href="<?=SITE_DIR?>auth/?backurl=<?=$APPLICATION->GetCurPageParam();?>"><?=GetMessage("LOGIN")?></a></li>
			<li class="top-auth-register"><a href="<?=SITE_DIR?>auth/?register=yes&amp;backurl=<?=$APPLICATION->GetCurPageParam();?>"><?=GetMessage("REGISTER")?></a></li>
		<?else:?>
			<li class="top-auth-personal"><a href="<?=SITE_DIR?>personal/"><?=GetMessage("PERSONAL")?></a></li>
			<li class="top-auth-exit"><a href="<?=SITE_DIR?>exit/"><?=GetMessage("EXIT")?></a></li>
		<?endif?>
	</ul>
</div>