<?if(!empty($_GET["SITE_ID"])){
	define("SITE_ID", $_GET["SITE_ID"]);
}?>
<?require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_before.php");?>
<?if(!empty($_GET["FORM_ID"])):?>

	<?if(!empty($_REQUEST) && !defined("BX_UTF")){
		foreach ($_REQUEST as $key => $nextValue) {
			if(is_array($nextValue)){
				foreach ($_REQUEST[$key] as $kkey => $nextElement) {
					$_REQUEST[$key][$kkey] = iconv("UTF-8", "WINDOWS-1251//IGNORE",  $nextElement);
				}
			}else{
				$_REQUEST[$key] = iconv("UTF-8", "WINDOWS-1251//IGNORE",  $nextValue);
			}
		}
	}?>

	<?$APPLICATION->IncludeComponent("bitrix:form.result.new", "ajax", Array(
		"CACHE_TIME" => "0",
			"CACHE_TYPE" => "N",
			"CHAIN_ITEM_LINK" => "",
			"CHAIN_ITEM_TEXT" => "",
			"EDIT_URL" => "",
			"IGNORE_CUSTOM_TEMPLATE" => "N",
			"LIST_URL" => "",
			"SEF_MODE" => "N",
			"SUCCESS_URL" => "",
			"USE_EXTENDED_ERRORS" => "Y",
			"WEB_FORM_ID" => intval($_GET["FORM_ID"]),
			"COMPONENT_TEMPLATE" => ".default",
			"VARIABLE_ALIASES" => array(
				"WEB_FORM_ID" => "WEB_FORM_ID",
				"RESULT_ID" => "RESULT_ID",
			)
		),
		false
	);?>

<?endif;?>

