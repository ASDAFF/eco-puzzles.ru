<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();
$this->setFrameMode(true);?>
	<div id="nextSection">
		<div class="title"><?=GetMessage("SELECT_SECTION")?></div>
		<ul>
			<?foreach($arResult["SECTIONS"] as $arElement):?>
	    		<?if($arElement["ELEMENT_CNT"] > 0 || $arParams["COUNT_ELEMENTS"] != "Y"):?>
	    			<li>
		    			<span class="sectionLine">
		    				<span class="sectionColumn"><a href="<?=$arElement["SECTION_PAGE_URL"]?>" class="<?=!empty($arElement["SELECTED"]) ? "selected" : ""?>"><?=$arElement["NAME"]?></a></span>
		    				<?if($arParams["COUNT_ELEMENTS"] == "Y"):?>
		    				<span class="sectionColumn last"><a href="<?=$arElement["SECTION_PAGE_URL"]?>" class="cnt"><?=$arElement["ELEMENT_CNT"]?></a></span>
		    				<?endif;?>
		    			</span>
	    			</li>
	    		<?endif;?>
		    <?endforeach;?>	
		</ul>
	</div>
	
