<?if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();
$frame = $this->createFrame()->begin();
?>
<div class="wrap">
	<a href="#" class="icon"></a>
	<div class="nf">
		<span class="heading"><?=GetMessage("FAVORITE_HEADING")?></span>
		<!-- <a href="#" class="link"><?=GetMessage("FAVORITE_COUNT")?> 1000</a> -->
		<span class="text"><?=GetMessage("FAVORITE_EMPTY")?></span>
	</div>
</div>
<?$frame->end();?>