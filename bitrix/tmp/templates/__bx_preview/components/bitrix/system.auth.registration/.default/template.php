<?
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();

?>

<h1><?=GetMessage("REGISTER_TITLE")?></h1>

<ul id="authMenu">
	<li><a href="<?=SITE_DIR?>auth/" rel="nofollow"><?=GetMessage("AUTH_TITLE")?></a></li>
	<li><a href="<?=$arResult["AUTH_REGISTER_URL"]?>" rel="nofollow" class="selected"><?=GetMessage("AUTH_REGISTER")?></a></li>
	<li><a href="<?=SITE_DIR?>auth/?forgot_password=yes" rel="nofollow"><?=GetMessage("AUTH_FORGOT_PASSWORD_2")?></a></li>
</ul>

<div class="bx-auth">

	<p class="registerText"><?=GetMessage("REGISTER_TEXT")?></p>

	<div class="bx-authform-description-container">
		<div class="bold"><?echo $arResult["GROUP_POLICY"]["PASSWORD_REQUIREMENTS"];?></div>
	</div>

	<div class="bx-authform-description-container">
		<div class="bold"><span class="bx-authform-starrequired">*</span> - <?=GetMessage("AUTH_REQ")?></div>
	</div>

	<?
	if(!empty($arParams["~AUTH_RESULT"])):
		$text = str_replace(array("<br>", "<br />"), "\n", $arParams["~AUTH_RESULT"]["MESSAGE"]);
	?>
		<div class="alert <?=($arParams["~AUTH_RESULT"]["TYPE"] == "OK"? "alert-success":"alert-danger")?>"><?=nl2br(htmlspecialcharsbx($text))?></div>
	<?endif?>

	<?if($arResult["USE_EMAIL_CONFIRMATION"] === "Y" && is_array($arParams["AUTH_RESULT"]) &&  $arParams["AUTH_RESULT"]["TYPE"] === "OK"):?>
		<div class="alert alert-success"><?echo GetMessage("AUTH_EMAIL_SENT")?></div>
	<?else:?>

	<?if($arResult["USE_EMAIL_CONFIRMATION"] === "Y"):?>
		<div class="alert alert-warning"><?echo GetMessage("AUTH_EMAIL_WILL_BE_SENT")?></div>
	<?endif?>

<noindex>
	<form method="post" action="<?=$arResult["AUTH_URL"]?>" name="bform" class="bx-register-form">
		<?if($arResult["BACKURL"] <> ''):?>
				<input type="hidden" name="backurl" value="<?=$arResult["BACKURL"]?>" />
		<?endif?>
		<input type="hidden" name="AUTH_FORM" value="Y" />
		<input type="hidden" name="TYPE" value="REGISTRATION" />

		<div class="bx-authform-formgroup-container-line">

			<div class="bx-authform-formgroup-container">
				<div class="bx-authform-label-container"><?=GetMessage("AUTH_NAME")?></div>
				<div class="bx-authform-input-container">
					<input type="text" name="USER_NAME" maxlength="255" value="<?=$arResult["USER_NAME"]?>" />
				</div>
			</div>
			<div class="bx-authform-formgroup-container">
				<div class="bx-authform-label-container"><?=GetMessage("AUTH_LAST_NAME")?></div>
				<div class="bx-authform-input-container">
					<input type="text" name="USER_LAST_NAME" maxlength="255" value="<?=$arResult["USER_LAST_NAME"]?>" />
				</div>
			</div>

		</div>
		<div class="bx-authform-formgroup-container-line">
			<div class="bx-authform-formgroup-container">
				<div class="bx-authform-label-container"><span class="bx-authform-starrequired">*</span><?=GetMessage("AUTH_LOGIN_MIN")?></div>
				<div class="bx-authform-input-container">
					<input type="text" name="USER_LOGIN" maxlength="255" value="<?=$arResult["USER_LOGIN"]?>" data-required="required" />
				</div>
			</div>
			<div class="bx-authform-formgroup-container">
				<div class="bx-authform-label-container"><?if($arResult["EMAIL_REQUIRED"]):?><span class="bx-authform-starrequired">*</span><?endif?><?=GetMessage("AUTH_EMAIL")?></div>
				<div class="bx-authform-input-container">
					<input type="text" name="USER_EMAIL" maxlength="255" value="<?=$arResult["USER_EMAIL"]?>" data-required="required" />
				</div>
			</div>
		</div>
		<div class="bx-authform-formgroup-container-line">
			<div class="bx-authform-formgroup-container">
				<div class="bx-authform-label-container"><span class="bx-authform-starrequired">*</span><?=GetMessage("AUTH_PASSWORD_REQ")?></div>
				<div class="bx-authform-input-container">
					<?if($arResult["SECURE_AUTH"]):?>
						<div class="bx-authform-psw-protected" id="bx_auth_secure" style="display:none"><div class="bx-authform-psw-protected-desc"><span></span><?echo GetMessage("AUTH_SECURE_NOTE")?></div></div>
						<script type="text/javascript">
							document.getElementById('bx_auth_secure').style.display = '';
						</script>
					<?endif?>
					<input type="password" name="USER_PASSWORD" maxlength="255" value="<?=$arResult["USER_PASSWORD"]?>" data-required="required" autocomplete="off" />
				</div>
			</div>
			<div class="bx-authform-formgroup-container">
				<div class="bx-authform-label-container"><span class="bx-authform-starrequired">*</span><?=GetMessage("AUTH_CONFIRM")?></div>
				<div class="bx-authform-input-container">
					<?if($arResult["SECURE_AUTH"]):?>
									<div class="bx-authform-psw-protected" id="bx_auth_secure_conf" style="display:none"><div class="bx-authform-psw-protected-desc"><span></span><?echo GetMessage("AUTH_SECURE_NOTE")?></div></div>

					<script type="text/javascript">
					document.getElementById('bx_auth_secure_conf').style.display = '';
					</script>
					<?endif?>
					<input type="password" name="USER_CONFIRM_PASSWORD" maxlength="255" value="<?=$arResult["USER_CONFIRM_PASSWORD"]?>" data-required="required" autocomplete="off" />
				</div>
			</div>

		</div>
<?if($arResult["USER_PROPERTIES"]["SHOW"] == "Y"):?>
	<?foreach ($arResult["USER_PROPERTIES"]["DATA"] as $FIELD_NAME => $arUserField):?>

		<div class="bx-authform-formgroup-container">
			<div class="bx-authform-label-container"><?if ($arUserField["MANDATORY"]=="Y"):?><span class="bx-authform-starrequired">*</span><?endif?><?=$arUserField["EDIT_FORM_LABEL"]?></div>
			<div class="bx-authform-input-container">
				<?
				$APPLICATION->IncludeComponent(
					"bitrix:system.field.edit",
					$arUserField["USER_TYPE"]["USER_TYPE_ID"],
					array(
						"bVarsFromForm" => $arResult["bVarsFromForm"],
						"arUserField" => $arUserField,
						"form_name" => "bform"
					),
					null,
					array("HIDE_ICONS"=>"Y")
				);
				?>
			</div>
		</div>

	<?endforeach;?>
<?endif;?>
<div class="bx-authform-formgroup-container-line">
	<div class="bx-authform-formgroup-container">
		<div class="bx-authform-input-container">
			<input type="checkbox" name="USER_PERSONAL_INFO" maxlength="255" value="Y" data-required="required" id="userPersonalInfoReg" /><label for="userPersonalInfoReg"><?=GetMessage("USER_PERSONAL_INFO")?>*</label>
		</div>
	</div>
</div>
<?if ($arResult["USE_CAPTCHA"] == "Y"):?>
<div class="bx-authform-formgroup-container-line">
		<input type="hidden" name="captcha_sid" value="<?=$arResult["CAPTCHA_CODE"]?>" />
		<div class="bx-authform-formgroup-container">
			<div class="bx-authform-input-container">
				<div class="bx-authform-label-container"><span class="bx-authform-starrequired">*</span><?=GetMessage("CAPTCHA_REGF_PROMT")?></div>
				<input type="text" name="captcha_word" maxlength="50" value="" data-required="required" autocomplete="off"/>
			</div>
		</div>
		<div class="bx-authform-formgroup-container">
			<div class="bx-authform-label-container">
			<div class="bx-captcha"><img src="/bitrix/tools/captcha.php?captcha_sid=<?=$arResult["CAPTCHA_CODE"]?>" width="230" height="48" alt="CAPTCHA" /></div>

			</div>
		</div>
</div>
<?endif?>
		<div class="bx-authform-formgroup-container send">
			<input type="submit" class="btn btn-primary submit" name="Register" value="<?=GetMessage("AUTH_REGISTER")?>" />
		</div>

	</form>
</noindex>

<script type="text/javascript">
document.bform.USER_NAME.focus();
</script>

<?endif?>
</div>