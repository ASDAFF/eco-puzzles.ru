<?if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();?>
<div id="skyweb24_age_default" style="<?if(!empty($arResult['GOOGLE_FONT'])){?>font-family:<?=$arResult['GOOGLE_FONT']?><?}?>">
<?if(!empty($arResult['GOOGLE_FONT'])){?><link href="https://fonts.googleapis.com/css?family=<?=$arResult['GOOGLE_FONT']?>:400,700" rel="stylesheet"><?}?>
	<img src="<?=$arResult['IMG_1_SRC']?>">
	<h2><?=$arResult['TITLE']?></h2>
	<div class="buttons">
		<a rel="nofollow" href="javascript:void(0);" class="sw24TargetAction yesClick"><?=$arResult['BUTTON_TEXT_Y']?></a>
		<a rel="nofollow" href="<?=$arResult['HREF_LINK']?>" class="noClick"><?=$arResult['BUTTON_TEXT_N']?></a>
	</div>
	<?if(($arResult['CLOSE_TEXTBOX']=='Y') && (!empty($arResult['CLOSE_TEXTAREA']))) {?>
		<div align="center"><a href="javascript:void(0);" class="sw24TextCloseButton"><?=$arResult['CLOSE_TEXTAREA']?></a></div>
	<?}?>
	<script>
		function findButtonClose(){
			var tmpEl=document.querySelector('#skyweb24_age_default .sw24TargetAction');
			if(tmpEl){
				tmpEl.addEventListener("click", pop_close);
			}else{
				setTimeout(findButtonClose, 50);
			}
		}

		findButtonClose();
		
		function pop_close(){	
			BX.ajax({
				url: '<?=$templateFolder?>/ajax.php',
				data:{'id_popup':'<?=$arParams["ID_POPUP"]?>','template_name':'<?=$templateName?>','checked':'Y'},
				method: 'POST',
				dataType: 'html',
				scriptsRunFirst:false,
				timeout:300,
				onsuccess: function(data){
					//skyweb24Popups.currentPopup.close();
				},
				onfailure: function(data){
					console.log(data);
				}
			});
			skyweb24Popups.currentPopup.close();
		}
	</script>
</div>