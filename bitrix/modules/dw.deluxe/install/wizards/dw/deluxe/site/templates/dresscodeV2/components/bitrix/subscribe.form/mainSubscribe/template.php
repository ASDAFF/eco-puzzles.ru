<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();?>
<?$this->setFrameMode(true);?>
<?if(!empty($arResult["RUBRICS"])):?>
	<div id="mainSubscribe">
		<div id="mainSubscribeContainer">
			<div class="limiter">
				<div class="heading"><?=GetMessage("MAIN_SUBSCRIBE_HEADING");?></div>
				<form action="<?=$arResult["FORM_ACTION"]?>">
					<?foreach($arResult["RUBRICS"] as $itemID => $itemValue):?>
						<div class="hidden">
							<label for="sf_RUB_ID_<?=$itemValue["ID"]?>">
								<input type="checkbox" name="sf_RUB_ID[]" id="sf_RUB_ID_<?=$itemValue["ID"]?>" value="<?=$itemValue["ID"]?>"<?if($itemValue["CHECKED"]) echo " checked"?> /> <?=$itemValue["NAME"]?>
							</label>
						</div>
					<?endforeach;?>
					<input type="text" name="sf_EMAIL" size="20" value="<?=$arResult["EMAIL"]?>" placeholder="<?=GetMessage("subscr_form_email_title")?>" class="field">
					<input type="submit" name="OK" value="<?=GetMessage("subscr_form_button")?>" class="submit">
				</form>
			</div>
		</div>
	</div>
<?endif;?>