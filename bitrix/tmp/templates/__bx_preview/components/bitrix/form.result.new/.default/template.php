<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();?>
<div class="webFormDw" data-id="<?=$arResult["arForm"]["ID"]?>">
	<?if($arResult["isFormErrors"] == "Y"):?><?=$arResult["FORM_ERRORS_TEXT"];?><?endif;?>
	<?=$arResult["FORM_NOTE"]?>
	<?if($arResult["isFormNote"] != "Y"):?>
		<?=$arResult["FORM_HEADER"]?>

		<?if($arResult["isFormDescription"] == "Y" || $arResult["isFormTitle"] == "Y" || $arResult["isFormImage"] == "Y"):?>
			<?if($arResult["isFormTitle"]):?>
				<h3 class="webFormHeading"><?=$arResult["FORM_TITLE"]?></h3>
			<?endif;?>
			<?if($arResult["isFormImage"] == "Y"):?>
				<a href="<?=$arResult["FORM_IMAGE"]["URL"]?>" target="_blank" alt="<?=GetMessage("FORM_ENLARGE")?>">
					<img src="<?=$arResult["FORM_IMAGE"]["URL"]?>" <?if($arResult["FORM_IMAGE"]["WIDTH"] > 300):?>width="300"<?elseif($arResult["FORM_IMAGE"]["HEIGHT"] > 200):?>height="200"<?else:?><?=$arResult["FORM_IMAGE"]["ATTR"]?><?endif;?> hspace="3" vscape="3" border="0" />
				</a>
			<?endif;?>
			<p class="webFormDescription"><?=$arResult["FORM_DESCRIPTION"]?></p>
		<?endif;?>
		<?if(!empty($arResult["QUESTIONS"])):?>
			<div class="webFormItems">
				<?foreach ($arResult["QUESTIONS"] as $FIELD_SID => $arQuestion):?>
					<?if ($arQuestion['STRUCTURE'][0]['FIELD_TYPE'] == 'hidden'):?>
						<?=$arQuestion["HTML_CODE"];?>
					<?else:?>
						<div class="webFormItem" id="WEB_FORM_ITEM_<?=$FIELD_SID?>">
							<div class="webFormItemCaption">
								<?if(!empty($arQuestion["IMAGE"])):?>
									<?$imageCaption = CFile::ResizeImageGet($arQuestion["IMAGE"]["ID"], array("width" => 24, "height" => 24), BX_RESIZE_IMAGE_PROPORTIONAL, false);?>
									<img src="<?=$imageCaption["src"]?>" class="webFormItemImage" alt="<?=$arQuestion["CAPTION"]?>">
								<?endif;?>
								<div class="webFormItemLabel"><?=$arQuestion["CAPTION"]?><?if ($arQuestion["REQUIRED"] == "Y"):?><span class="webFormItemRequired">*</span><?endif;?></div>
							</div>
							<div class="webFormItemError"></div>
							<div class="webFormItemField"<?if ($arQuestion["REQUIRED"] == "Y"):?> data-required="Y"<?endif;?>>
								<?if($arQuestion["STRUCTURE"]["0"]["FIELD_TYPE"] == "radio"):?>
									<?foreach ($arQuestion["STRUCTURE"] as $iq => $arNextStructureField):?>
										<div class="webFormItemFieldVariant">
											<input type="radio" name="form_radio_<?=$FIELD_SID?>" id="form_radio_<?=$FIELD_SID?>_<?=$arNextStructureField["ID"]?>" value="<?=$arNextStructureField["ID"]?>">
											<label for="form_radio_<?=$FIELD_SID?>_<?=$arNextStructureField["ID"]?>"><?=$arNextStructureField["MESSAGE"]?></label>
										</div>
									<?endforeach;?>
								<?else:?>
									<?=$arQuestion["HTML_CODE"]?>
								<?endif;?>
							</div>
						</div>
					<?endif;?>
				<?endforeach;?>
				<div class="personalInfo">
					<div class="webFormItem">
						<div class="webFormItemError"></div>
						<div class="webFormItemField" data-required="Y">
							<input type="checkbox" id="personalInfoFieldStatic" name="personalInfo" value="Y"><label for="personalInfoFieldStatic"><?=getMessage("PERSONAL_INFO_REQUIRED")?><span class="webFormItemRequired">*</span></label>
						</div>
					</div>
				</div>
				<?if($arResult["isUseCaptcha"] == "Y"):?>
					<div class="webFormItem">
						<div class="webFormItemCaption"><?=GetMessage("FORM_CAPTCHA_TABLE_TITLE")?></div>		
							<input type="hidden" name="captcha_sid" value="<?=htmlspecialcharsbx($arResult["CAPTCHACode"]);?>" class="webFormCaptchaSid" />
							<div class="webFormCaptchaPicture">
								<img src="/bitrix/tools/captcha.php?captcha_sid=<?=htmlspecialcharsbx($arResult["CAPTCHACode"]);?>" width="180" height="40" class="webFormCaptchaImage"/>
							</div>
							<div class="webFormCaptchaLabel">
								<?=GetMessage("FORM_CAPTCHA_FIELD_TITLE")?><?=$arResult["REQUIRED_SIGN"];?>
							</div>
						<div class="webFormItemField" data-required="Y">
							<input type="text" name="captcha_word" size="30" maxlength="50" value="" class="captcha_word" />
						</div>
					</div>
				<?endif;?>
			</div>
		<?endif;?>
		<div class="webFormError"></div>
		<div class="webFormTools">
			<input <?=(intval($arResult["F_RIGHT"]) < 10 ? "disabled=\"disabled\"" : "");?> type="submit" name="web_form_submit" value="<?=htmlspecialcharsbx(strlen(trim($arResult["arForm"]["BUTTON"])) <= 0 ? GetMessage("FORM_ADD") : $arResult["arForm"]["BUTTON"]);?>" class="sendWebFormDw" />
			<input type="hidden" name="web_form_apply" value="Y" />
			<input type="reset" value="<?=GetMessage("FORM_RESET");?>" />
			<p><span class="form-required starrequired">*</span> - <?=GetMessage("FORM_REQUIRED_FIELDS")?></p>
		</div>
		<?=$arResult["FORM_FOOTER"]?>
	<?endif;?>
	<div class="webFormMessage" id="webFormMessage_<?=$arResult["arForm"]["ID"]?>">
		<div class="webFormMessageContainer">
			<div class="webFormMessageMiddle">
				<div class="webFormMessageHeading"><?=GetMessage("WEB_FORM_SENDED_HEADING")?></div>
				<div class="webFormMessageDescription"><?=GetMessage("WEB_FORM_SENDED_DESCRIPTION")?></div>
				<a href="#" class="webFormMessageExit"><?=GetMessage("WEB_FORM_SENDED_CLOSE")?></a>
			</div>
		</div>
	</div>
</div>
<script type="text/javascript">
	var webFormAjaxDir = "<?=$templateFolder?>/ajax.php";
	var webFormSiteId = "<?=SITE_ID?>";
</script>