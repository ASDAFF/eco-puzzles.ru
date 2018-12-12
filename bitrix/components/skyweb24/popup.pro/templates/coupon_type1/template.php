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
<?function inducementWord($number, $wordArr){$cases = array (2, 0, 1, 1, 1, 2);return $wordArr[ ($number%100>4 && $number%100<20)? 2: $cases[min($number%10, 5)] ];}?>
<div id="skyweb24_coupon_coupon" style="<?if(!empty($arResult['GOOGLE_FONT'])){?>font-family:<?=$arResult['GOOGLE_FONT']?><?}?>">
<?if(!empty($arResult['GOOGLE_FONT'])){?><link href="https://fonts.googleapis.com/css?family=<?=$arResult['GOOGLE_FONT']?>:400,700" rel="stylesheet"><?}?>
	<div class="bg" style="background-image:url('<?=$arResult['IMG_1_SRC']?>')">
		<div>
			
			<h2><?=$arResult['TITLE']?></h2>
			<p><?=$arResult['SUBTITLE']?></p>
			<span class="sale"><?=GetMessage("POPUPPRO_SALE")?> <?=$arResult['PERCENT']?></span><span class="avaliable"><?=GetMessage("POPUPPRO_AVALIABLE")?> <?=$arResult['TIMING']?>
			<?=GetMessage("POPUPPRO_DAYS_".inducementWord($arResult['TIMING'],array(1,2,5)))?>
			</span>
			<?
				if($arResult['EMAIL_SHOW']=='Y'){
					if($arResult['EMAIL_REQUIRED']=='N' || $arResult['EMAIL_REQUIRED']=='Y'){
						$arResult['EMAIL_REQUIRED']=($arResult['EMAIL_REQUIRED']=='N')?'':'required';
					}
				}
				if($arResult['EMAIL_SHOW']=='N' || $arResult['EMAIL_SHOW']=='Y'){
					$arResult['EMAIL_SHOW']=($arResult['EMAIL_SHOW']=='N')?'notshow':'';
					$param_consent[]=$arResult['EMAIL_TITLE'];
				}
				?>
				<label class="<?=$arResult['EMAIL_SHOW']?> input">
					<p><?=GetMessage("POPUPPRO_INFO")?><?=$arResult['EMAIL_PLACEHOLDER']?></p>
					<input type="email" value="<?=$arResult['EMAIL']?>" name="EMAIL" placeholder="<?=$arResult['EMAIL_PLACEHOLDER']?>"/>
					<span class="error"><?=GetMessage("POPUPPRO_WRONG")?></span>
					<span class="not_new"><?=$arResult['EMAIL_NOT_NEW_TEXT']?></span>
				</label>
			<div class="coupon_block"><button onclick="getCouponC<?=$arParams['ID_POPUP']?>();"><?=$arResult['BUTTON_TEXT']?></button>
			<?//if(($arResult['CLOSE_TEXTBOX']=='Y') && (!empty($arResult['CLOSE_TEXTAREA']))) {?>
				<div><a href="javascript:void(0);" class="sw24TextCloseButton"><?=$arResult['CLOSE_TEXTAREA']?></a></div>
			<?//}?>
			<input class="goodCoupon" type="text" disabled>
			<?=bitrix_sessid_post()?>
			<span><?=GetMessage('POPUPPRO_COPYED')?></span>
			</div>
		</div>
	</div>
</div>
<script>
	function validateEmailCoupon(elementValue){
		var emailPattern = /^[a-zA-Z0-9._-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,4}$/;
		return emailPattern.test(elementValue);
	}
	function getCouponC<?=$arParams['ID_POPUP']?>(){
		var url = "<?=$templateFolder?>/ajax.php?id=<?=$arResult['RULE_ID']?>&avaliable=<?=$arResult['TIMING']?>&idPopup=<?=$arParams['ID_POPUP']?>";
		var email = BX('skyweb24_coupon_coupon').querySelector('label.input');
		url+='&sessid='+BX('skyweb24_coupon_coupon').querySelector('input[name="sessid"]').value;
		var getContinue=true;
		if(!email.classList.contains('notshow')){
			email = email.querySelector('input');
			getContinue=validateEmailCoupon(email.value);
			url+="&email="+email.value+"&addtotable=<?=$arResult['EMAIL_ADD2BASE']?>&unique=<?=$arResult['EMAIL_NOT_NEW']?>";
		}
		if(getContinue){
			if(!email.classList.contains('notshow')){
				email.className="";
			}
			BX.ajax({
				url:url,
				method:'POST',
				onsuccess: function(data){

					if(data=='not_unique'){
						BX('skyweb24_coupon_coupon').querySelector('span.not_new').style.display='inline-block';
					}else{
						BX('skyweb24_coupon_coupon').querySelector('span.not_new').style.display='none';
						BX('skyweb24_coupon_coupon').querySelector('label.input').remove()//.style.display='none';

						BX('skyweb24_coupon_coupon').querySelector('div.coupon_block button').remove()//.style.display='none';
						let closeButtonText=BX('skyweb24_coupon_coupon').querySelector('.sw24TextCloseButton');
						if(closeButtonText){
							closeButtonText.remove();
						}
						var input=BX('skyweb24_coupon_coupon').querySelector('div.coupon_block input[type="text"]');
						input.disabled=false;
						input.value=data;
						input.style.display='inline-block';
						input.addEventListener('click',function(){
							var range = document.createRange();
							range.selectNode(input);
							window.getSelection().addRange(range);
							try{
								var successful = document.execCommand('copy');
							}catch(err){
								console.log(err);
							}
							window.getSelection().removeRange(range);
							BX('skyweb24_coupon_coupon').querySelector('div.coupon_block input[type="text"]+span').style.display='inline';
						});
						<?=$arResult['BUTTON_METRIC']?>
						skyweb24PopupTargetAction();
					}
				},
				onfailure: function(data){
					console.log(data);
				}
			});
		}else{
			BX('skyweb24_coupon_coupon').querySelector('span.not_new').style.display='none';
			email.className="error";
			email.focus();
		}
	}
</script>
