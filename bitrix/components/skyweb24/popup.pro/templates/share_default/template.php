<?if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();?>
<div id="skyweb24_share_default" style="<?if(!empty($arResult['GOOGLE_FONT'])){?>font-family:<?=$arResult['GOOGLE_FONT']?><?}?>">
<?if(!empty($arResult['GOOGLE_FONT'])){?><link href="https://fonts.googleapis.com/css?family=<?=$arResult['GOOGLE_FONT']?>:400,700" rel="stylesheet"><?}?>
	<div class="bg">		
		<h2><?=$arResult['TITLE']?></h2>
		<h3><?=$arResult['SUBTITLE']?></h3>
		<div class="socialButtons">
			<a class="sw24TargetAction <?=$arResult['SOC_VK']?>" href="https://vk.com/share.php?url=<?=$_SERVER["HTTP_REFERER"];?>" target="<?=$arResult['HREF_TARGET']?>" title="<?=GetMessage("skyweb24_referralsales_SHARE_IN_VK")?>"><img src="<?=$templateFolder?>/img/vk.jpg"></a>
			<a class="sw24TargetAction <?=$arResult['SOC_OD']?>" href="http://www.odnoklassniki.ru/dk?st.cmd=addShare&st.s=1&st._surl=<?=$_SERVER["HTTP_REFERER"];?>" target="<?=$arResult['HREF_TARGET']?>" title="<?=GetMessage("skyweb24_referralsales_SHARE_IN_OK")?>"><img src="<?=$templateFolder?>/img/odnoklassniki.jpg"></a>
			<a class="sw24TargetAction <?=$arResult['SOC_FB']?>" href="http://www.facebook.com/sharer/sharer.php?u=<?=$_SERVER["HTTP_REFERER"];?>" target="<?=$arResult['HREF_TARGET']?>" title="<?=GetMessage("skyweb24_referralsales_SHARE_IN_FB")?>"><img src="<?=$templateFolder?>/img/faceb.jpg"></a>
			<a class="sw24TargetAction <?=$arResult['SOC_TW']?>" href="http://twitter.com/share?url=<?=$_SERVER["HTTP_REFERER"];?>" target="<?=$arResult['HREF_TARGET']?>" title="<?=GetMessage("skyweb24_referralsales_SHARE_IN_TWITTER")?>"><img src="<?=$templateFolder?>/img/tw.jpg"></a>
			<a class="sw24TargetAction <?=$arResult['SOC_GP']?>" href="http://plus.google.com/share?url=<?=$_SERVER["HTTP_REFERER"];?>" target="<?=$arResult['HREF_TARGET']?>" title="<?=GetMessage("skyweb24_referralsales_SHARE_IN_GOOGLE")?>"><img src="<?=$templateFolder?>/img/google.jpg"></a>
			<a class="sw24TargetAction <?=$arResult['SOC_MR']?>" href="http://connect.mail.ru/share?share_url=<?=$_SERVER["HTTP_REFERER"];?>" target="<?=$arResult['HREF_TARGET']?>" title="<?=GetMessage("skyweb24_referralsales_SHARE_IN_MM")?>"><img src="<?=$templateFolder?>/img/mail.jpg"></a>
		</div>
		<?if(($arResult['CLOSE_TEXTBOX']=='Y') && (!empty($arResult['CLOSE_TEXTAREA']))) {?>
			<div align="center"><a href="javascript:void(0);" class="sw24TextCloseButton"><?=$arResult['CLOSE_TEXTAREA']?></a></div>
		<?}?>
	</div>
</div>
