<?
if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true){
	die();
}
?>
<h1><?=GetMessage("AUTH_TITLE")?></h1>

<ul id="authMenu">
	<li><a href="<?=$arResult["AUTH_URL"]?>" rel="nofollow" class="selected"><?=GetMessage("AUTH_TITLE")?></a></li>
	<li><a href="<?=$arResult["AUTH_REGISTER_URL"]?>" rel="nofollow"><?=GetMessage("AUTH_REGISTER")?></a></li>
	<li><a href="<?=$arResult["AUTH_FORGOT_PASSWORD_URL"]?>" rel="nofollow"><?=GetMessage("AUTH_FORGOT_PASSWORD_2")?></a></li>
</ul>

<div class="bx-auth">
	<div class="leftContainer">
		<?
		if(!empty($arParams["~AUTH_RESULT"])):
			$text = str_replace(array("<br>", "<br />"), "\n", $arParams["~AUTH_RESULT"]["MESSAGE"]);
		?>
			<div class="alert alert-danger"><?=nl2br(htmlspecialcharsbx($text))?></div>
		<?endif?>

		<?
		if($arResult['ERROR_MESSAGE'] <> ''):
			$text = str_replace(array("<br>", "<br />"), "\n", $arResult['ERROR_MESSAGE']);
		?>
			<div class="alert alert-danger"><?=nl2br(htmlspecialcharsbx($text))?></div>
		<?endif?>

			<form name="form_auth" method="post" target="_top" action="<?=$arResult["AUTH_URL"]?>" class="bx-auth-form">

				<input type="hidden" name="AUTH_FORM" value="Y" />
				<input type="hidden" name="TYPE" value="AUTH" />
		<?if (strlen($arResult["BACKURL"]) > 0):?>
				<input type="hidden" name="backurl" value="<?=$arResult["BACKURL"]?>" />
		<?endif?>
		<?foreach ($arResult["POST"] as $key => $value):?>
				<input type="hidden" name="<?=$key?>" value="<?=$value?>" />
		<?endforeach?>

				<div>
					<div class="bx-authform-label-container"><?=GetMessage("AUTH_LOGIN")?>*</div>
					<div class="bx-authform-input-container">
						<input type="text" name="USER_LOGIN" maxlength="255" value="<?=$arResult["LAST_LOGIN"]?>" />
					</div>
				</div>
				<div>
					<div class="bx-authform-label-container"><?=GetMessage("AUTH_PASSWORD")?>*</div>
					<div class="bx-authform-input-container">
		<?if($arResult["SECURE_AUTH"]):?>
						<div class="bx-authform-psw-protected" id="bx_auth_secure" style="display:none"><div class="bx-authform-psw-protected-desc"><span></span><?echo GetMessage("AUTH_SECURE_NOTE")?></div></div>

		<script type="text/javascript">
		document.getElementById('bx_auth_secure').style.display = '';
		</script>
		<?endif?>
						<input type="password" name="USER_PASSWORD" maxlength="255" autocomplete="off" />
					</div>
				</div>

		<?if($arResult["CAPTCHA_CODE"]):?>
				<input type="hidden" name="captcha_sid" value="<?echo $arResult["CAPTCHA_CODE"]?>" />

				<div class="dbg_captha">
					<div class="bx-authform-label-container">
						<?echo GetMessage("AUTH_CAPTCHA_PROMT")?>
					</div>
					<div class="bx-captcha"><img src="/bitrix/tools/captcha.php?captcha_sid=<?echo $arResult["CAPTCHA_CODE"]?>" width="180" height="40" alt="CAPTCHA" /></div>
					<div class="bx-authform-input-container">
						<input type="text" name="captcha_word" maxlength="50" value="" autocomplete="off" />
					</div>
				</div>
		<?endif;?>

		<?if ($arResult["STORE_PASSWORD"] == "Y"):?>
				<div class="">
					<div class="checkbox">
						<input type="checkbox" id="USER_REMEMBER" name="USER_REMEMBER" value="Y" checked="checked" />
						<label class="bx-filter-param-label" for="USER_REMEMBER"><?=GetMessage("AUTH_REMEMBER_ME")?></label>
					</div>
				</div>
		<?endif?>
				<div class="">
					<input type="submit" class="btn btn-primary submit" name="Login" value="<?=GetMessage("AUTH_AUTHORIZE")?>" />
					<a href="<?=$arResult["AUTH_FORGOT_PASSWORD_URL"]?>" rel="nofollow" class="forgot"><?=GetMessage("AUTH_FORGOT_PASSWORD_2")?></a>
				</div>
			</form>
	</div>
	<div class="rightContainer">
		<h3 class="bx-title"><?=GetMessage("AUTH_SERVICES_TITLE")?></h3>
		<?if($arResult["AUTH_SERVICES"]):?>
			<?
			$APPLICATION->IncludeComponent("bitrix:socserv.auth.form",
				"flat",
				array(
					"AUTH_SERVICES" => $arResult["AUTH_SERVICES"],
					"AUTH_URL" => $arResult["AUTH_URL"],
					"POST" => $arResult["POST"],
				),
				$component,
				array("HIDE_ICONS"=>"Y")
			);
			?>
		<?endif?>
		<p><?=GetMessage("REGISTER_TEXT")?></p>
	</div>
</div>
<script type="text/javascript">
	<?if (strlen($arResult["LAST_LOGIN"])>0):?>
		try{document.form_auth.USER_PASSWORD.focus();}catch(e){}
	<?else:?>
		try{document.form_auth.USER_LOGIN.focus();}catch(e){}
	<?endif?>
</script>