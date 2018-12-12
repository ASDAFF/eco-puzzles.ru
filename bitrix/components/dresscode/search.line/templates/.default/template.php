<?if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true) die();?>
<?$this->setFrameMode(true);?>
<div id="topSearch">
	<form action="/search/" method="GET" id="topSearchForm" class="limiter">
		<table>
			<tr>
				<td class="searchField">
					<input type="text" name="q" value="<?=!empty($arResult["q"]) ? $arResult["q"] : ""?>" autocomplete="off" placeholder="<?=GetMessage("SEARCH_TEXT")?>" id="searchQuery">
					<a href="#" id="topSeachCloseForm"><?=GetMessage("SEARCH_CLOSE_BUTTON")?></a>
				</td>
				<td class="submit">
					<input type="hidden" name="r" value="Y">
					<input type="submit" name="send" value="Y" id="goSearch">
				</td>
			</tr>
		</table>
	</form>
</div>
<div id="searchResult"></div>
<div id="searchOverlap"></div>
<script>
	var searchAjaxPath = "<?=$templateFolder?>/ajax.php";
	var searchProductParams = '<?=\Bitrix\Main\Web\Json::encode($arParams);?>';
</script>