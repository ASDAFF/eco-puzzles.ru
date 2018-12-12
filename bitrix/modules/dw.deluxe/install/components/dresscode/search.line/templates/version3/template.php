<?if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true) die();?>
<?$this->setFrameMode(true);?>
<a href="#" class="openTopSearch" id="openSearch"></a>
<div id="topSearch3">
	<div class="limiter">
		<form action="/search/" method="GET" id="topSearchForm">
			<div class="searchContainerInner">
				<div class="searchContainer">
					<div class="searchColumn">
						<input type="text" name="q" value="<?=!empty($arResult["q"]) ? $arResult["q"] : ""?>" autocomplete="off" placeholder="<?=GetMessage("SEARCH_TEXT")?>" id="searchQuery">
						<a href="#" id="topSeachCloseForm"><?=GetMessage("SEARCH_CLOSE_BUTTON")?></a>
					</div>
					<div class="searchColumn">
						<input type="submit" name="send" value="Y" id="goSearch">
						<input type="hidden" name="r" value="Y">
					</div>
				</div>
			</div>
		</form>
	</div>
</div>
<div id="searchResult"></div>
<div id="searchOverlap"></div>
<script>
	var searchAjaxPath = "<?=$templateFolder?>/ajax.php";
	var searchProductParams = '<?=\Bitrix\Main\Web\Json::encode($arParams);?>';
</script>