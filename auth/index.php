<?
define("NEED_AUTH", true);
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/header.php");

$userName = CUser::GetFullName();
if (!$userName)
	$userName = CUser::GetLogin();
?>
<script>
	<?if ($userName):?>
	BX.localStorage.set("eshop_user_name", "<?=CUtil::JSEscape($userName)?>", 604800);
	<?else:?>
	BX.localStorage.remove("eshop_user_name");
	<?endif?>

	<?if (isset($_REQUEST["backurl"]) && strlen($_REQUEST["backurl"])>0 && preg_match('#^/\w#', $_REQUEST["backurl"])):?>
	document.location.href = "<?=CUtil::JSEscape($_REQUEST["backurl"])?>";
	<?endif?>
</script>

<?$APPLICATION->SetTitle("Вы зарегистрированы и успешно авторизовались.");?>
<h1>Авторизация</h1>
<p>Вы зарегистрированы и успешно авторизовались.</p>
<p><a href="<?=SITE_DIR?>" class="backToIndexPage">Вернуться на главную страницу</a></p>
<div id="empty">
	<?$APPLICATION->IncludeComponent("bitrix:menu", "emptyMenuAuth", Array(
		"ROOT_MENU_TYPE" => "left",
			"MENU_CACHE_TYPE" => "N",
			"MENU_CACHE_TIME" => "3600",
			"MENU_CACHE_USE_GROUPS" => "Y",
			"MENU_CACHE_GET_VARS" => "",
			"MAX_LEVEL" => "1",
			"CHILD_MENU_TYPE" => "left",
			"USE_EXT" => "Y",
			"DELAY" => "N",
			"ALLOW_MULTI_SELECT" => "N",
		),
		false
	);?>
</div>
<?require($_SERVER["DOCUMENT_ROOT"]."/bitrix/footer.php");?>