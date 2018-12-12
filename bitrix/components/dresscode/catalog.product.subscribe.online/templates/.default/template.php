<?if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) die(); ?>
<?$this->setFrameMode(true);?>
<?if(!empty($arResult["ITEMS"])):?>
	<script type="text/javascript">
		$(function(){
			//if exist function
			if(typeof subscribeOnline == "function"){
				//vars
				var jsonComponentResult = <?=\Bitrix\Main\Web\Json::encode($arResult["ITEMS"])?>
				//change addCart labels and clases
				subscribeOnline(jsonComponentResult);
			}
		});
	</script>
<?endif;?>
<script type="text/javascript">
	var subscribeOnlineAjaxDir = "<?=$componentPath;?>";
	var subscribeOnlineLang = {
		add: "<?=GetMessage("CATALOG_SUBSCRIBE_ONLINE_ADD_LABEL")?>",
		delete: "<?=GetMessage("CATALOG_SUBSCRIBE_ONLINE_DEL_LABEL")?>"
	}
</script>
