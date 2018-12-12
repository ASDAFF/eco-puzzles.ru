<?if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();?>
<div id="skyweb24_contact_type2" style="<?if(!empty($arResult['GOOGLE_FONT'])){?>font-family:<?=$arResult['GOOGLE_FONT']?><?}?>">
<?if(!empty($arResult['GOOGLE_FONT'])){?><link href="https://fonts.googleapis.com/css?family=<?=$arResult['GOOGLE_FONT']?>:400,700" rel="stylesheet"><?}?>
	<?if(empty($arResult['SUCCESS'])){
		if($arResult['TIMER']=='Y'){
		$APPLICATION->IncludeComponent('skyweb24:popup.pro.timer','',array(
			'TITLE'=>$arResult['TIMER_TEXT'],
			'TIME'=>$arResult['TIMER_DATE'],
			'LEFT'=>$arResult['TIMER_LEFT'],
			'RIGHT'=>$arResult['TIMER_RIGHT'],
			'TOP'=>$arResult['TIMER_TOP'],
			'BOTTOM'=>$arResult['TIMER_BOTTOM'],
		),$component);
	}?>
	<h2><?=$arResult['TITLE']?></h2>
	<div class="subtitle">
		<div></div>
		<h3>
			<?$arResult['SUBTITLE'] = explode('<br>',$arResult['SUBTITLE']);?>
			<?foreach($arResult['SUBTITLE'] as $subtitle){?>
				<span><?=$subtitle?></span>
			<?}?>
		</h3>
	</div>
	<?if(!empty($arResult['ERRORS'])){?>
		<div class="error"><p><?=GetMessage("POPUPPRO_ERROR")?></p>
		<p><?foreach($arResult['ERRORS'] as $nextError){?>
			<?=GetMessage("POPUPPRO_ERROR_".$nextError)?> 
		<?}?></p>
		</div>
	<?}?>
	<form action="<?=$templateFolder?>/ajax.php" method="POST" onsubmit="sendForm2<?=$arParams['ID_POPUP']?>(this);return false;">
		<input type="hidden" name="id_popup" value="<?=$arParams['ID_POPUP']?>" />
		<input type="hidden" name="template_name" value="<?=$templateName?>" />
		<?=bitrix_sessid_post()?>
		<fieldset>
			<?
			if($arResult['NAME_SHOW']=='Y'){
				if($arResult['NAME_REQUIRED']=='N' || $arResult['NAME_REQUIRED']=='Y'){
					$arResult['NAME_REQUIRED']=($arResult['NAME_REQUIRED']=='N')?'':'required';
				}
			}
			if($arResult['NAME_SHOW']=='N' || $arResult['NAME_SHOW']=='Y'){
				$nameShow=($arResult['NAME_SHOW']=='N')?'notshow':'';
			}?>
			<label class="one <?=$arResult['NAME_SHOW']?> <?=$arResult['NAME_REQUIRED']?>">
				<input <?=$arResult['NAME_REQUIRED']?> name="NAME" type="text" value="<?=$arResult['NAME']?>" placeholder="<?=$arResult['NAME_PLACEHOLDER']?>" />
				<span><?=$arResult['NAME_TITLE']?><sup>*</sup></span>
			</label>
			<?
			if($arResult['PHONE_SHOW']=='Y'){
				if($arResult['PHONE_REQUIRED']=='N' || $arResult['PHONE_REQUIRED']=='Y'){
					$arResult['PHONE_REQUIRED']=($arResult['PHONE_REQUIRED']=='N')?'':'required';
				}
			}
			$arResult['PHONE_SHOW'];
			if($arResult['PHONE_SHOW']=='N' || $arResult['PHONE_SHOW']=='Y'){
				$arResult['PHONE_SHOW']=($arResult['PHONE_SHOW']=='N')?'notshow':'';
			}?>
			<label class="one <?=$arResult['PHONE_SHOW']?> <?=$arResult['PHONE_REQUIRED']?>">
				<input <?=$arResult['PHONE_REQUIRED']?> type="text" value="<?=$arResult['PHONE']?>" name="PHONE" placeholder="<?=$arResult['PHONE_PLACEHOLDER']?>" />
				<span><?=$arResult['PHONE_TITLE']?><sup>*</sup></span>
			</label>
			<?
			if($arResult['EMAIL_SHOW']=='Y'){
				if($arResult['EMAIL_REQUIRED']=='N' || $arResult['EMAIL_REQUIRED']=='Y'){
					$arResult['EMAIL_REQUIRED']=($arResult['EMAIL_REQUIRED']=='N')?'':'required';
				}
			}
			if($arResult['EMAIL_SHOW']=='N' || $arResult['EMAIL_SHOW']=='Y'){
				$arResult['EMAIL_SHOW']=($arResult['EMAIL_SHOW']=='N')?'notshow':'';
			}
			?>
			<label class="<?=$arResult['EMAIL_SHOW']?> <?=$arResult['EMAIL_REQUIRED']?> one">
				<input <?=$arResult['EMAIL_REQUIRED']?> type="email" value="<?=$arResult['EMAIL']?>" name="EMAIL" placeholder="<?=$arResult['EMAIL_PLACEHOLDER']?>" />
				<span><?=$arResult['EMAIL_TITLE']?><sup>*</sup></span>
			</label>
			<?
			if($arResult['DESCRIPTION_SHOW']=='Y'){
				if($arResult['DESCRIPTION_REQUIRED']=='N' || $arResult['DESCRIPTION_REQUIRED']=='Y'){
					$arResult['DESCRIPTION_REQUIRED']=($arResult['DESCRIPTION_REQUIRED']=='N')?'':'required';
				}
			}
			if($arResult['DESCRIPTION_SHOW']=='N' || $arResult['DESCRIPTION_SHOW']=='Y'){
				$arResult['DESCRIPTION_SHOW']=($arResult['DESCRIPTION_SHOW']=='N')?'notshow':'';?>
				<div class="clear"></div>
				<?
			}?>
			<label class="<?=$arResult['DESCRIPTION_SHOW']?> <?=$arResult['DESCRIPTION_REQUIRED']?>">

				<textarea <?=$arResult['DESCRIPTION_REQUIRED']?> name="DESCRIPTION" placeholder="<?=$arResult['DESCRIPTION_PLACEHOLDER']?>"><?=$arResult['DESCRIPTION']?></textarea><span><?=$arResult['DESCRIPTION_TITLE']?><sup>*</sup></span>
			</label>
			
			<?
				if($arResult['USE_CONSENT_SHOW']=='N' || $arResult['USE_CONSENT_SHOW']=='Y'){
					$arResult['USE_CONSENT_SHOW']=($arResult['USE_CONSENT_SHOW']=='N')?'notshow':'';
				}
			if($arResult['USE_CONSENT_SHOW']!='N' && count($arResult['AGREEMENTS'])>0){
			?>
			<div class="<?=$arResult['USE_CONSENT_SHOW']?> consentBlock">
			<input type="checkbox" name="use_consent" value="Y" checked="checked" required /> <a href="/bitrix/tools/skyweb24_agreement.php?ID=<?=$arResult['CONSENT_ID']?>" target="_blank"><?=$arResult['CONSENT_LIST']?></a>
			</div>
			<?}?>

			<label class="submit">
				<button type="submit" onclick=""><?=$arResult['BUTTON_TEXT']?></button>
			</label>
			<?if(($arResult['CLOSE_TEXTBOX']=='Y') && (!empty($arResult['CLOSE_TEXTAREA']))) {?>
				<div align="center"><a href="javascript:void(0);" class="sw24TextCloseButton"><?=$arResult['CLOSE_TEXTAREA']?></a></div>
			<?}?>
			<div class="clear"></div>
		</fieldset>
	</form>
	<script>
		function sendForm2<?=$arParams['ID_POPUP']?>(f){
			var sendO={},
				cInputs=f.querySelectorAll("input, textarea");
			for(var i=0; i<cInputs.length; i++){
				sendO[cInputs[i].name]=cInputs[i].value;
			}
			BX.ajax({
				url: f.action,
				data:sendO,
				method: 'POST',
				dataType: 'html',
				scriptsRunFirst:false,
				timeout:300,
				onsuccess: function(data){
					BX("skyweb24_contact_type2").outerHTML=data;
					<?=$arResult['BUTTON_METRIC']?>
				},
				onfailure: function(data){
					console.log(data);
				}
			});
		}
	</script>
	<?}if(!empty($arResult['SUCCESS']) && $arResult['SUCCESS']=='Y'){?>
	<div class="success"><?=GetMessage("POPUPPRO_THANKS")?></div>
	<?}?>
</div>
