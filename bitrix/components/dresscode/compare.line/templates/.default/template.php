<?if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();
$frame = $this->createFrame()->begin();
?>
<div class="wrap">
	<a href="#" class="icon"></a>
	<div class="nf">
		<span class="heading"><?=GetMessage("COMPARE_HEADING")?></span>
		<!-- <a href="#" class="link"><?=GetMessage("COMPARE_COUNT")?> 1000</a> -->
		<span class="text"><?=GetMessage("COMPARE_EMPTY")?></span>
	</div>
</div>
<?$frame->end();?>