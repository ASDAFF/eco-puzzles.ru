<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();?>
<form action="<?=$arResult["FORM_ACTION"]?>" method="post">
<?echo bitrix_sessid_post();?>
<table width="100%" border="0" cellpadding="0" cellspacing="0" class="data-table">
<thead><tr><td colspan="2"><h3><?echo GetMessage("subscr_title_settings")?></h3></td></tr></thead>
<tr valign="top">
	<td width="40%">
		<div class="lb"><?echo GetMessage("subscr_email")?><span class="starrequired">*</span></div>
		<input type="text" name="EMAIL" value="<?=$arResult["SUBSCRIPTION"]["EMAIL"]!=""?$arResult["SUBSCRIPTION"]["EMAIL"]:$arResult["REQUEST"]["EMAIL"];?>" size="30" maxlength="255" /></p>
		<div class="lb"><?echo GetMessage("subscr_rub")?><span class="starrequired">*</span></div>
		<?foreach($arResult["RUBRICS"] as $itemID => $itemValue):?>
			<input type="checkbox" name="RUB_ID[]" id="subscribe_rub_<?=$itemValue["ID"]?>" value="<?=$itemValue["ID"]?>"<?if($itemValue["CHECKED"]) echo " checked"?> /><label for="subscribe_rub_<?=$itemValue["ID"]?>"><?=$itemValue["NAME"]?></label>
		<?endforeach;?>
		<div class="lb"><?echo GetMessage("subscr_fmt")?></div>
		<div class="mCol">
			<input type="radio" name="FORMAT" id="subscribe_format_text" value="text"<?if($arResult["SUBSCRIPTION"]["FORMAT"] == "text") echo " checked"?> />
			<label for="subscribe_format_text"><?echo GetMessage("subscr_text")?></label>
		</div>
		<div class="mCol">
			<input type="radio" name="FORMAT" id="subscribe_format_html" value="html"<?if($arResult["SUBSCRIPTION"]["FORMAT"] == "html") echo " checked"?> />
			<label for="subscribe_format_html">HTML</label>
		</div>
		<p><?echo GetMessage("subscr_settings_note1")?></p>
		<p><?echo GetMessage("subscr_settings_note2")?></p>
	</td>
</tr>
<tfoot><tr><td colspan="2">
	<input type="submit" name="Save" class="submit" value="<?echo ($arResult["ID"] > 0? GetMessage("subscr_upd"):GetMessage("subscr_add"))?>" />
	<input type="reset" class="clear" value="<?echo GetMessage("subscr_reset")?>" name="reset" />
</td></tr>
<tr>
	<td colspan="2"><br /><br />
		<?echo GetMessage("USER_PERSONAL_INFO")?>
	</td>
</tr></tfoot>
</table>
<input type="hidden" name="PostAction" value="<?echo ($arResult["ID"]>0? "Update":"Add")?>" />
<input type="hidden" name="ID" value="<?echo $arResult["SUBSCRIPTION"]["ID"];?>" />
<?if($_REQUEST["register"] == "YES"):?>
	<input type="hidden" name="register" value="YES" />
<?endif;?>
<?if($_REQUEST["authorize"]=="YES"):?>
	<input type="hidden" name="authorize" value="YES" />
<?endif;?>
</form>
<br />
