<?
if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true)
{
	die();
}
?>

<h1><?=GetMessage("FORGOT_TITLE")?></h1>

<ul id="authMenu">
	<li><a href="<?=SITE_DIR?>auth/" rel="nofollow"><?=GetMessage("AUTH_TITLE")?></a></li>
	<li><a href="<?=SITE_DIR?>auth/?register=yes" rel="nofollow"><?=GetMessage("AUTH_REGISTER")?></a></li>
	<li><a href="<?=SITE_DIR?>auth/?forgot_password=yes" rel="nofollow" class="selected"><?=GetMessage("AUTH_FORGOT_PASSWORD_2")?></a></li>
</ul>

<div class="bx-auth">

<?
if(!empty($arParams["~AUTH_RESULT"])):
	$text = str_replace(array("<br>", "<br />"), "\n", $arParams["~AUTH_RESULT"]["MESSAGE"]);
?>
	<div class="alert small <?=($arParams["~AUTH_RESULT"]["TYPE"] == "OK"? "alert-success":"alert-danger")?>"><?=nl2br(htmlspecialcharsbx($text))?></div>
<?endif?>
	<h3 class="bx-title"><?=GetMessage("AUTH_GET_CHECK_STRING")?></h3>
	<p class=""><?=GetMessage("AUTH_FORGOT_PASSWORD_1")?></p>
	<form name="bform" method="post" target="_top" action="<?=$arResult["AUTH_URL"]?>" class="bx-auth-form">
		<?if($arResult["BACKURL"] <> ''):?>
				<input type="hidden" name="backurl" value="<?=$arResult["BACKURL"]?>" />
		<?endif?>
		<input type="hidden" name="AUTH_FORM" value="Y">
		<input type="hidden" name="TYPE" value="SEND_PWD">

		<div class="">
			<div class="bx-authform-label-container"><?echo GetMessage("AUTH_LOGIN_EMAIL")?></div>
			<div class="bx-authform-input-container">
				<input type="text" name="USER_LOGIN" maxlength="255" value="<?=$arResult["LAST_LOGIN"]?>" />
				<input type="hidden" name="USER_EMAIL" />
			</div>
		</div>

		<div class="">
			<input type="submit" class="btn btn-primary submit" name="send_account_info" value="<?=GetMessage("AUTH_SEND")?>" />
		</div>

	</form>

</div>

<script type="text/javascript">
document.bform.onsubmit = function(){document.bform.USER_EMAIL.value = document.bform.USER_LOGIN.value;};
document.bform.USER_LOGIN.focus();
</script>
