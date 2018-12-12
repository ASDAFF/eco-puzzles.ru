<?if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();?>
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
<div class="skyweb24_discount" id="skyweb24_discount" style="<?if(!empty($arResult['GOOGLE_FONT'])){?>font-family:<?=$arResult['GOOGLE_FONT']?><?}?>">
    <link href="https://fonts.googleapis.com/css?family=Old+Standard+TT" rel="stylesheet">
	<?if(!empty($arResult['GOOGLE_FONT'])){?><link href="https://fonts.googleapis.com/css?family=<?=$arResult['GOOGLE_FONT']?>:400,700" rel="stylesheet"><?}?>
    <div class="background">
		<span></span>
		<span></span>
		<span></span>
		<span></span>
		<span></span>
		<span></span>
		<span></span>
		<span></span>
		<span></span>
		<span></span>
		<span></span>
	</div>
    <div class="card">
        <?if(!empty($arResult['IMG_1_SRC'])){?><img src="<?=$arResult['IMG_1_SRC']?>"><?}?>
        <div class="number"><?$str=str_replace('#','0',$arResult['DISCOUNT_MASK']); $str=substr_replace($str,'1',-1);?>VIP <?=$str?></div>
    </div>
    <?if(!empty($arResult['IMG_2_SRC'])){?><img src="<?=$arResult['IMG_2_SRC']?>"><?}?>
    <div class="content">
        <h2><?=$arResult['TITLE']?></h2>
        <p><?=$arResult['SUBTITLE']?></p>
        <form method="POST" onsubmit="getCouponDisc<?=$arParams['ID_POPUP']?>(this);return false;">
			<?=bitrix_sessid_post()?>
        <fieldset>
			<?
			if($arResult['NAME_SHOW']=='Y'){
				if($arResult['NAME_REQUIRED']=='N' || $arResult['NAME_REQUIRED']=='Y'){
					$arResult['NAME_REQUIRED']=($arResult['NAME_REQUIRED']=='N')?'':'required';
				}
            }
			if($arResult['NAME_SHOW']=='N' || $arResult['NAME_SHOW']=='Y'){
				$arResult['NAME_SHOW']=($arResult['NAME_SHOW']=='N')?'notshow':'';
				$param_consent[]=$arResult['NAME_TITLE'];
			}?>
			<label class="one <?=$arResult['NAME_SHOW']?> <?=$arResult['NAME_REQUIRED']?>">
				<input <?=$arResult['NAME_REQUIRED']?> name="NAME" type="text" value="<?=$arResult['NAME']?>" placeholder="<?=$arResult['NAME_TITLE']?>" />
				<span><sup>*</sup></span>
			</label>
            <?
			if($arResult['LASTNAME_SHOW']=='Y'){
				if($arResult['LASTNAME_REQUIRED']=='N' || $arResult['LASTNAME_REQUIRED']=='Y'){
					$arResult['LASTNAME_REQUIRED']=($arResult['LASTNAME_REQUIRED']=='N')?'':'required';
				}
			}
			if($arResult['LASTNAME_SHOW']=='N' || $arResult['LASTNAME_SHOW']=='Y'){
				$arResult['LASTNAME_SHOW']=($arResult['LASTNAME_SHOW']=='N')?'notshow':'';
				$param_consent[]=$arResult['LASTNAME_TITLE'];
			}?>
			<label class="one <?=$arResult['LASTNAME_SHOW']?> <?=$arResult['LASTNAME_REQUIRED']?>">
				<input <?=$arResult['LASTNAME_REQUIRED']?> name="LASTNAME" type="text" value="<?=$arResult['LASTNAME']?>" placeholder="<?=$arResult['LASTNAME_TITLE']?>" />
				<span><sup>*</sup></span>
			</label>
			<?
			if($arResult['PHONE_SHOW']=='Y'){
				if($arResult['PHONE_REQUIRED']=='N' || $arResult['PHONE_REQUIRED']=='Y'){
					$arResult['PHONE_REQUIRED']=($arResult['PHONE_REQUIRED']=='N')?'':'required';
				}
			}
			if($arResult['PHONE_SHOW']=='N' || $arResult['PHONE_SHOW']=='Y'){
				$arResult['PHONE_SHOW']=($arResult['PHONE_SHOW']=='N')?'notshow':'';
				$param_consent[]=$arResult['PHONE_TITLE'];
			}
            ?>
			<label class="one <?=$arResult['PHONE_SHOW']?> <?=$arResult['PHONE_REQUIRED']?>">
				<input <?=$arResult['PHONE_REQUIRED']?> type="text" value="<?=$arResult['PHONE']?>" name="PHONE" placeholder="<?=$arResult['PHONE_TITLE']?>" />
				<span><sup>*</sup></span>
			</label>
			<?
			if($arResult['EMAIL_SHOW']=='Y'){
				if($arResult['EMAIL_REQUIRED']=='N' || $arResult['EMAIL_REQUIRED']=='Y'){
					$arResult['EMAIL_REQUIRED']=($arResult['EMAIL_REQUIRED']=='N')?'':'required';
				}
			}
			if($arResult['EMAIL_SHOW']=='N' || $arResult['EMAIL_SHOW']=='Y'){
				$arResult['EMAIL_SHOW']=($arResult['EMAIL_SHOW']=='N')?'notshow':'';
				$param_consent[]=$arResult['EMAIL_TITLE'];
			}?>
            <span class="not_new"><?=GetMessage('skwb24.not_new')?></span>
            <span class="not_valid"><?=$arResult['EMAIL_NOT_NEW_TEXT']?></span>
			<label class="<?=$arResult['EMAIL_SHOW']?> <?=$arResult['EMAIL_REQUIRED']?> one email">
				<input <?=$arResult['EMAIL_REQUIRED']?> type="email" value="<?=$arResult['EMAIL']?>" name="EMAIL" placeholder="<?=$arResult['EMAIL_TITLE']?>" />
				<span><sup>*</sup></span>
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

			<label class="submit"><input type="submit" onclick=""  value="<?=$arResult['BUTTON_TEXT']?>"></label>
			
			<?if(($arResult['CLOSE_TEXTBOX']=='Y') && (!empty($arResult['CLOSE_TEXTAREA']))) {?>
				<div align="center"><a href="javascript:void(0);" class="sw24TextCloseButton"><?=$arResult['CLOSE_TEXTAREA']?></a></div>
			<?}?>
			<div class="clear"></div>
		</fieldset>
        </form>
    </div>
</div>
<script>
	function validateEmailDisc(elementValue){
		var emailPattern = /^[a-zA-Z0-9._-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,4}$/;
		return emailPattern.test(elementValue);
	}
	function getCouponDisc<?=$arParams['ID_POPUP']?>(f){
		var url = "<?=$templateFolder?>/ajax.php?id=<?=$arResult['RULE_ID']?>&idPopup=<?=$arParams['ID_POPUP']?>";
		var email=f.querySelector('label.email');
        var name=f.querySelector('input[name="NAME"]').value;
        var lastname=f.querySelector('input[name="LASTNAME"]').value;
        var phone=f.querySelector('input[name="PHONE"]').value;
        var sessid=f.querySelector('input[name="sessid"]').value;
		var getContinue=true;
		email = email.querySelector('input');
		getContinue=validateEmailDisc(email.value);
		url+="&sessid="+sessid+"&email="+email.value+"&addtotable=<?=$arResult['EMAIL_ADD2BASE']?>&unique=<?=$arResult['EMAIL_NOT_NEW']?>&MASK=<?=str_replace('#','%23',$arResult['DISCOUNT_MASK'])?>&group=<?=$arResult['USER_GROUP']?>";
        url+='&NAME='+encodeURIComponent(name)+'&LAST_NAME='+encodeURIComponent(lastname)+'&PHONE'+encodeURIComponent(phone);
		if(getContinue){
			if(!email.classList.contains('notshow')){
				email.className="";
			}
			BX.ajax({
				url:url,
				method:'POST',
				onsuccess: function(data){
					console.log(data);
					if(data=='not_unique'){
						f.querySelector('span.not_new').style.display='inline-block';
					}else{
						f.querySelector('span.not_new').style.display='none';
						f.querySelector('span.not_valid').style.display='none';
						f.remove()//.style.display='none';
						var element = document.createElement('div');
                        element.setAttribute('class','send');
                        var t = document.createTextNode('<?=GetMessage('skwb24.discount_send')?>');
                        element.appendChild(t);
                        document.querySelector('#skyweb24_discount div.content').appendChild(element);
						skyweb24PopupTargetAction();
                        <?=$arResult['BUTTON_METRIC']?>
					}
				},
				onfailure: function(data){
					console.log(data);
				}
			});
		}else{
			f.querySelector('span.not_new').style.display='none';
			f.querySelector('span.not_valid').style.display='block';
			email.className="error";
			email.focus();
		}
	}
</script>
