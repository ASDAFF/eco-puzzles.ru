<?if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();?>
<div id="skyweb24_popup_action" style="<?if(!empty($arResult['GOOGLE_FONT'])){?>font-family:<?=$arResult['GOOGLE_FONT']?><?}?>">
<?if(!empty($arResult['GOOGLE_FONT'])){?><link href="https://fonts.googleapis.com/css?family=<?=$arResult['GOOGLE_FONT']?>:400,700" rel="stylesheet"><?}?>
	<?if($arResult['TIMER']=='Y'){
		$APPLICATION->IncludeComponent('skyweb24:popup.pro.timer','',array(
			'TITLE'=>$arResult['TIMER_TEXT'],
			'TIME'=>$arResult['TIMER_DATE'],
			'LEFT'=>$arResult['TIMER_LEFT'],
			'RIGHT'=>$arResult['TIMER_RIGHT'],
			'TOP'=>$arResult['TIMER_TOP'],
			'BOTTOM'=>$arResult['TIMER_BOTTOM'],
		),$component);
	}?>
	<img id="img_going_s1" src="<?=$arResult['IMG_1_SRC']?>">
	<div class="text">
		<h2><?=$arResult['TITLE']?></h2>
		<div class="info"><?=$arResult['CONTENT']?></div>
		<h3><?=$arResult['SUBTITLE']?></h3>
		<a onclick="<?=$arResult['BUTTON_METRIC']?>" target="<?=$arResult['HREF_TARGET']?>" href="<?=$arResult['LINK_HREF']?>" class="sw24TargetAction going_link"><?=$arResult['LINK_TEXT']?></a>
	</div>
	<?if(($arResult['CLOSE_TEXTBOX']=='Y') && (!empty($arResult['CLOSE_TEXTAREA']))) {?>
		<a href="javascript:void(0);" class="sw24TextCloseButton"><?=$arResult['CLOSE_TEXTAREA']?></a>
	<?}?>
</div>