<?if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true) die();?>
<?$this->setFrameMode(true);?>
<div id="topSearch2">
	<form action="/search/" method="GET" id="topSearchForm">
		<div class="searchContainerInner">
			<div class="searchContainer">
				<div class="searchColumn">
					<input type="text" name="q" value="<?=!empty($arResult["q"]) ? $arResult["q"] : ""?>" autocomplete="off" placeholder="<?=GetMessage("SEARCH_TEXT")?>" id="searchQuery">
				</div>
				<div class="searchColumn">
					<input type="submit" name="send" value="Y" id="goSearch">
					<input type="hidden" name="r" value="Y">
				</div>
			</div>
		</div>
	</form>
</div>
<div id="searchResult"></div>
<div id="searchOverlap"></div>
<script>
	var searchAjaxPath = "<?=$templateFolder?>/ajax.php";
	var searchProductParams = '<?=\Bitrix\Main\Web\Json::encode($arParams);?>';
</script>