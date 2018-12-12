<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();?>
<?$this->setFrameMode(true);?>
<?if(!empty($arResult)):?>
	<?include_once($_SERVER["DOCUMENT_ROOT"]."/".$templateFolder."/include/params.php");?>
	<div class="txSwitcherSettings<?if(!empty($_COOKIE["switcherOpened"]) && $_COOKIE["switcherOpened"] == "Y"):?> active noAnimate<?endif;?>"><div class="txSwitcherSettingsPicture"></div></div>
	<div class="txSwitcher<?if(!empty($_COOKIE["switcherOpened"]) && $_COOKIE["switcherOpened"] == "Y"):?> opened<?endif;?>">
		<div class="txSwitcherScroll">
			<div class="txSwitcherScrollContainer">
				<div class="switcherContainer">
					<div class="switcherBigHeading"><?=GetMessage("SETTINGS_DESIGN")?> <a href="#" class="switcherClose">&#10006;</a></div>
					<div class="switcherTabs">
						<div class="switcherChangeTabItems">
							<div class="switcherChangeTabItem"><a href="#" class="switcherChangeTab active"><?=GetMessage("SETTINGS_TECHNICAL_TAB")?></a></div>
							<div class="switcherChangeTabItem"><a href="#" class="switcherChangeTab"><?=GetMessage("SETTINGS_DESIGN_TAB")?></a></div>
							<div class="switcherChangeTabItem"><a href="#" class="switcherChangeTab"><?=GetMessage("SETTINGS_COUNTERS_TAB")?></a></div>
							<div class="switcherChangeTabItem"><a href="#" class="switcherChangeTab"><?=GetMessage("SETTINGS_MORE_TAB")?></a></div>
						</div>
						<?include_once($_SERVER["DOCUMENT_ROOT"]."/".$templateFolder."/include/tabs/technical.php");?>
						<?include_once($_SERVER["DOCUMENT_ROOT"]."/".$templateFolder."/include/tabs/design.php");?>
						<?include_once($_SERVER["DOCUMENT_ROOT"]."/".$templateFolder."/include/tabs/counters.php");?>
						<?include_once($_SERVER["DOCUMENT_ROOT"]."/".$templateFolder."/include/tabs/more.php");?>
					</div>
					<div class="switcherRowBlock">
						<div class="switcherList">
							<div class="switcherListItem active"><a href="#" class="switcherSave"><?=GetMessage("SETTINGS_SAVE")?></a></div>
							<div class="switcherListItem"><a href="#" class="switcherClose"><?=GetMessage("SETTINGS_CLOSE")?></a></div>
						</div>
					</div>
				</div>
			</div>
		</div>
	</div>
	<?include_once($_SERVER["DOCUMENT_ROOT"]."/".$templateFolder."/include/windows.php");?>
	<script>
		var settingsAjaxDir = "<?=$componentPath?>";
		var settingsLang = {
			"SETTINGS_PROPERTY_ALLREADY_CREATED": "<?=GetMessage("SETTINGS_PROPERTY_ALLREADY_CREATED")?>",
			"SETTINGS_PROPERTY_SUCCESS_CREATED": "<?=GetMessage("SETTINGS_PROPERTY_SUCCESS_CREATED")?>",
			"SETTINGS_PROPERTY_SHOW_ALL": "<?=GetMessage("SETTINGS_PROPERTY_SHOW_ALL")?>",
			"SETTINGS_PROPERTY_HIDE_ALL": "<?=GetMessage("SETTINGS_PROPERTY_HIDE_ALL")?>",
			"SETTINGS_PROPERTY_ERROR": "<?=GetMessage("SETTINGS_PROPERTY_ERROR")?>",
			"SETTINGS_PROPERTY_EDIT": "<?=GetMessage("SETTINGS_PROPERTY_EDIT")?>",
		};
	</script>
<?endif;?>